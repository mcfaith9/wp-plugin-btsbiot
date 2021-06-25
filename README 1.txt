=== BTS BIOT by Mcfaith ===
Contributors: mcfaith9
Donate link: facebook.com/mcfaith
Tags: multisite, custom, taxonomies
Requires at least: 5.6
Requires PHP: 7.4
Tested up to: 5.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sync basic needs for multisite installation.

== Description ==

Sync categories and taxonomies on multisite use same db_table instead of its own. Custom function like copy post/pages/woocommerce product from Site A to Site B. etc.

== Installation ==

1. Upload the `bts-biot-by-mcfaith` folder to the `/wp-content/plugins/` directory.
1. **Either** activate the plugin on each child site's Plugins panel, **or** activate it across the whole site using the Network Admin's Plugins panel.
1. Edit pages and posts as you normally would.

e.g.

1. Upload `btsbiot.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Frequently Asked Questions ==

= Why do I want this? =

If you're using multisite to separate out parts of a website that still have some connection to each other,
then you may want to use the same tags, categories and other taxonomies across them all.

= Where is the data stored? =

In the taxonomy tables for the *parent* site of the network.

= Can I use this for some child sites and not others? =

Yes. Activate the plugin on each child site that you wish to use it for.
In this case, do not activate the plugin across the whole network.

= Can I use this for some taxonomies and not others? =

No, it's all or nothing.

= Can I use this with my custom taxonomy? =

Yes.

= Is this compatible with other plugins that affect taxonomies? =

In most cases, yes.

== Changelog ==

= 1.0 =
* Added Global Taxonomies.

== Upgrade Notice ==

Will be added in the future

== Screenshots ==

1. Temporary Screenshots `/assets/34260281.png`

Here's a link to [WordPress](https://github.com/mcfaith9/wp-plugin-btsbiot) and one to [Markdown's Syntax Documentation][markdown syntax].

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`