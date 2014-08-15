=== Plugin Name ===
Contributors: fareharbor
Tags: reservations, booking, embeds, fareharbor
Requires at least: 3.0
Tested up to: 3.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds shortcodes for FareHarbor reservation booking calendar embeds to your site

== Description ==

Adds a shortcode that makes it easy to add FareHarbor embed calendars to your site. You'll need to be on FareHarbor for this to be useful, learn about it at https://fareharbor.com/.

Usage example: [fareharbor shortname=“companyname” type=“small” items=“500” lightframe=“yes”]

## Available Options

* `shortname`: Your company’s FareHarbor shortname. Required.
* `type`: What style of embed should be used. Available options are large, small, and button. Optional, if no type is provided the small calendar style will be used.
* `items`: IDs of the items that should be included in the calendar. Optional, if no items are provided a calendar of all your items will be displayed.
* `lightframe`: Use “yes” or “no” to enable or disable the on-site Lightframe booking. Optional, is yes by default.
* `asn`: Your company’s shortname if you are using the ASN network. (If used, shortname should be the name of your partner company.) Optional.
* `ref`: The voucher number that should be set for ASN bookings. Optional.

== Installation ==

1. Download the plugin and unzip it.
1. Upload the `fareharbor` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place the `[fareharbor shortname="companyname"]` shortcode in your templates

== Changelog ==

= 0.5 =
* Initial implementation