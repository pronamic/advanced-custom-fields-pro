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

= 5.7.5 =
*Release Date - 6 September 2018*

* Fix - Fixed bug causing multisite login redirect issues.
* Fix - Fixed bug causing validation issues in older versions of Firefox.
* Fix - Fixed bug causing duplicate Select2 instances when adding a widget via drag/drop.
* Dev - Improved WPML compatibility by using `$sitepress->get_current_language()` instead of `ICL_LANGUAGE_CODE`.
* Dev - Improved validation JS with new Validator model and logic.

= 5.7.4 =
*Release Date - 30 August 2018*

* Fix - Fixed bug causing field groups to disappear when selecting a taxonomy term with WPML active.
* Tweak - Added more Dark Mode styles.
* Tweak - Improved DB upgrade prompt, functions and AJAX logic.
* Tweak - Improved the "What's New" admin page seen after DB Upgrade.
* Dev - Added new location rules filters.

= 5.7.3 =
*Release Date - 20 August 2018*

* New - Added Dark Mode styles for the [Dark Mode Plugin](https://en-au.wordpress.org/plugins/dark-mode/).
* New - Added "Value Contains" condition to the Select field type.
* New - Added support for the WooCommerce product type dropdown to trigger "update metaboxes".
* Tweak - Improved acf.screen model responsible for "updating metaboxes" when changing post data.
* Tweak - Removed user fields from the multisite "Add New User" page. 
* Fix - Fixed bug preventing some tinymce customizations from working.
* Fix - Fixed JS bug throwing "preference" error in console.
* Dev - Added action 'acf/enqueue_uploader' triggered after the hidden "ACF Content" editor is rendered.

= 5.7.2 =
*Release Date - 6 August 2018*

* Fix - Fixed bug preventing the Google Maps Field address from being customized.
* Fix - Improved logic to request and cache plugin update information.
* Fix - Fixed bug preventing JS initialization when editing widgets in accessibility mode.
* Fix - Added missing $parent argument to term_exists() function when adding a new term via taxonomy field popup.
* Fix - Fixed bug where nested Group Fields did not delete their values.
* Fix - Fixed JS error thrown by localStorage if cookies are not enabled.
* Dev - Bumped minimum WP version requirement to 4.4.
* Dev - Added action 'wp_nav_menu_item_custom_fields' for compatibility with other plugins modifying the menu walker class.
* Dev - Added 'multiple' to the allowed attributes for an email field.
* Dev - Added new ACF_Ajax class for upcoming features.

= 5.7.1 =
* Core: Minor fixes and improvements

= 5.7.0 =
* Core: Major JavaScript updates
* Core: Improved conditional logic with new types and more supported fields
* Core: Improved localization and internationalization
* Repeater field: Improved logic that remembers collapsed row states
* Repeater field: Added support to collapse multiple rows (hold shift)
* API: Improved lookup to find fields without a reference value
* Language: Added Croatian translation - Thanks to Vlado Bosnjak
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Updated Romanian translation - thanks to Ionut Staicu
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Arabic translation - thanks to Karim Ramadan
* Language: Updated Portuguese translation - thanks to Pedro Mendonça

= 5.6.10 =
* Core: Minor fixes and improvements

= 5.6.9 =
* User field: Added new 'Return Format' setting (Array, Object, ID)
* Core: Added basic compatibility with Gutenberg - values now save
* Core: Fixed bug affecting the loading of fields on new Menu Items
* Core: Removed private ('show_ui' => false) post types from the 'Post Type' location rule choices
* Core: Minor fixes and improvements
* Language: Updated French translation - thanks to Maxime Bernard-Jacquet

= 5.6.8 =
* API: Fixed bug causing have_rows() to fail with PHP 7.2
* Core: Fixed bug causing "Add new term" form to hide after submit
* Core: Minor fixes and improvements
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Arabic translation - thanks to Karim Ramadan
* Language: Updated Spanish translation - thanks to Luis Rull Muñoz
* Language: Updated Persian translation - thanks to Majix

= 5.6.7 =
* Fixed an assortment of bugs found in 5.6.6

= 5.6.6 =
* Accordion field: Added new field type
* Tab field: Added logic to remember active tabs
* WYSIWYG field: Fixed JS error in quicktags initialization
* Core: Fixed issue preventing conditional logic for menu item fields
* Core: Fixed issue preventing JS initialization for newly added menu items.
* Core: Allow whitespace in input value (previously trimmed)
* Core: Minor fixes and improvements
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Updated Brazilian Portuguese translation - thanks to Rafael Ribeiro
* Language: Updated Dutch translation - thanks to Derk Oosterveld
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Persian translation - thanks to Kamel Kimiaei
* Language: Updated Swiss German translation - thanks to Raphael Hüni
* Language: Updated Arabic translation - thanks to Karim Ramadan

= 5.6.5 =
* API: Added new 'kses' setting to the `acf_form()` function
* Core: Added new 'Admin Tools' framework (includes design refresh)
* Core: Minor fixes and improvements
* Language: Update Ukrainian translation - thanks to Jurko Chervony
* Language: Update Russian translation - thanks to Andriy Toniyevych
* Language: Update Hebrew translation - thanks to Itamar Megged

= 5.6.4 =
* Google Map field: Fixed bug causing invalid url to JavaScript library
* WYSIWYG field: Fixed minor z-index and drag/drop bugs
* Group field: Fixed bug causing incorrect export settings
* Core: Fixed bug in 'Post Taxonomy' location rule ignoring selected terms during AJAX callback
* Core: Fixed bug preventing a draft to validate with required fields
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Update Turkish translation - thanks to Emre Erkan
* Language: Updated Chinese translation - thanks to Wang Hao
* Language: Update Hebrew translation - thanks to Itamar Megged

= 5.6.3 =
* Button Group field: Added new field type
* Range field: Added missing 'step' attribute to number input
* Range field: Added width to number input based on max setting
* Basic fields: Added missing 'required' attribute to inputs
* Basic fields: Removed empty attributes from inputs
* API: Fixed `get_fields()` bug ignoring fields starting with an underscore
* Core: Minor fixes and improvements
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated French translation - thanks to Maxime Bernard-Jacquet
* Language: Updated Finnish translation - thanks to Sauli Rajala
* Language: Updated German translation - thanks to Ralf Koller

= 5.6.2 =
* Range field: Added new field type
* Clone field: Fixed bug causing value update issues for 'seamless' + widgets / nave menu items
* Location: Added parent theme's post templates to 'post template' location rule
* Location: Fixed bug causing 'nav menu' location rule to fail during AJAX (add new item)
* Core: Fixed PHP errors in customizer when editing non ACF panels
* Core: Fixed bug casing backslash character to break fields / field groups
* Core: Many minor bug fixes
* Language: Updated Romanian translation - thanks to Ionut Staicu
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Update Turkish translation - thanks to Emre Erkan
* Language: Updated Russian translation - Thanks to Алекс Яровиков
* Language: Updated French translation - Thanks to Julie Arrigoni

= 5.6.1 =
* Fixed an assortment of bugs found in 5.6.0

= 5.6.0 =
* Link field: Added new field type
* Group field: Added new field type
* API: Improved `have_rows()` function to work with clone and group field values
* Core: Added new location for Menus
* Core: Added new location for Menu Items
* Core: Added types to Attachment location rule - thanks to Jan Thomas
* Core: Added "Confirm Remove" tooltips
* Core: Updated Select2 JS library to v4
* Core: Minor fixes and improvements

= 5.5.14 =
* Core: Minor bug fixes

= 5.5.13 =
* Clone field: Improved 'Fields' setting to show all fields within a matching field group search
* Flexible Content field: Fixed bug causing 'layout_title' filter to fail when field is cloned
* Flexible Content field: Added missing 'translate_field' function
* WYSIWYG field: Fixed JS error when using CKEditor plugin
* Date Picker field: Improved 'Display Format' and 'Return Format' settings UI
* Time Picker field: Same as above
* Datetime Picker field: Same as above
* Core: Added new 'remove_wp_meta_box' setting
* Core: Added constants ACF, ACF_PRO, ACF_VERSION and ACF_PATH
* Core: Improved compatibility with Select2 v4 including sortable functionality
* Language: Updated Portuguese translation - thanks to Pedro Mendonça

= 5.5.12 =
* Tab field: Allowed HTML within field label to show in tab
* Core: Improved plugin update class
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Brazilian Portuguese translation - thanks to Rafael Ribeiro

= 5.5.11 =
* Google Map field: Added new 'google_map_init' JS action
* Core: Minor fixes and improvements
* Language: Updated Swiss German translation - thanks to Raphael Hüni
* Language: Updated French translation - thanks to Maxime Bernard-Jacquet

= 5.5.10 =
* API: Added new functionality to the `acf_form()` function:
* - added new 'html_updated_message' setting
* - added new 'html_submit_button' setting
* - added new 'html_submit_spinner' setting
* - added new 'acf/pre_submit_form' filter run when form is successfully submit (before saving $_POST)
* - added new 'acf/submit_form' action run when form is successfully submit (after saving $_POST)
* - added new '%post_id%' replace string to the 'return' setting
* - added new encryption logic to prevent $_POST exploits
* - added new `acf_register_form()` function
* Core: Fixed bug preventing values being loaded on a new post/page preview
* Core: Fixed missing 'Bulk Actions' dropdown on sync screen when no field groups exist
* Core: Fixed bug ignoring PHP field groups if exists in JSON
* Core: Minor fixes and improvements

= 5.5.9 =
* Core: Fixed bug causing ACF4 PHP field groups to be ignored if missing ‘key’ setting

= 5.5.8 =
* Flexible Content: Added logic to better 'clean up' data when re-ordering layouts
* oEmbed field: Fixed bug causing incorrect width and height settings in embed HTML
* Core: Fixed bug causing incorrect Select2 CSS version loading for WooCommerce 2.7
* Core: Fixed bug preventing 'min-height' style being applied to floating width fields
* Core: Added new JS 'init' actions for wysiwyg, date, datetime, time and select2 fields
* Core: Minor fixes and improvements

= 5.5.7 =
* Core: Fixed bug causing `get_field()` to return incorrect data for sub fields registered via PHP code.

= 5.5.6 =
* Core: Fixed bug causing license key to be ignored after changing url from http to https
* Core: Fixed Select2 (v4) bug where 'allow null' setting would not correctly save empty value
* Core: Added new 'acf/validate_field' filter
* Core: Added new 'acf/validate_field_group' filter
* Core: Added new 'acf/validate_post_id' filter
* Core: Added new 'row_index_offset' setting
* Core: Fixed bug causing value loading issues for a taxonomy term in WP < 4.4
* Core: Minor fixes and improvements

= 5.5.5 =
* File field: Fixed bug creating draft post when saving an empty value
* Image field: Fixed bug mentioned above

= 5.5.4 =
* File field: Added logic to 'connect' selected attachment to post (only if attachment is not 'connected')
* File field: Removed `filesize()` call causing performance issues with externally hosted attachments
* File field: Added AJAX validation to 'basic' uploader
* Image field: Added 'connect' logic mentioned above
* Image field: Added AJAX validation mentioned above
* True false field: Improved usability by allowing 'tab' key to focus element (use space or arrow keys to toggle)
* Gallery field: Fixed bug causing unsaved changes in sidebar to be lost when selecting another attachment
* API: Fixed `add_row()` and `add_sub_row()` return values (from true to new row index)
* Core: Improved `get_posts()` query speeds by setting 'update_cache' settings to false
* Core: Allowed 'instruction_placement' setting on 'widget' forms (previously set always to 'below fields')
* Core: Removed 'ACF PRO invalid license nag' and will include fix for 'protocol change' in next release
* Language: Updated French translation - thanks to Martial Parfait

= 5.5.3 =
* Options page: Fixed bug when using WPML in multiple tabs causing incorrect 'lang' to be used during save.
* Core: Added support with new `get_user_locale()` setting in WP 4.7
* Core: Improved efficiency of termmeta DB upgrade logic
* Core: Minor fixes and improvements

= 5.5.2 =
* Tab field: Fixed bug causing value loading issues for field's with the same name
* Repeater field: Fixed bug in 'collapsed' setting where field key was shown instead of field label

= 5.5.1 =
* Select field: Fixed bug preventing some field settings from being selected
* Date picker field: Improved compatibility with customized values
* Core: Added new 'enqueue_datepicker' setting which can be used to prevent the library from being enqueued
* Core: Added new 'enqueue_datetimepicker' setting which can be used to prevent the library from being enqueued
* Core: Minor fixes and improvements

= 5.5.0 =
* True False field: Added new 'ui' setting which renders as a toggle switch
* WYSIWYG field: Added new 'delay' setting which delays tinymce initialization until the field is clicked
* WYSIWYG field: Added compatibility for WP 4.7 toolbar buttons order
* Checkbox field: Added new 'allow_custom' and 'save_custom' settings allowing you to add custom choices
* Select field: Fixed bug where Select2 fields did not correctly use the ‘allow null’ setting
* Clone field: Fixed bug causing save/load issues found when 2 sub fields clone in the same field/group.
* Flexible Content field: Improved popup style and validation messages
* Google Map field: Prevent scroll zoom
* Date picker field: Added better compatibility logic for custom 'date_format' setting found in version < 5.0.0
* API: acf_form() 'id' setting is now used as 'id' attribute in <form> element
* Options page: Fixed incorrect redirect URL from a sub options page
* Field group: Added new 'post_template' location rule (requires WP 4.7)
* Core: Added support for the wp_termmeta table (includes DB upgrade)
* Core: Added new 'select_2_version' setting which can be changed between 3 and 4
* Core: Added new 'enqueue_select2' setting which can be used to prevent the library from being enqueued
* Core: Added new 'enqueue_google_maps' setting which can be used to prevent the library from being enqueued
* Core: Minor fixes and improvements
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Norwegian translation - thanks to Havard Grimelid
* Language: Updated Swedish translation - thanks to Jonathan de Jong
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Italian translation - thanks to Davide Pantè
* Language: Updated Swiss German translation - thanks to Raphael Hüni

= 5.4.8 =
* Flexible Content field: Fixed bug in 'layout_title' filter preventing values being loaded correctly

= 5.4.7 =
* Time Picker field: Fixed bug preventing default time from being selected
* Date Picker field: Improved compatibility with unix timestamp values
* File field: Fixed validation bugs when used as a sub field (multiple selection)
* Select field: Fixed bug incorrectly allowing a disabled field (hidden by conditional logic) to save values
* API: Added new `add_sub_row()` function
* API: Added new `update_sub_row()` function
* API: Added new `delete_sub_row()` function
* Core: Fixed bug causing 'sync' issues with sub clone fields
* Core: Minor fixes and improvements

= 5.4.6 =
* Gallery field: Fixed bug where open sidebar fields were saved to post
* Flexible Content field: Fixed bug causing Google map render issue within collapsed layout
* Flexible Content field: Fixed bug during 'duplicate layout' where radio input values were lost
* API: Fixed bug causing `get_row(true)` to return incorrect values
* Core: Fixed bug where preview values did not load for a draft post
* Core: Added notice when PRO license fails to validate URL
* Core: Fixed bug where conditional logic would incorrectly enable select elements
* Core: Minor fixes and improvements

= 5.4.5 =
* API: Fixed bug in `acf_form()` where AJAX validation ignored 'post_title'
* API: Improved `update_field()` when saving a new value (when reference value does not yet exist)
* Core: Added search input & toggle to admin field groups list
* Core: Fixed bug where preview values did not load for a draft post

= 5.4.4 =
* WYSIWYG field: Fixed JS error when 'Disable the visual editor when writing' is checked

= 5.4.3 =
* WYSIWYG field: Fixed JS bug (since WP 4.6) causing conflicts with editor plugins
* Google Maps field: Fixed JS error conflict with Divi theme
* Radio field: Fixed bug (Chrome only) ignoring default values in cloned sub fields
* Core: Fixed `wp_get_sites()` deprecated error (since WP 4.6) shown in network admin

= 5.4.2 =
* API: Fixed bug preventing post_title and post_content values saving in `acf_form()`

= 5.4.1 =
* API: Fixed bug causing `get_fields('options')` to return false
* Core: Fixed bug causing `get_current_screen()` to throw PHP error
* Core: Fixed bug causing 'Preview Post' to load empty field values

= 5.4.0 =
* Clone field: Added new field type (https://www.advancedcustomfields.com/resources/clone/)
* Gallery field: Removed 'Preview Size' setting and improved UI
* Taxonomy field: Added compatibility to save/load terms to user object
* Select field: Added new 'Return Format' setting
* Radio field: Added new 'Return Format' setting
* Checkbox field: Added new 'Return Format' setting
* Page link field: Added new 'Allow Archives URLs' setting
* Core: Fixed plugin update bug delaying updates
* Core: Fixed bug when editing field settings in Chrome causing required setting to self toggle
* Core: Improved speed and fixed bugs when creating and restoring revisions
* Core: Minor fixes and improvements
* Language: Updated Portuguese translation - thanks to Pedro Mendonça
* Language: Updated Brazilian Portuguese translation - thanks to Augusto Simão
* Language: Updated Dutch translation - thanks to Derk Oosterveld
* Language: Updated Persian translation - thanks to Kamel
* Language: Updated German translation - thanks to Ralf Koller
* Language: Updated Swiss German translation - thanks to Raphael Hüni

View full changelog: https://www.advancedcustomfields.com/changelog/

== Upgrade Notice ==

= 5.2.7 =
* Field class names have changed slightly in v5.2.7 from `field_type-{$type}` to `acf-field-{$type}`. This change was introduced to better optimize JS performance. The previous class names can be added back in with the following filter: https://www.advancedcustomfields.com/resources/acfcompatibility/

= 3.0.0 =
* Editor is broken in WordPress 3.3

= 2.1.4 =
* Adds post_id column back into acf_values