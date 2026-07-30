# Gratia Gratis block theme

This is a fully block-based WordPress theme. Its templates, template parts,
patterns, global settings, and style variations can all be edited in the Site
Editor.

## Included templates

- Front page
- Posts page and fallback index
- Single post
- Standard page and page without title
- Contact page (`contact-us` slug)
- Books page (`books` slug)
- Archive, search, and 404

## Included patterns

The homepage is assembled from separate patterns for the hero, mission/TELL,
ministry gallery, books, journal, donation, and full newsletter form. Interior
templates use a compact email-only newsletter pattern. Header, footer, and
page-hero patterns are available in the inserter too.

## Site setup

1. Activate **Gratia Gratis** under Appearance > Themes.
2. In Settings > Reading, keep the existing static homepage and posts page.
3. Set the Site Logo in the Site Editor; the theme uses WordPress' Site Logo
   block rather than embedding a duplicate logo.
4. Keep the existing Mailchimp for WordPress form. The newsletter pattern
   renders the plugin's default `[mc4wp_form]` shortcode.
5. The contact template styles the site's existing WPForms form.

WordPress stores Site Editor customizations in the database. If a template has
already been customized, reset it in the Site Editor to see later changes made
to the corresponding theme file.

## Development

Run Composer from the `gg` directory. The `postbuild` script copies this source
theme into `wordpress/wp-content/themes/gratia-gratis` while retaining Twenty
Twenty-Four as a safe fallback.
