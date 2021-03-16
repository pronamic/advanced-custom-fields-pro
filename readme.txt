=== Advanced Custom Fields Pro ===
Contributors: elliotcondon
Tags: acf, fields, custom fields, meta, repeater
Requires at least: 4.7
Tested up to: 5.6
Requires PHP: 5.6
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

= 5.9.5 =
*Release Date - 11 February 2021*

* Fix - Fixed regression preventing blocks from loading correctly within the editor in WordPress 5.5.
* Fix - Fixed bug causing incorrect post_status properties when restoring a Field Group from trash in WordPress 5.6.
* Fix - Fixed edge case bug where a taxonomy named "options" could interfere with saving and loading option values.
* Fix - Fixed additional PHP 8.0 warnings.
* i18n - Updated Finnish translation thanks to Mikko Kekki

= 5.9.4 =
*Release Date - 14 January 2021*

* Enhancement - Added PHP validation for the Email field (previously relied solely on browser validation).
* Fix - Added support for PHP 8.0 (fixed logged warnings).
* Fix - Added support for jQuery 3.5 (fixed logged warnings).
* Fix - Fixed bug causing WYSIWYG field to appear unresponsive within the Gutenberg editor.
* Fix - Fixed regression preventing "blog_%d" and "site_%d" as valid `$post_id` values for custom Taxonomy terms.
* Fix - Fixed bug causing Radio field label to select first choice.
* Fix - Fixed bug preventing preloading blocks that contain multiple parent DOM elements.
* i18n - Updated Japanese translation thanks to Ryo Takahashi.
* i18n - Updated Portuguese translation thanks to Pedro Mendonça.

= 5.9.3 =
*Release Date - 3 November 2020*

* Fix - Fixed bug causing Revision meta to incorrectly update the parent Post meta.
* Fix - Fixed bug breaking "Filter by Post Type" and "Filter by Taxonomy" Field settings.

= 5.9.2 =
*Release Date - 29 October 2020*

* Enhancement - Added experiment for preloading block HTML and reducing AJAX requests on page load.
* Fix - Added boolean attribute value detection to JSX parser (fixes issue with templateLock="false").
* Fix - Added "dateTime" attribute to JSX parser ruleset.
* Fix - Fixed unresponsive Select2 instances after duplicating a row or layout.
* Fix - Added missing Color Picker script translations for previous WordPress versions.
* Fix - Fixed bug in Clone Field causing potential PHP error if cloning a Field Group that no longer exists.
* Fix - Fixed PHP warning logged when comparing a revision that contains values for a Field that no longer exist.
* Dev - Added `$wp_block` parameter to block render_callback and render_template (unavailable during AJAX preview requests).
* Dev - Deprecated `acf_get_term_post_id()` function.

= 5.9.1 =
*Release Date - 8 September 2020*

* Fix - Fixed guten-bug causing "Preview Post" button to publish changes.
* Fix - Fixed guten-bug causing JS errors when editing with Elementor or Beaver Builder.
* Fix - Fixed bug in Color Picker field causing JS error on front-end forms.
* Fix - Fixed bug in Post Taxonomy location rule causing incomplete list of rule choices.
* Fix - Reverted Local JSON "save to source path" enhancement due to DX feedback. 
* i18n - Updated Indonesian translations thanks to Rio Bahtiar.
* i18n - Updated Turkish translation thanks to Emre Erkan.

= 5.9.0 =
*Release Date - 17 August 2020*

* Enhancement - New Field Groups admin.
    * Added toolbar across all ACF admin pages.
    * Added new table columns: Description, Key, Location, Local JSON.
    * Added popup modal to review Local JSON changes before sync.
    * Added visual representation of where Field Groups will appear.
    * Added new help tab.
    * Simplified layout.
* Enhancement - New ACF Blocks features.
    * Added support for Inner Blocks.
    * Added new "jsx" setting.
    * Added new "align_text" settings.
    * Added new "align_content" settings.
