=== Zouetech Portfolio ===
Contributors: zouetech
Tags: portfolio, projects, custom post type, elementor, meta
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional portfolio projects CPT with taxonomies, structured meta, and Elementor Dynamic Tags support.

== Description ==

Zouetech Portfolio registers a Portfolio Projects custom post type, categories, tags, and a complete meta field system designed for Elementor Pro Single Templates and Rank Math SEO.

It does not render frontend templates. Elementor (or your theme) owns presentation.

Features:

* Custom Post Type: Portfolio Projects (`portfolio`)
* Taxonomies: Portfolio Categories, Portfolio Tags
* Project meta: links, gallery, project info, overview, features, technologies, testimonial
* Elementor Dynamic Tags (Zouetech Portfolio group)
* Clean, modern admin meta UI with card sections
* Secure, prefixed, OOP architecture
* Rank Math friendly (public + REST-enabled CPT/taxonomies/meta)

== Installation ==

1. Upload the `zouetech-portfolio` folder to `/wp-content/plugins/`
2. Activate the plugin through the Plugins screen
3. Go to Portfolio to add projects
4. Build your Single Portfolio template in Elementor Pro using Dynamic Tags → Zouetech Portfolio

== Frequently Asked Questions ==

= Does this plugin design the frontend? =

No. Use Elementor Pro Theme Builder for singles and archives.

= Is it compatible with Rank Math? =

Yes. The CPT and taxonomies are public and REST-enabled.

= Which fields map to core WordPress? =

Title, Excerpt (short description), Content (long description), and Featured Image use native WordPress fields. Everything else uses `_ztp_*` meta.

== Changelog ==

= 1.0.0 =
* Initial release: CPT, taxonomies, meta system, admin UI, Elementor Dynamic Tags.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
