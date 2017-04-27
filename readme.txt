=== Easy Digital Downloads Visual Composer Integration ===
Contributors: nwoetzel
Tags: em, events manager, vc, visual composer, js_composer
Requires at least: 4.6
Tested up to: 4.7.2
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This pluging integrates shortcodes defined by the events-manager plugin as elements into visual composer.

== Description ==

This plugin requires that you have installed:
* [Visual Composer](https://vc.wpbakery.com/) - tested for version 5.0.1
* [Events Manager](https://wordpress.org/plugins/events-manager/) - tested for version 5.6.6.1 

The [Events Manager shortcodes](http://wp-events-plugin.com/documentation/shortcodes/) are mapped as Visual Composer elements.

== Installation ==

Download the latest release from github as zip and install it through wordpress.
Or use [wp-cli](http://wp-cli.org/) with the latest release:
<pre>
wp-cli.phar plugin install https://github.com/nwoetzel/em-vc-integration/archive/1.2.0.zip --activate
</pre>

Or add them as a composer package in your wordpress' composer.json file:
<pre>
{
        "repositories": [
                {
                        "type":  "vcs",
                        "url":   "https://github.com/nwoetzel/em-vc-integration.git"
                }

        ],
        "require"     : {
                "nwoetzel/em-vc-integration":"~1.3"
        }
}
</pre>
Read more about that at http://composer.rarst.net/

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.2.0 =
* added support for composer http://composer.rarst.net/

= 1.1.0 =
* added translations

= 1.0.0
* Initial release
