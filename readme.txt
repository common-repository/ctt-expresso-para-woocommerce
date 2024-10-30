=== CTT Expresso para WooCommerce ===
Contributors: Limpinho, ffernandes9747
Tags: woocommerce, ctt, ctt expresso, this.functional
Author URI: https://thisfunctional.pt/
Plugin URI: https://thisfunctional.pt/wordpress/plugins/ctt-expresso-para-woocommerce/
Requires at least: 5.0
Tested up to: 6.6.1
Requires PHP: 7.1
Stable tag: 3.2.13
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows integrating your WooCommerce website with CTTExpresso platform via their API.

== Description ==

This plugin is only for those who have hired CTT Expresso pick up/drop off services.
This is not a shipping method but, it's an add-on for any WooCommerce shipping method you activate it on.

It can be used in two ways:

1. Print Using CTT Expresso Portal: When an order is marked as completed, it automatically does 3 things: Creates an order in [CTT Expresso Portal](https://portal.cttexpresso.pt/); Includes the tracking number and link in WooCommerce completed order email; Also shows the tracking number in the order itself. It's necessary to access [CTT Expresso Portal](https://portal.cttexpresso.pt/) to fullfill the orders;

2. Print on My Website: When an order is marked as completed, it does all the actions listed in option 1 and also makes available the order documents to download, print and request pickups, with no need to access [CTT Expresso Portal](https://portal.cttexpresso.pt/) afterwards;

In both scenarios, the plugin also adds a CTT Expresso option in the Shipping Methods, so you can choose where to make CTT Expresso available.

Important: You must have weights in grams in all your products.

This is not an official CTT Expresso plugin, but their support was obtained during its development. The CTT Expresso logo and brand is copyrighted, belongs to them and is used with their permission.


== Installation ==

* Use the included automatic install feature on your WordPress admin panel and search for “CTT Expresso para WooCoommerce”.
* Go to the  plugin Settings or Woocommerce > Settings > CTT Expresso para WooCommerce Tab

* Client ID, Contract ID are provided when you use CTT Expresso services.
* Authentication ID is provided on request, in order to have access to CTT Expresso API.
* Sub Shipping Options and Number of delivery attempts are set depending on the service type you've established with CTT Expresso.
* In WooCommerce Shipping Zones, you must set the CTT Expresso option, defining your own costs per shipping option.

== Changelog ==

= 3.2.13 =
* WordPress 6.6.1 compatibility
* Minor vulnerabilities/sanitization fixes.

= 3.2.12 =
* Minor vulnerabilities/sanitization fixes.

= 3.2.11 =
* Fixed quantity bug on 13 Múltiplo, 19 Múltiplo and Rede Shopping.

= 3.2.10 =
* WordPress 5.9 compatibility
* Minor usability changes

= 3.2.9 =
* Minor changes in developer hooks;

= 3.2.8 =
* Minor changes in pickup options due to CTT API changes;
* Possibility to remove items in pickup list;

= 3.2.7 =
* WordPress 5.8 compatibility
* Custom Data update for non EU countries.

= 3.2.6 =
* EMS Economy customs data update.

= 3.2.5 =
* New Table Rate Shipping for WooCommerce version compatibilty.
* Order notes 50 chars limit when sending to CTT API

= 3.2.4 =
* New WooCommerce Table Rate Shipping version compatibilty.

= 3.2.3 =
* New Flexible Shipping version compatibilty.

= 3.2.2 =
* Minor optimizations.
* New developer hooks.

= 3.2.1 =
* Minor bug fixes.

= 3.2 =
* Introduction of developer hooks for possible customizations.

= 3.1.1 =
* Minor bug fixes.

= 3.1 =
* Enable sender email and mobile number on plugin settings.

= 3.0 =
* The plugin now supports pick up requests, that enables those that use "Print on My Website" version, being able to schedule pickup requests, totally eliminating the need to use CTT Expresso Portal.
* Enable/Disable tracking ID info on Completed Order email.
* Minor bug fixes and improvements.

= 2.3 =
* Shipping Against reimbursement.
* Debug log improvements.
* Folder permission issues fixed.

= 2.2 =
* Additional SubProduct support: EMS Economy – ENCF008.02; EMS International – EMSF001.02.
* New feature: Possibility to add and manage special services.
* Alert if WooCommerce weight unit not in gram.

= 2.1 =
* Additional SubProduct support: EMSF009.01 – 10; EMSF028.01 – 13 Múltiplo; ENCF005.01 – 19; EMSF010.01 – 19 Múltiplo; EMSF015.01 – Cargo; EMSF053.01 – Easy Return 24; EMSF054.01 – Easy Return 48; EMSF059.01 – Rede Shopping.
* File Log for debugging.
* Tested with WooCommerce 4.2.2

= 2.0 =
* Possibility to either choose to use CTT Expresso Portal or download and print order documents directly on WooCommerce.
* Additional SubProduct support: EMSF056.01 - Para amanhã; EMSF001.01 - 13h; ENCF008.01 - 48h; EMSF057.01 Em 2 dias.
* Added compatibility with the following plugins: [Flexible Shipping for WooCommerce](https://wordpress.org/plugins/flexible-shipping), [WooCommerce Table Rate Shipping](http://bolderelements.net/plugins/table-rate-shipping-woocommerce), [Table Rate Shipping](https://woocommerce.com/products/table-rate-shipping/) and [WooCommerce Advanced Shipping](https://codecanyon.net/item/woocommerce-advanced-shipping/8634573).

= 1.2 =
* CTT Expresso tracking URL changed

= 1.1 =
* Translation changes

= 1.0 =
* Initial release 
* Tested with WordPress 5.3.2
* Tested with WooCommerce 3.9.1