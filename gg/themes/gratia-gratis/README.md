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
- Single book
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
4. Activate Mailchimp for WordPress and keep form `1158`. The theme wrapper
   renders that form and shows an honest disabled preview if the plugin is not
   active on a temporary environment.
5. The contact template styles the site's existing WPForms form.

The project migration creates three shared Navigation entities: **Primary
navigation**, **Footer: About**, and **Footer: Explore**. Edit them in the Site
Editor's Navigation area; changes are reflected everywhere the corresponding
header or footer menu is used. The theme patterns resolve these entities by
stable slug, so their database IDs can differ safely between environments.

The `/blog` slug has its own query template, so it works whether or not that
page is selected as the Posts page under Reading settings.

Books are managed under the dedicated **Books** admin menu. Each Book uses its
title, Featured Image as the cover, editor content and excerpt, Book Status,
menu order, and the Author, destination URL, and link-label fields in the Book
details panel. The Books page and homepage preview are editable block patterns
whose Query Loops read those records. The accompanying project MU plugin owns
the content type so the book data remains available independently of the
active theme. It also performs a one-time migration of the five legacy covers
into Book posts.

The single-post template uses the post's Featured Image above its title. When
an older post has no Featured Image assigned, the empty image band collapses;
assigning the appropriate Media Library image restores the prototype layout.

WordPress stores Site Editor customizations in the database. If a template has
already been customized, reset it in the Site Editor to see later changes made
to the corresponding theme file.

## Development

Run Composer from the `gg` directory. The `postbuild` script copies this source
theme into `wordpress/wp-content/themes/gratia-gratis` while retaining Twenty
Twenty-Four as a safe fallback.
