# Gratia Gratis WordPress on Upsun

Gratia Gratis is a Composer-managed WordPress application based on the
`artetecha/wordpress-upsun-starter` architecture. WordPress core, plugins,
Italian translations, the `upsun-wp` MU plugin, and the Redis object-cache
drop-in are assembled into the ignored `wordpress/` directory. Do not edit
generated files there.

The production site currently uses Twenty Twenty-Four. The build preserves
that active theme while removing the other bundled Twenty themes. No theme or
branding code is imported from Per Grazia.

## Local installation

From this directory:

```bash
composer install
cp example.wp-config-local.php wp-config-local.php
```

Create a local MariaDB database, replace every placeholder in
`wp-config-local.php`, and generate unique WordPress salts. Install WordPress
with your own URL and administrator credentials:

```bash
wp --path=wordpress core install \
  --url=http://localhost:8080 \
  --title='Gratia Gratis' \
  --admin_user='<admin-user>' \
  --admin_email='<admin-email>' \
  --prompt=admin_password
```

Serve `wordpress/` as the document root. Re-run `composer install` after
switching branches or when `composer.lock` changes.

## Composer-managed application tree

`composer install` and `composer update` both run the `postbuild` script. It
copies WordPress configuration and project MU plugins, installs `upsun-wp` and
its loader, installs the Redis object-cache drop-in, preserves Twenty
Twenty-Four, and removes unmanaged bundled plugins and inactive Twenty themes.

Add WordPress plugins to `composer.json`; do not install or update them from
wp-admin on Upsun.

## Upsun runtime and caching

The `gg` application uses PHP 8.4 with MariaDB, Redis, and Elasticsearch.
ElasticPress and Jetpack remain installed because their production features
are active. Uploads and cache files retain the legacy mount data through
`source: instance`. `www.gratia.gratis` is canonical and the apex redirects to
it.

Cloudflare provides CDN and security services and caches static assets. The
Upsun router is the only full-page HTML cache. WordPress page-cache plugins
must not be reintroduced. `wp-config.php` isolates Redis keys by environment,
disables dashboard file changes, and disables request-driven WordPress cron.

## Deployment lifecycle

The deploy and post-deploy hooks exit safely when WordPress has not been
installed. They never install WordPress, create users, or set administrator
credentials. Existing sites update the core schema, apply pending migrations,
enable Redis, run due cron jobs, and sanitize cloned environments.

For a genuinely new, empty Upsun database, install explicitly after the first
successful build:

```bash
upsun ssh --environment='<environment>'
cd wordpress
wp core install \
  --url='https://www.gratia.gratis/' \
  --title='Gratia Gratis' \
  --admin_user='<admin-user>' \
  --admin_email='<admin-email>' \
  --prompt=admin_password
wp theme activate "$(jq -r '.extra.distro["default-theme"]' ../composer.json)"
jq -r '.extra.distro["enable-plugins"][]' ../composer.json | xargs wp plugin activate
```

Use this only for a new empty database. Existing environments retain their
content, users, settings, and credentials.

## Migrations

Put ordered migration files in `migrations/` using
`YYYYMMDD_NNNN_short_name.php`. Each file returns a callable. Throw an
exception or return `false` to abort deployment and leave the migration
pending. Successful migrations are recorded in the database and follow cloned
data.

The included migrations remove only Really Simple Security, the WordPress
Cloudflare page-cache plugin, and WordPress Importer state. User-authored and
previously imported content is preserved. Jetpack and ElasticPress cleanup is
deliberately excluded after the production usage audit.

## Cron, CI, and backups

Upsun runs due WordPress events every five minutes. CI validates Composer,
installs the lock file, verifies generated placement, and PHP-lints project
configuration. The daily/manual backup workflow follows the project's default
production environment (currently `master`, and automatically `main` after a
rename) and requires `UPSUN_PROJECT` and `UPSUN_CLI_TOKEN` repository secrets.
