=== BuddyPress XProfile Image Field ===
Contributors: kalengi
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KWZPYPL527WVN
Tags: BuddyPress, XProfile, Extended Profile, User Profile, Field, Image
Requires at least: WordPress 3.2.1 with BuddyPress 1.5
Tested up to: WordPress 3.8.1 with BuddyPress 1.9.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows users to add fields of type Image to user profile forms without having to edit any of the BuddyPress core files.

== Description ==

The BuddyPress Extended Profile component lacks native handling of Image type fields. The BuddyPress XProfile Image Field plugin allows users to add fields of type Image to user profile forms without having to edit any of the BuddyPress core files. 

The images are stored by default into /wp-content/uploads/profiles/[USER_ID] directory. Where [USER_ID] is the id of logged in user.

== Installation ==

1. Upload `bp-xprofile-image-field` to the `/wp-content/plugins/` directory or use the automatic installation in the WordPress plugin panel.
2. Activate the plugin through the WordPress 'Plugins' menu


== Changelog ==

= 1.1.0 =
* fixed to prevent crashing the profile edit page on sites not using BuddyPress Default Theme 

= 1.0.0 =
* Initial release
