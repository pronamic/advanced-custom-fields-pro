=== Advanced Custom Fields Pro ===
Contributors: elliotcondon
Tags: acf, advanced, custom, field, fields, form, repeater, content
Requires at least: 4.7.0
Tested up to: 5.2
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Customize WordPress with powerful, professional and intuitive fields.

== Description ==

Use the Advanced Custom Fields plugin to take full control of your WordPress edit screens & custom field data.

**Add fields on demand.** Our field builder allows you to quickly and easily add fields to WP edit screens with only the click of a few buttons!

**Add them anywhere.** Fields can be added all over WP including posts, users, taxonomy terms, media, comments and even custom options pages!

**Show them everywhere.** Load and display your custom field values in any theme template file with our hassle free developer friendly functions!

= Features =
* Simple & Intuitive
* Powerful Functions
* Over 30 Field Types
* Extensive Documentation
* Millions of Users

= Links =
* [Website](https://www.advancedcustomfields.com)
* [Documentation](https://www.advancedcustomfields.com/resources/)
* [Support](https://support.advancedcustomfields.com)
* [ACF PRO](https://www.advancedcustomfields.com/pro/)

= PRO =
The Advanced Custom Fields plugin is also available in a professional version which includes more fields, more functionality, and more flexibility! [Learn more](https://www.advancedcustomfields.com/pro/)


== Installation ==

From your WordPress dashboard

1. **Visit** Plugins > Add New
2. **Search** for "Advanced Custom Fields"
3. **Activate** Advanced Custom Fields from your Plugins page
4. **Click** on the new menu item "Custom Fields" and create your first Custom Field Group!
5. **Read** the documentation to [get started](https://www.advancedcustomfields.com/resources/getting-started-with-acf/)


== Frequently Asked Questions ==

= What kind of support do you provide? =

**Help Desk.** Support is currently provided via our email help desk. Questions are generally answered within 24 hours, with the exception of weekends and holidays. We answer questions related to ACF, its usage and provide minor customization guidance. We cannot guarantee support for questions which include custom theme code, or 3rd party plugin conflicts & compatibility. [Open a Support Ticket](https://www.advancedcustomfields.com/support/)

**Support Forums.** Our Community Forums provide a great resource for searching and finding previously answered and asked support questions. You may create a new thread on these forums, however, it is not guaranteed that you will receive an answer from our support team. This is more of an area for developers to talk to one another, post ideas, plugins and provide basic help. [View the Support Forum](https://support.advancedcustomfields.com/)


== Screenshots ==

1. Simple & Intuitive

2. Made for developers

3. All about fields


== Changelog ==

= 5.8.1 =
*Release Date - 3 June 2019*

* New - Added "Preview Size" and "Return Format" settings to the Gallery field.
* Tweak - Improved metabox styling for Gutenberg.
* Tweak - Changed default "Preview Size" to medium for the Image field.
* Fix - Fixed bug in media modal causing the primary button text to disappear after editing an image.
* Fix - Fixed bug preventing the TinyMCE Advanced plugin from adding `< p >` tags.
* Fix - Fixed bug where HTML choices were not visible in conditional logic dropdown.
* Fix - Fixed bug causing incorrect order of imported/synced flexible content sub fields.
* i18n - Updated German translation thanks to Ralf Koller.
* i18n - Updated Persian translation thanks to Majix.

= 5.8.0 =
*Release Date - 8 May 2019*

* New - Added ACF Blocks feature for ACF PRO.
* Fix - Fixed bug causing duplicate "save metabox" AJAX requests in the Gutenberg editor.
* Fix - Fixed bug causing incorrect Repeater field value order in AJAX requests.
* Dev - Added JS filter `'relationship_ajax_data'` to customize Relationship field AJAX data.
* Dev - Added `$field_group` parameter to `'acf/location/match_rule'` filter.
* Dev - Bumped minimum supported PHP version to 5.4.0.
* Dev - Bumped minimum supported WP version to 4.7.0.
* i18n - Updated German translation thanks to Ralf Koller.
* i18n - Updated Portuguese language thanks to Pedro Mendonça.

= 5.7.13 =
*Release Date - 6 March 2019*

* Fix - Fixed bug causing issues with registered fields during `switch_to_blog()`.
* Fix - Fixed bug preventing sub fields from being reused across multiple parents.
* Fix - Fixed bug causing the `get_sub_field()` function to fail if a tab field exists with the same name as the selected field.
* Fix - Fixed bug corrupting field settings since WP 5.1 when instructions contain `< a target="" >`.
* Fix - Fixed bug in Gutenberg where custom metabox location (acf_after_title) did not show on initial page load.
* Fix - Fixed bug causing issues when importing/syncing multiple field groups which contain a clone field.
* Fix - Fixed bug preventing the AMP plugin preview from working.
* Dev - Added new 'pre' filters to get, update and delete meta functions.
* i18n - Update Turkish translation thanks to Emre Erkan.

= 5.7.12 =
*Release Date - 15 February 2019*

* Fix - Added missing function `register_field_group()`.
* Fix - Fixed PHP 5.4 error "Can't use function return value in write context".
* Fix - Fixed bug causing wp_options values to be slashed incorrectly.
* Fix - Fixed bug where "sync" feature imported field groups without fields.
* Fix - Fixed bug preventing `get_field_object()` working with a field key.
* Fix - Fixed bug causing incorrect results in `get_sub_field()`.
* Fix - Fixed bug causing draft and preview issues with serialized values.
* Fix - Fixed bug causing reversed field group metabox order.
* Fix - Fixed bug causing incorrect character count when validating values.
* Fix - Fixed bug showing incorrect choices for post_template location rule.
* Fix - Fixed bug causing incorrect value retrieval after `switch_to_blog()`.
* i18n - Updated Persian translation thanks to Majix.

= 5.7.11 =
*Release Date - 11 February 2019*

* New - Added support for persistent object caching.
* Fix - Fixed PHP error in `determine_locale()` affecting AJAX requests.
* Fix - Fixed bug affecting dynamic metabox check when selecting "default" page template.
* Fix - Fixed bug where tab fields did not render correctly within a dynamic metabox.
* Tweak - Removed language fallback from "zh_TW" to "zh_CN".
* Dev - Refactored various core functions.
* Dev - Added new hook variation functions `acf_add_filter_variations()` and `acf_add_action_variations()`.
* i18n - Updated Portuguese language thanks to Pedro Mendonça.
* i18n - Updated German translation thanks to Ralf Koller.
* i18n - Updated Swiss German translation thanks to Raphael Hüni.

= 5.7.10 =
*Release Date - 16 January 2019*

* Fix - Fixed bug preventing metaboxes from saving if validation fails within Gutenberg.
* Fix - Fixed bug causing unload prompt to show incorrectly within Gutenberg.
* Fix - Fixed JS error when selecting taxonomy terms within Gutenberg.
* Fix - Fixed bug causing jQuery sortable issues within other plugins.
* Tweak - Improved loading translations by adding fallback from region to country when .mo file does not exit.
* Tweak - Improved punctuation throughout admin notices.
* Tweak - Improved performance and accuracy when loading a user field value.
* Dev - Added filter 'acf/get_locale' to customize the locale used to load translations.
* Dev - Added filter 'acf/allow_unfiltered_html' to customize if current user can save unfiltered HTML.
* Dev - Added new data storage functions `acf_register_store()` and `acf_get_store()`.
* Dev - Moved from .less to .scss and minified all css.
* i18n - Updated French translation thanks to Maxime Bernard-Jacquet.
* i18n - Updated Czech translation thanks to David Rychly.

= 5.7.9 =
*Release Date - 17 December 2018*

* Fix - Added custom metabox location (acf_after_title) compatibility with Gutenberg.
* Fix - Added dynamic metabox check compatibility with Gutenberg.
* Fix - Fixed bug causing required date picker fields to prevent form submit.
* Fix - Fixed bug preventing multi-input values from saving correctly within media modals.
* Fix - Fixed bug where `acf_form()` redirects to an incorrect URL for sub-sites.
* Fix - Fixed bug where breaking out of a sub `have_rows()` loop could produce undesired results.
* Dev - Added filter 'acf/connect_attachment_to_post' to prevent connecting attachments to posts.
* Dev - Added JS filter 'google_map_autocomplete_args' to customize Google Maps autocomplete settings.

= 5.7.8 =
*Release Date - 7 December 2018*

* Fix - Fixed vulnerability allowing author role to save unfiltered HTML values.
* Fix - Fixed all metaboxes appearing when editing a post in WP 5.0.
* i18n - Updated Polish translation thanks to Dariusz Zielonka.
* i18n - Updated Czech translation thanks to Veronika Hanzlíková.
* i18n - Update Turkish translation thanks to Emre Erkan.
* i18n - Updated Portuguese language thanks to Pedro Mendonça.

= 5.7.7 =
*Release Date - 1 October 2018*

* Fix - Fixed various plugin update issues.
* Tweak - Added 'language' to Google Maps API url.
* Dev - Major improvements to the `acf.models.Postbox` model.
* Dev - Added JS filter 'check_screen_args'.
* Dev - Added JS action 'check_screen_complete'.
* Dev - Added action 'acf/options_page/submitbox_before_major_actions'.
* Dev - Added action 'acf/options_page/submitbox_major_actions'.
* i18n - Updated Portuguese language thanks to Pedro Mendonça

= 5.7.6 =
*Release Date - 12 September 2018*

* Fix - Fixed unload prompt not working.
* Dev - Reduced number of queries needed to populate the relationship field taxonomy filter.
* Dev - Added 'nav_menu_item_id' and 'nav_menu_item_depth' to get_field_groups() query.
* Dev - Reordered various actions and filters for more usefulness.
* i18n - Updated Polish language thanks to Dariusz Zielonka

[View the full changelog](https://www.advancedcustomfields.com/changelog/)

== Upgrade Notice ==
