# Gratia Gratis WordPress on Upsun

Gratia Gratis is a Composer-managed WordPress application based on the
`artetecha/wordpress-upsun-starter` architecture. WordPress core, plugins,
Italian translations, the `upsun-wp` MU plugin, and the Redis object-cache
drop-in are assembled into the ignored `wordpress/` directory. Do not edit
generated files there.

WordPress core comes from `roots/wordpress`, which installs the official
WordPress archive without bundled themes or plugins through
`roots/wordpress-core-installer`. Core remains constrained to 7.1 for this
package migration; upgrading WordPress is a separate change.

The production site uses the source-controlled Gratia Gratis block theme from
`themes/gratia-gratis`. Twenty Twenty-Four 1.6 remains available as a fallback
through an explicit `wpackagist-theme/twentytwentyfour` dependency. Existing
environments are not switched automatically; fresh installs use Gratia Gratis
as their default theme.

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

When migrating a local installation from the John P. Bloch packages, use a
fresh checkout and install from the lockfile. Composer removes the old core
package's installation directory, which can also remove nested plugin, theme,
and upload files. Keep the old checkout and any local configuration or uploads
until the new checkout is ready. Upsun builds already start with a fresh
application tree; persistent uploads are mounted at deployment.

## Composer-managed application tree

`composer install` and `composer update` both run the `postbuild` script. It
first requires `wordpress/wp-includes/version.php` so an incomplete core
archive fails the build, then copies WordPress configuration and project MU
plugins, installs `upsun-wp` and its loader, installs the Redis object-cache
drop-in, and installs the custom Gratia Gratis theme. Composer installs Twenty
Twenty-Four and all third-party plugins explicitly; core supplies no bundled
themes or plugins to remove.

Add WordPress plugins and third-party themes to `composer.json`; do not install
or update them from wp-admin on Upsun.

## Gratia Gratis block theme

The custom theme is fully block based. Its source lives in
`themes/gratia-gratis`; never edit the generated copy under `wordpress/`.
Templates are composed from template parts and PHP patterns, global design
tokens live in `theme.json`, and the small CSS layer under `assets/css` covers
responsive and plugin-form details that cannot be expressed cleanly through
Global Styles.

After visual review on a non-production environment, activate it explicitly:

```bash
wp --path=wordpress theme activate gratia-gratis
```

Activating the theme does not overwrite content or stored Site Editor
customizations. Existing template customizations can override theme files, so
review or clear those customizations when comparing the packaged templates.

## Upsun runtime and caching

The `gg` application uses PHP 8.4 with MariaDB and Redis. Uploads and cache
files retain the legacy mount data through `source: instance`.
`www.gratia.gratis` is canonical and the apex redirects to it.

Cloudflare provides CDN and security services and caches static assets. The
Upsun router is the only full-page HTML cache. WordPress page-cache plugins
must not be reintroduced. `wp-config.php` isolates Redis keys by environment,
disables dashboard file changes, and disables request-driven WordPress cron.
The project MU plugin strips Polylang's anonymous `pll_language` response
cookie because language selection is URL-based; otherwise every HTML response
would carry `Set-Cookie` and bypass the Upsun router cache.

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

The included migrations import the legacy book catalog into the Book content
type, create the shared header and footer Navigation entities used by the
block theme, and remove obsolete plugin activation and settings state,
including Really Simple Security, the WordPress Cloudflare page-cache plugin,
WordPress Importer, ElasticPress, and Jetpack. Other user-authored and
previously imported content is preserved, including content created with
Jetpack blocks. The site styles MU plugin retains the layout of saved
tiled-gallery blocks.

## Cron, CI, and backups

Upsun runs due WordPress events every five minutes. CI validates Composer,
installs the lock file, verifies generated placement, and PHP-lints project
configuration. Production backups use Upsun's built-in scheduled backups.