* Enhancement - Added duplicate functionality for Repeater and Flexible Content fields.
* Enhancement - Added PHP validation support for Gutenberg.
* Enhancement - Added ability to bypass confirmation tooltips (just hold shift).
* Enhancement - Local JSON files now save back to their loaded source path (not "save_json" setting).
* Tweak - Replaced all custom icons with dashicons.
* Tweak - Changed custom post status label from "Inactive" to "Disabled".
* Tweak - Improved styling of metaboxes positioned in the block editor sidebar.
* Fix - Improved AJAX request efficiency when editing block className or anchor attributes.
* Fix - Fixed bug causing unresponsive WYSIWYG fields after moving a block via the up/down arrows.
* Fix - Fixed bug causing HTML to jump between multiple instances of the same Reusable Block.
* Fix - Fixed bug sometimes displaying validation errors when saving a draft.
* Fix - Fixed bug breaking Image field UI when displaying a scaled portrait attachment.
* Fix - Fixed bug in Link field incorrectly treating the "Cancel" button as "Submit".
* Fix - Fixed bug where a sub field within a collapsed Repeater row did not grow to the full available width.
* Fix - Ensured all archive URLs shown in the Page Link field dropdown are unique.
* Fix - Fixed bug causing incorrect conditional logic settings on nested fields when duplicating a Field Group.
* Fix - Fixed bug causing license activation issues with some password management browser extensions.
* Dev - Major improvements to `ACF_Location` class.
* Dev - Refactored all location classes to optimize performance.
* Dev - Extracted core JavaScript from "acf-input.js" into a separate "acf.js" file.
* Dev - Field Group export now shows "active" attribute as bool instead of int.
* Dev - Added filter "acf/get_object_type" to customize WP object information such as "label" and "icon".
* Dev - Added action "acf/admin_print_uploader_scripts" fired when printing uploader (WP media) scripts in the footer.
* Dev - Added filters "acf/pre_load_attachment" and "acf/load_attachment" to customize attachment details.
* Dev - Added filter "acf/admin/toolbar" to customize the admin toolbar items.
* Dev - Added new JS actions "duplicate_fields" and "duplicate_field" fired when duplicating a row.
* i18n - Changed Croatian locale code from "hr_HR to "hr".
* i18n - Updated Portuguese translation thanks to Pedro Mendonça.
* i18n - Updated French Canadian translation thanks to Bérenger Zyla.
* i18n - Updated French translation thanks to Maxime Bernard-Jacquet.
* i18n - Updated German translations thanks to Ralf Koller.

= 5.8.14 =
*Release Date - 13 August 2020*

* Fix - Fixed bug breaking ACF Block `$is_preview` parameter in WordPress 5.5.
* Fix - Fixed bug breaking seamless postbox style in WordPress 5.5.

= 5.8.13 =
*Release Date - 10 August 2020*

* Tweak - Added styling compatibility for WordPress 5.5.
* Fix - Implemented new `wp_filter_content_tags()` function in "acf_the_content" filter.
* i18n - Updated Arabic translation thanks to Karim Ramadan.

= 5.8.12 =
*Release Date - 10 June 2020*

* Fix - Improved string escaping in Select2 drop-downs to address XSS concerns.
* Fix - Fixed bug causing PHP error when updating the settings of a Checkbox field.
* Fix - Fixed bug causing WYSIWYG field to hide when toggling between Document and Block tabs within the Block editor.
* Fix - Fixed bug incorrectly validating the length of Text and Textarea field values that contained HTML entities.

= 5.8.11 =
*Release Date - 12 May 2020*

* Fix - Fixed bug in ACF Blocks where "inserter examples" and "block templates" did not load the defined "data".
* Fix - Reverted "fix" in 5.8.9 regarding Taxonomy fields saving terms to non "post" objects.
* Fix - Fixed bug allowing the Image field to save the value of a deleted attachment.
* Fix - Improved Select field `format_value()` behaviour to correctly cast value types depending on the "multiple" setting.
* Tweak - Changed language fallback for "zh_HK" to "zh_TW".
* Tweak - Changed Time Picker field settings to display in a localized format via `date_i18n()`.
* Tweak - Improved CSS styling of menu item fields.
* i18n - Updated Finnish translation thanks to Mikko Kekki
* i18n - Updated French translation thanks to Maxime Bernard-Jacquet.
* i18n - Updated Turkish translation thanks to Emre Erkan.

= 5.8.10 =
*Release Date - 12 May 2020*

* See hotfix version 5.8.11 for release notes.

= 5.8.9 =
*Release Date - 26 March 2020*

