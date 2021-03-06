=== RSVPMaker for Toastmasters ===
Contributors: davidfcarr
Donate link: http://wp4toastmasters.com/
Tags: toastmasters
Requires at least: 3.0
Tested up to: 4.2.2
Stable tag: 1.5.3
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This Toastmasters-specific extension to the RSVPMaker events plugin adds role signups and member performance tracking.

== Description ==

This Toastmasters-specific extension to the RSVPMaker events plugin adds role signups and member performance tracking.

As an alternative to other club web software options that include a custom content management system, this WordPress-based solution allows website operators to take advantage of all the flexibility available on other WordPress sites.

For documentation and Toastmasters club website hosting, see [WP4Toastmasters.com](http://wp4toastmasters.com/ "WordPress for Toastmasters")

First install RSVPMaker - [download from wordpress.org](https://wordpress.org/plugins/rsvpmaker/) | [documentation at RSVPMaker.com](https://rsvpmaker.com)

A related set of Toastmasters-branded WordPress themes are [available for download from WP4Toastmasters.com](http://wp4toastmasters.com/2014/11/06/toastmasters-branded-wordpress-themes/), allowing clubs and districts to use a design that complies with the branding guidelines from Toastmasters International while still having a degree of design flexibility.

== Installation ==

1. First, download and install [RSVPMaker](https://wordpress.org/plugins/rsvpmaker/)
1. Upload the RSVPMaker for Toastmasters plugin folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why is RSVPMaker required? =

RSVPMaker provides the basic functionality for creating, editing, and displaying event posts. By default, it collects a yes/no response to an event (and, optionally, a PayPal payment). This Toastmasters extension allows the software to collect signups for specific roles.

RSVPMaker includes many features you will not use in the course of a regular Toastmasters meeting, but you may have open house events or seminars you might want to advertise as events on your website. 

= How is the version hosted at wp4toastmasters.com different? =

Toastmasters clubs can get free websites hosted as subdomains of [wp4toastmasters.com](http://wp4toastmasters.com/). This is using the WordPress Multisite version of WordPress, meaning that all sites hosted in this fashion run on the same instance of the software, with the network administrator controlling what plugins and themes are available. This is how WordPress.com, the service provided by the creators of the software, functions.

When you install this software on your own website, you have greater freedom to install other plugins or themes, including those of your own design.

You can purchase hosting through [wp4toastmasters.com](http://wp4toastmasters.com/), which is a way of supporting the author of this software and getting the most direct technical support.

== Screenshots ==

1. Role signup on the online agenda.
2. Data collected through the plugin feeds performance reports, such as this one showing progress toward Competent Communicator.

== Changelog ==

= 1.5 =

* Simplification of the Agenda Setup screen
* Bug fix (plugin was interfering with WordPress standard password reset function. Sorry!)

= 1.4.9 =

Bug fixes and tweaks for compatibility with the latest version of RSVPMaker.

= 1.4.7 =

RSVPMaker for Toastmasters is now translation-ready. See the readme file in the translations folder for instructions on how to use the POEdit tool to define equivalent labels for user interface elements in other languages.

= 1.4.6 =

Adds option of new agenda layout with sidebar.

= 1.4.5 =

Tested with WordPress 4.1

= 1.4.4 =

Role data and speech details recorded on a Free Toast Host can now be imported so that it will be reflected in reports run on the website.

= 1.4.3 =

* Added ability to assign a role to a guest of the club who is not on the member list.
* Toastmasters settings screen includes key options such as setting the timezone (important for scheduling) and making the site public (turning off the "discourage search engines from indexing this site" option).

= 1.4.2 =

* Added support for members-only posts. Posts tagged to the Members Only category will only be displayed to logged in members. (Display name of category can vary, but slug must be 'members-only')
* Bug fixes and a removal of a hard-coded mention of a carrcommunications.com email address.

= 1.4 =

Simplified editor for Agenda Setup.

= 1.3.2 =

Correcting initial setup of database and meeting templates. If you installed an earlier version, please deactivate and reactivate the plugin for the correct setup.

= 1.3.1 =

Bug fix: member stats editing

= 1.2/1.3 =

* Fixed recommend function bug
* Default event template created on plugin activation