* Fix - Fixed bug in ACF Blocks causing default "align" property to be ignored.
* Fix - Fixed bug allowing Taxonomy field to save terms to a non "post" object.
* Tweak - Improved User field AJAX query performance.
* Tweak - Improved logic that determines width of Range field input.
* Tweak - Improved styling of ACF Blocks components.
* Dev - Added new "acf/register_block_type_args" filter.
* Dev - Added new generic ACF_Ajax_Query and ACF_Ajax_Query_Users classes.
* i18n - Updated French Canadian translation thanks to Bérenger Zyla.
* i18n - Updated Traditional Chinese translation thanks to Audi Lu.
* i18n - Updated German translation thanks to Ralf Koller.
* i18n - Updated Portuguese translation thanks to Pedro Mendonça.

= 5.8.8 =
*Release Date - 4 March 2020*

* Fix - Fixed bug in `have_rows()` function causing a PHP warning when no value is found.
* Fix - Fixed bug in Google Maps field causing marker to snap to nearest address.
* Fix - Avoid Nav Menu items displaying twice in WordPress 5.4.
* Tweak - Added place name data to Google Maps field value.
* Tweak - Improved performance of PHP registered fields.
* Dev - Added new "acf/prepare_field_group_for_import" filter.
* i18n - Added Traditional Chinese translation thanks to Audi Lu.
* i18n - Added Catalan translation thanks to Jordi Tarrida.
* i18n - Updated French translation thanks to Maxime Bernard-Jacquet & Bérenger Zyla.

= 5.8.7 =
*Release Date - 12 November 2019*

* New - Updated admin CSS for new WordPress 5.3 styling.
* Fix - Fixed various issues affecting dynamic metaboxes in the block editor (requires WordPress 5.3)
* Fix - Fixed performance issue when checking network sites for upgrades.
* Fix - Fixed Select2 clones appearing after duplicating a Relationship field.
* Tweak - Repeater field "Add row" icons will now hide when maximum rows are reached.
* Tweak - Removed ACF Blocks keyword limit for later versions of Gutenberg.

= 5.8.6 =
*Release Date - 24 October 2019*

* New - Added more data to Google Maps field value including place_id, street_name, country and more.
* Fix - Fixed bug in Gallery field incorrectly displaying .pdf attachments as icons.
* Fix - Fixed bug in Checkbox field missing "selected" class after "Toggle All".
* Dev - Added compatibility for Attachments in the Post Taxonomy location rule.
* Dev - Added missing return statement from `acf_get_form()` function.
* Dev - Added "google_map_result" JS filter.

= 5.8.5 =
*Release Date - 8 October 2019*

* New - Added new choice "Add" to the User Form location rule.
* New - Optimized `acf_form()` logic when used in combination with `acf_register_form()`.
* Fix - Fixed bug causing incorrect field order after sync.
* Fix - Fixed bug reverting the first field type to Text in Firefox version 69.0.1.
* Fix - Fixed bug causing tinymce issues when changing between block modes.
* Fix - Fixed bug preventing block registration when category does not exist.
* Fix - Fixed bug preventing block registration when no icon is declared.
* Dev - Added RegExp compatibility for innerBlocks.

= 5.8.4 =
*Release Date - 3 September 2019*

* New - Optimized Relationship field by delaying AJAX call until UI is visible.
* Fix - Fixed bug incorrectly escaping HTML in the Link field title.
* Fix - Fixed bug showing Discussion and Comment metaboxes for newly imported field groups.
* Fix - Fixed PHP warning when loading meta from Post 0.
* Dev - Ensure Checkbox field value is an array even when empty.
* Dev - Added new `ACF_MAJOR_VERSION` constant.

= 5.8.3 =
*Release Date - 7 August 2019*

* Tweak - Changed Options Page location rules to show "page_title" instead of "menu_title".
* Fix - Fixed bug causing Textarea field to incorrectly validate maxlength.
* Fix - Fixed bug allowing Range field values outside of the min and max settings.
* Fix - Fixed bug in block RegExp causing some blocks to miss the "acf/pre_save_block" filter.
* Dev - Added `$block_type` parameter to block settings "enqueue_assets" callback.
* i18n - Added French Canadian language thanks to Bérenger Zyla.
* i18n - Updated French language thanks to Bérenger Zyla.

= 5.8.2 =
*Release Date - 15 July 2019*

* Fix - Fixed bug where validation did not prevent new user registration.
* Fix - Fixed bug causing some "reordered" metaboxes to not appear in the Gutenberg editor.
* Fix - Fixed bug causing WYSIWYG field with delayed initialization to appear blank.
* Fix - Fixed bug when editing a post and adding a new tag did not refresh metaboxes.
* Dev - Added missing `$value` parameter in "acf/pre_format_value" filter.

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
