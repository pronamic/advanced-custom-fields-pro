=== Advanced Custom Fields PRO ===
Contributors: elliotcondon
Tags: acf, fields, custom fields, meta, repeater
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 6.2.10
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced Custom Fields (ACF) helps you easily customize WordPress with powerful, professional and intuitive fields. Proudly powering over 2 million websites, Advanced Custom Fields is the plugin WordPress developers love.

== Description ==

Advanced Custom Fields (ACF) turns WordPress sites into a fully-fledged content management system by giving you all the tools to do more with your data.

Use the ACF plugin to take full control of your WordPress edit screens, custom field data, and more.

**Add fields on demand.**
The ACF field builder allows you to quickly and easily add fields to WP edit screens with only the click of a few buttons! Whether it's something simple like adding an “author” field to a book review post, or something more complex like the structured data needs of an ecommerce site or marketplace, ACF makes adding fields to your content model easy.

**Add them anywhere.**
Fields can be added all over WordPress including posts, pages, users, taxonomy terms, media, comments and even custom options pages! It couldn't be simpler to bring structure to the WordPress content creation experience.

**Show them everywhere.**
Load and display your custom field values in any theme template file with our hassle-free, developer friendly functions! Whether you need to display a single value or generate content based on a more complex query, the out-of-the-box functions of ACF make templating a dream for developers of all levels of experience.

**Any Content, Fast.**
Turning WordPress into a true content management system is not just about custom fields. Creating new custom post types and taxonomies is an essential part of building custom WordPress sites. Registering post types and taxonomies is now possible right in the ACF UI, speeding up the content modeling workflow without the need to touch code or use another plugin.

**Simply beautiful and intentionally accessible.**
For content creators and those tasked with data entry, the field user experience is as intuitive as they could desire while fitting neatly into the native WordPress experience. Accessibility standards are regularly reviewed and applied, ensuring ACF is able to empower as close to anyone as possible.

**Documentation and developer guides.**
Over 10 plus years of vibrant community contribution alongside an ongoing commitment to clear documentation means that you'll be able to find the guidance you need to build what you want.

= Features =
* Simple & Intuitive
* Powerful Functions
* Over 30 Field Types
* Extensive Documentation
* Millions of Users

= Links =
* [Website](https://www.advancedcustomfields.com/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Website)
* [Documentation](https://www.advancedcustomfields.com/resources/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Website)
* [Support](https://support.advancedcustomfields.com)
* [ACF PRO](https://www.advancedcustomfields.com/pro/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade)

= PRO =
The Advanced Custom Fields plugin is also available in a professional version which includes more fields, more functionality, and more flexibility. The ACF PRO plugin features:

* The [Repeater Field](https://www.advancedcustomfields.com/resources/repeater/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade) allows you to create a set of sub fields which can be repeated again, and again, and again.
* [ACF Blocks](https://www.advancedcustomfields.com/resources/blocks/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade), a powerful PHP-based framework for developing custom block types for the WordPress Block Editor (aka Gutenberg).
* Define, create, and manage content with the [Flexible Content Field](https://www.advancedcustomfields.com/resources/flexible-content/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade), which provides for multiple layout and sub field options.
* Use the [Options Page](https://www.advancedcustomfields.com/resources/options-page/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade) feature to add custom admin pages to edit ACF fields.
* Build fully customisable image galleries with the [Gallery Field](https://www.advancedcustomfields.com/resources/gallery/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade).
* Unlock a more efficient workflow for managing field settings by reusing existing fields and field groups on demand with the [Clone Field](https://www.advancedcustomfields.com/resources/clone/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade).

[Upgrade to ACF PRO](https://www.advancedcustomfields.com/pro/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Pro%20Upgrade)

== Installation ==

From your WordPress dashboard

1. **Visit** Plugins > Add New
2. **Search** for "Advanced Custom Fields" or “ACF”
3. **Install and Activate** Advanced Custom Fields from your Plugins page
4. **Click** on the new menu item "ACF" and create your first custom field group, or register a custom post type or taxonomy.
5. **Read** the documentation to [get started](https://www.advancedcustomfields.com/resources/getting-started-with-acf/?utm_source=wordpress.org&utm_medium=free%20plugin%20listing&utm_campaign=ACF%20Website)


== Frequently Asked Questions ==

= What kind of support do you provide? =

**Support Forums.** Our ACF Community Forums provide a great resource for searching and finding previously answered and asked support questions. You may create a new thread on these forums, however, it is not guaranteed that you will receive an answer from our support team. This is more of an area for ACF developers to talk to one another, post ideas, plugins and provide basic help. [View the Support Forum](https://support.advancedcustomfields.com/)


== Screenshots ==

1. Simple & Intuitive

2. Made for Developers

3. All About Fields

4. Registering Custom Post Types

5. Registering Taxonomies


== Changelog ==

= 6.2.10 =
*Release Date 15th May 2024*

* Security Fix - ACF Blocks no longer allow render templates, or render or asset callbacks to be overridden in the block's attributes. For full information, please read [the release blog post](https://www.advancedcustomfields.com/blog/acf-pro-6-2-10-security-release/)

= 6.2.9 =
*Release Date 8th April 2024*

* Enhancement - The Select2 escapeMarkup function can now be overridden when initializing a custom Select2
* Fix - “Hide on Screen” settings are now correctly applied when using conditionally loaded field groups
* Fix - Field names are no longer converted to lowercase when editing the name
* Fix - Field group titles will no longer convert HTML entities into their encoded form

= 6.2.8 =
*Release Date 2nd April 2024*

* New - Support for the Block Bindings API in WordPress 6.5 with a new `acf/field` source. For more information on how to use this, please read [the release blog post](https://www.advancedcustomfields.com/blog/acf-6-2-8)
* New - Support for performance improvements for translations in WordPress 6.5
* Enhancement - A new JS filter, `select2_escape_markup` now allows fields to customize select2's HTML escaping behavior
* Fix - Options pages can no longer set to have a parent of themselves
* Fix - ACF PRO license activations on multisite subsite installs will now use the correct site URL
* Fix - ACF PRO installed on multisite installs will no longer try to check for updates resulting in 404 errors when the updates page is not visible
* Fix - ACF JSON no longer produces warnings on Windows servers when no ACF JSON folder is found
* Fix - Field and layout names can now contain valid non-ASCII characters
* Other - ACF PRO now requires a valid license to be activated in order to use PRO features. [Learn more](https://www.advancedcustomfields.com/resources/license-activations/)

= 6.2.7 =
*Release Date 27th February 2024*

* Security Fix - `the_field` now escapes potentially unsafe HTML as notified since ACF 6.2.5. For full information, please read [the release blog post](https://www.advancedcustomfields.com/blog/acf-6-2-7-security-release/)
* Security Fix - Field and Layout names are now enforced to alphanumeric characters, resolving a potential XSS issue
* Security Fix - The default render template for select2 fields no longer allows HTML to be rendered resolving a potential XSS issue
* Security Enhancement - A `acf/shortcode/prevent_access` filter is now available to limit what data the ACF shortcode is allowed to access
* Security Enhancement - i18n translated strings are now escaped on output
* Enhancement - ACF now universally uses WordPress file system functions rather than native PHP functions

= 6.2.6.1 =
*Release Date 7th February 2024*

* Fix - Fatal JS error no longer occurs when editing fields in the classic editor when Yoast or other plugins which load block editor components are installed
* Fix - Using `$escape_html` on get functions for array returning field types no longer produces an Array to string conversion error

= 6.2.6 =
*Release Date 6th February 2024*

* Enhancement - The `get_field()` and other `get_` functions now support an `escape_html` parameter which return an HTML safe field value
* Enhancement - The URL field will be now escaped with `esc_url` rather than `wp_kses_post` when returning an HTML safe value
* Fix - ACF fields will now correctly save into the WordPress created revision resolving issues with previews of drafts on WordPress 6.4 or newer
* Fix - Multisite subsites will now correctly be activated by the main site where the ACF PRO license allows, hiding the updates page on those subsites
* Fix - Field types in which the `required` property would have no effect (such as the tab, or accordion) will no longer show the option
* Fix - Duplicating a field group now maintains the current page of field groups being displayed
* Fix - Fields in ACF Blocks in edit mode in hybrid themes will now use ACF's styling, rather than some attributes being overridden by the theme
* Fix - Text in some admin notices will no longer overlap the dismiss button
* Fix - The word `link` is now prohibited from being used as a CPT name to avoid a WordPress core conflict
* Fix - Flexible content layouts can no longer be duplicated over their maximum count limit
* Fix - All ACF notifications shown outside of ACF's admin screens are now prefixed with the plugin name
* Fix - ACF no longer checks if a polyfill is needed for <PHP7 and the polyfill has been removed

= 6.2.5 =
*Release Date 16th January 2024*

* Security Fix - The ACF shortcode will now run all output through `wp_kses`, escaping unsafe HTML. This may be a breaking change to your site but is required for security, a message will be shown in WordPress admin if you are affected. Please see the [blog post for this release for more information.](https://www.advancedcustomfields.com/blog/acf-6-2-5-security-release/) Thanks to Francesco Carlucci via Wordfence for the responsible disclosure
* Security - ACF now warns via an admin message, when upcoming changes to `the_field` and `the_sub_field` may require theme changes to your site to avoid stripping unsafe HTML. Please see the [blog post for this release for more information](https://www.advancedcustomfields.com/blog/acf-6-2-5-security-release/)
* Security - Users may opt in to automatically escaping unsafe HTML via a new filter `acf/the_field/escape_html_optin` when using `the_field` and `the_sub_field` before this becomes default in an upcoming ACF release.

= 6.2.4 =
*Release Date 28th November 2023*

* Fix - Custom Post Types labels now match the WordPress 6.4 behavior for "Add New" labels
* Fix - When exporting both post types and taxonomies as PHP, taxonomies will now appear before post types, matching the order ACF registers them. This resolves issues where taxonomy slugs will not work in post type permalinks
* Fix - Advanced Settings for Taxonomies, Post Types or Options Pages now display with the correct top padding when toggled on
* Fix - When a parent option page is set to "Redirect to Child Page", the child page will now correctly show it's parent setting
* Fix - When activated as a must-use plugin, the ACF PRO "Updates" page is now visible. Use the existing `show_updates` setting to hide
* Fix - When activated as a must-use plugin, ACF PRO licenses defined in code will now correctly activate sites
* Fix - When `show_updates` is set or filtered to false, ACF PRO will now automatically still activate defined licenses
* i18n - Maintenance and internal upstream messages from the ACF PRO activation server are now translatable

= 6.2.3 =
*Release Date 15th November 2023*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-6-2-3/)
* New - An ACF Blocks specific JSON schema for block.json is now available on [GitHub](https://github.com/AdvancedCustomFields/schemas)
* New - Flexible Content fields now show the layout name in the layout's header bar and supports click-to-copy
* New - Duplicating Flexible Content layouts now appends "Copy" to their name and label, matching the behavior of field group duplication
* Enhancement - ACF PRO will now automatically attempt license reactivation when the site URL changes, e.g. after a site migration. This resolves issues where updates may fail
* Enhancement - Presentation setting for "High" placement of the Field Group made clear that it's not supported in the block editor
* Fix - `acf_format_date` now ensures the date parameter is valid to prevent fatal errors if other data types are passed in
* Fix - CPTs with a custom icon URL now display the posts icon in the location column of the field groups screen
* Fix - The ACF JSON import form will now disable on first submit resolving an issue where you could submit the form twice
* Fix - The "Add Row" button in the Flexible Content field now displays correctly when using nested layouts
* Fix - Warning and Error notices no longer flicker on ACF admin pages load
* i18n - ACF PRO license activation success and error messages are now translatable

= 6.2.2 =
*Release Date 25th October 2023*

* Enhancement - ACF Blocks which have not been initialized by the editor will now render correctly
* Enhancement - Added a new `acf/filesize` filter to allow third party media plugins to bypass ACF calling `filesize()` on attachments with uncached file sizes, which may result in a remote download if offloaded
* Enhancement - ACF PRO license status and subscription expiry dates are now displayed on the “Updates” page
* Fix - Product pages for WooCommerce version 8.2 or newer now correctly support field group location rules
* Fix - Relationship field items can now be removed on mobile devices
* Fix - Color picker fields no longer autocomplete immediately after typing 3 valid hex characters
* Fix - Field settings no longer appear misaligned when the viewport is something other than 100%
* Fix - Select fields without an aria-label no longer throw a warning
* Fix - CPTs and Taxonomies with a custom text domain now export correctly when using PHP export

= 6.2.1.1 =
*Release Date 8th September 2023*

* Fix - Editing a field group no longer generates an error when UI options pages are disabled

= 6.2.1 =
*Release Date 7th September 2023*

* New - Options Pages created in the admin UI can now be assigned as child pages for any top-level menu item
* New - Added a "Title Placeholder" setting to ACF Post Types which filters the "Add title" text when editing posts
* Enhancement - ACF PRO will now warn when it can't update due to PHP version incompatibilities
* Enhancement - ACF PRO will now work correctly with WordPress automatic updates
* Enhancement - The internal ACF Blocks template attribute parser function `parseNodeAttr` can now be shortcut with the new `acf_blocks_parse_node_attr` filter.
* Enhancement - Removed legacy code for supporting WordPress versions under 5.8
* Fix - The "Menu Position" setting is no longer hidden for child options pages
* Fix - The tabs for the "Advanced" settings in Post Types and Taxonomies are now rendered inside a wrapper div
* Fix - Options pages will no longer display as a child page in the list view when set to a top level page after previously being a child
* Fix - Conflict with Elementor CSS breaking the ACF PRO banner
* Fix - Errors generated during the block editor's `savePost` function will no longer be caught and ignored by ACF

= 6.2.0 =
*Release Date 9th August 2023*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-6-2-0-released/)
* New - ACF now requires WordPress version 5.8 or newer, and PHP 7.0 or newer. View the [release post](https://www.advancedcustomfields.com/blog/acf-6-2-0-released/#version-requirements) for more information
* New - Bidirectional Relationships now supported for Relationship, Post Object, User and Taxonomy fields. View the [release post](https://www.advancedcustomfields.com/blog/acf-6-2-0-released/#bidirectional-relationships) for more information
* New - [Options Pages](https://www.advancedcustomfields.com/resources/options-page/) can now be registered and managed by the admin UI in ACF PRO
* New - Link to the [product feedback board](https://www.advancedcustomfields.com/feedback/) added to the plugin footer
* Enhancement - ACF JSON now supports multiple save locations (props Freddy Leitner)
* Enhancement - ACF Post Types and Taxonomies can now be duplicated
* Enhancement - The filename for JSON files can now be customized with the `acf/json/save_file_name` filter
* Fix - REST updates of fields with choices containing integer or mixed keys now behave correctly
* Fix - Using the `block_type_metadata_settings` PHP filter to add usesContext values no longer breaks ACF blocks
* Fix - Notice to import post types/taxonomies from CPTUI no longer flashes on page load
* Fix - Various buttons for fields in blocks now display correctly
* Fix - The settings for the DateTime field are no longer cut off when nested in several fields in the field group editor
* Fix - The newline added to the end of JSON files will now use `PHP_EOL` to detect the correct newline character with a filter `acf/json/eof_newline` to alter it.
* i18n - Updated French and Portuguese translations (Thanks to pedro-mendonca and maximebj)

= 6.1.8 =
*Release Date 3rd August 2023*

* Security Fix - This release resolves a stored XSS vulnerability in admin screens with ACF post type and taxonomy labels (Thanks to Satoo Nakano and Ryotaro Imamura)

= 6.1.7 =
*Release Date 27th June 2023*

* New - Added new capability settings for ACF taxonomies
* Enhancement - Added a new `acf/field_group/auto_add_first_field` filter which can be used to prevent new field groups from automatically adding a field
* Enhancement - Field setting labels now have standard capitalization in the field group editor
* Enhancement - Clone field now has a tutorial link
* Enhancement - "Exclude From Search" CPT setting now has an improved description
* Enhancement - The `acf_get_posts()` function now has `acf/acf_get_posts/args` and `acf/acf_get_posts/results` filters
* Enhancement - Added a new `acf/options_page/save` action hook that gets fired during save of ACF Options Pages
* Fix - Taxonomies are now initialized before post types, preventing some permalink issues
* Fix - Increased the taxonomy slug maximum length to 32 characters
* Fix - Extra tabs are no longer added to PHP exports with field settings containing multiple lines
* Fix - ACF admin assets now load when editing profile and users for a multisite network
* Fix - Blocks with recursive `render_callback` functions will no longer crash the editor
* Fix - JSON files now end in a new line for better compatibility with code editors
* i18n - `layout(s)` strings in Flexible Content fields are now translatable
* i18n - Updated Polish translations

= 6.1.6 =
*Release Date 4th May 2023*

* Security Fix - This release resolves an XSS vulnerability in ACF's admin pages (Thanks to Rafie Muhammad for the responsible disclosure)
* Fix - Duplicating fields in a new field group with field setting tabs disabled now behaves correctly

= 6.1.5 =
*Release Date 2nd May 2023*

* Enhancement - Creating a new field group from the post-save actions for a post type or taxonomy will automatically populate the field group title
* Enhancement - Empty values in list tables now display as a dash, rather than blank
* Enhancement - The `Generate PHP` export tool for field groups now displays the code wrapped in the `acf/include_fields` action hook to match the recommended way of using `acf_add_local_field_group`, and the code is formatted correctly
* Enhancement - Post count and Term count values in list tables now link through to the matching posts or terms
* Enhancement - Added post-save actions to post type and taxonomies to create another
* Enhancement - Selecting existing taxonomies when registering a Custom Post Type is now available in the Basic settings section rather than Advanced
* Fix - `Exclude From Search` setting for custom post types now behaves correctly
* Fix - Duplicating fields with sub fields no longer results in JS errors
* Fix - Select2 field settings now render correctly when duplicating fields
* Fix - Checkbox fields allowing custom values which use integer keys can now be updated over the REST API
* Fix - Using the `No Permalink` rewrite setting for post type will no longer generate PHP warnings
* Fix - The `minimum rows not met` validation message for the Repeater field type now correctly states the minimum rows are not met
* Fix - The Range field type no longer cuts off three digit values
* Fix - `Created on` and `Delete Field Group` now correctly only appear on the Group Settings tab of a field group
* Fix - Padding for field settings tabs is now correct
* i18n - Updated all PRO translation files, removing legacy free strings which are now handled through [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/advanced-custom-fields/stable/)
* i18n - Updated PRO translations with the latest contributions from [GitHub](https://github.com/AdvancedCustomFields/acf/tree/master/lang/pro) (Thanks to @MilenDalchev, @Xilonz and @wiliamjk)

= 6.1.4 =
*Release Date 12th April 2023*

* Fix - ACF now detects when another plugin or theme is loading an incompatible version of Select2, and will fallback to a basic select field for the field type selection
* Fix - Post Object, Page Link and Relationship field types now use a default `post_status` filter of `any`, matching the behavior prior to ACF 6.1. This can be edited per field in the field group admin or via the `acf/fields/$field_type/query` filters
* Fix - Post Type and Taxonomy key generation now uses dashes rather than underscores
* Fix - The "add first" text no longer appears when no search results are found for ACF field groups, post types or taxonomies

= 6.1.3 =
*Release Date 5th April 2023*

* Fix - 'Create Terms' button for taxonomy fields now displays correctly
* Fix - ACF JSON field group files which have unsupported keys (not beginning with `group_`) will now load as field groups
* Fix - Renaming capabilities for post types will now set `map_meta_cap` to `true` solving an issue with assigning permissions to roles for that post type

= 6.1.2 =
*Release Date 4th April 2023*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-6-1-0-released/)
* Fix - Calls to `acf_add_options_page` after `acf_add_local_field_group` before `acf/init` will now behave correctly
* i18n - All new ACF 6.1 strings are now correctly English (United States) by default

= 6.1.1 =
*Release Date 3rd April 2023*

* Fix - Calls to `acf_add_local_field_group` before `acf/init` now behave correctly

= 6.1.0 =
*Release Date 3rd April 2023*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-6-1-0-released/)
* New - Register Custom Post Types and Taxonomies using ACF. View the [release post](https://www.advancedcustomfields.com/blog/acf-6-1-0-released/#cpts-taxonomies) for full information
* New - A new field type selection browser providing details on each type of field. View the [release post](https://www.advancedcustomfields.com/blog/acf-6-1-0-released/#field-type-modal) for full information
* New - PHP 8.1 and 8.2 support
* Security Fix - ACF's data serialization system will now prevent classes being unserialized. This prevents a potential security issue should an attacker know a vulnerable class is available, and can write malicious data to the database.
* Enhancement - Post Object, Page Link and Relationship fields now support filtering by post status
* Enhancement - Checkbox fields which allow custom entries can now be filtered to set custom text for the “Add New Choice” button using the `custom_choice_button_text` property
* Fix - ACF Block field edit view buttons now work correctly inside reusable blocks
* Fix - An empty callback function in now passed to scripts to prevent JS warnings when using Google Map fields
* Fix - Checkbox field values now support keys indexed as 0
* Fix - Automatic deactivation of the free or PRO plugin when activating the other now displays the correct message in the admin notice
* Fix - Empty Flexible Content fields will no longer cause an error when used in the block editor on save
* Fix - Admin notices now behave correctly and are closable in RTL languages

= 6.0.7 =
*Release Date 18th January 2023*

* Improvement - Removed unnecessary “Layout” prefix for Flexible Content field layouts
* Fix - Dragging and dropping fields containing settings rendered as radio button groups no longer removes the selected value
* Fix - Using the WordPress `default_page_template_title` filter with two parameters no longer causes a fatal error
* Fix - Select2 inputs in the content editor are no longer receiving styles from the ACF 6 admin UI
* Fix - `acf_add_local_field_group()` now works with field group titles containing non-ASCII characters
* Fix - Flexible Content field no longer has a missing icon for the “Duplicate” button
* Fix - Clicking the “Add Field” button in a Flexible Content layout no longer adds an invalid field if there are other Flexible Content fields in the layout
* Fix - Edit buttons for ACF blocks now behave correctly inside reusable blocks 
* Fix - Field settings rendered as a select2 field now correctly reinitialize when changing between field types

= 6.0.6 =
*Release Date 13th December 2022*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-6-0-6-release-flexible-content-field-layout-improvement/)
* New - Flexible Content field now has a new admin user experience when editing layouts
* New - Tabs for field settings in the field group editor can now be disabled via a new “Field Settings Tabs” screen option or with the new [`acf/field_group/disable_field_settings_tabs`](https://www.advancedcustomfields.com/resources/acf-field_group-disable_field_settings_tabs) filter
* Improvement - General field settings tab now selected by default when a field is opened
* Fix - Sub fields are no longer initialized by their parent, resolving performance issues when field groups contain many nested sub fields
* Fix - Frontend forms now disable the submit button after click to prevent multiple submissions
* Fix - Unknown field types no longer display broken HTML in the field group editor
* Fix - Returning an empty string via the `acf/blocks/no_fields_assigned_message` filter will no longer result in blocks without fields assigned having an extra wrapping div
* Fix - Sites with WPML enabled no longer experience failed ACF updates due to license errors
* Fix - Buttons featuring icons no longer have display issues when using RTL languages

= 6.0.5 =
*Release Date 18th November 2022*

* Fix - Uploading multiple files nested in a subfield no longer causes a fatal error when using basic uploader (props @JoKolov)

= 6.0.4 =
*Release Date 8th November 2022*

* Improvement - JavaScript initialization performance while editing large field groups has been improved, especially in Safari
* Improvement - Tooltips for field settings are now shown as inline instructions
* Improvement - Saving a field group is now disabled until a field group title has been entered
* Improvement - Additional sanitization across various internal parts of the plugin
* Fix - Dragging and dropping a field in no longer opens the field settings in Firefox
* Fix - Copying the field name or key to the clipboard now works as expected for new or reordered fields, and subfields
* Fix - Saving a field group will now temporarily disable the "Save Changes" button while saving
* Fix - Block templates that include html comments as the first DOM element no longer crash the block editor on edit
* Fix - Block templates that include InnerBlocks on the DOM's first level no longer trigger JS warnings
* Fix - Block templates that render other blocks now correctly render their InnerBlocks
* Fix - Legacy block attribute values are no longer overwritten by blank defaults of new versions
* Fix - Paginated Repeater fields now work with non-paginated Repeaters as subfields
* Fix - Repeater pagination is now properly disabled while inside blocks
* Fix - REST API no longer causes a PHP warning if `$_SERVER['REQUEST_METHOD']` is not defined
* Fix - REST API now supports integer keys for the Select field
* Fix - REST API now supports passing `null` to Image and File fields
* Fix - Invalid ACF meta keys no longer cause a fatal error when retrieved with `get_fields()`
* a11y - The Relationship field is now fully accessible for keyboard navigation
* i18n - Select dropdown arrow is now aligned correctly in RTL languages
* i18n - Radio buttons are now aligned correctly in RTL languages

= 6.0.3 =
*Release Date 18th October 2022*

* Security Fix - ACF shortcode security fixes detailed [here](https://www.advancedcustomfields.com/blog/acf-6-0-3-release-security-changes-to-the-acf-shortcode-and-ui-improvements/#acf-shortcode)
* Improvement - Field names and keys now copy to clipboard on click, and do not open a field
* Fix - The field type input now has default focus when adding a new field
* Fix - ACF no longer publishes `h1`, `h2` or `h3` CSS classes outside of the ACF admin screens
* Fix - Conditional field settings now work correctly across different tabs
* Fix - The field list for sub fields are now full width
* Fix - ACF admin notices now display with correct margin
* Fix - Admin CSS improvements when using ACF in an RTL language
* Fix - Clone fields now have the presentation tab for setting wrapper properties when in group display mode
* Fix - Appended labels on field settings will now be displayed in the correct place
* Accessibility - The move field modal is now keyboard and screen reader accessible

= 6.0.2 =
*Release Date 29th September 2022*

* Improvement - Field group and field rows no longer animate on hover to reveal the action links
* Fix - Field order is now saved correctly when fields are reordered
* Fix - WordPress notice styles outside of ACF's admin screens are no longer affected by the plugin's CSS

= 6.0.1 =
*Release Date 28th September 2022*

* Improvement - ACF's header bar inside our admin pages is no longer sticky
* Improvement - ACF's admin pages no longer use a custom font
* Fix - Duplicating flexible content layouts now works correctly
* Fix - ACF CSS classes no longer target translated class names for sub pages, resolving issues when using ACF in a language other than English
* Fix - ACF no longer reactivates when using WPML with different domains per language
* Fix - i18n - Labels for some field settings no longer break onto multiple lines in languages other than English
* Fix - Radio field types no longer generate a warning in logs due to invalid parameter counts
* Fix - True/False field focus states no longer apply outside ACF admin screens
* Fix - Focus states for many field types no longer show when interacting with a mouse input
* Fix - ACF 6's new Tab background colors no longer apply outside ACF admin screens, increasing readability
* Fix - User fields named “name” no longer have a different label presentation view
* Fix - Changing field types with subfields no longer removes those fields when switching field type and back
* Fix - Resolved a potential fatal error if a third party plugin sets the global `$post_type` something other than a string
* Fix - Tooltip alignment is no longer incorrect inside subfields
* Fix - Resolved a potential JS error when third party plugins modify the metabox sort order

= 6.0.0 =
*Release Date 21st September 2022*

* New - ACF now has a new refreshed UI with improved UX for editing field groups, including a new tabbed settings layout for fields. Third party ACF extension plugin developers can read more about the optional changes they can make to support the new tabs in [our release announcement post](https://www.advancedcustomfields.com/blog/acf-6-0-released/#new-ui)
* New - Repeaters now have an optional "Pagination" setting which can be used to control the number of rows displayed at once. More details can be found on our [Repeater field documentation](https://www.advancedcustomfields.com/resources/repeater/#pagination)
* New - ACF Blocks now have a versioning system allowing developers to opt in to new features
* New - ACF Blocks now support version 2, enabling block.json support, reduced wrapper markup and significant other new features. Full details and examples of this can be found in [What's new with ACF Blocks in ACF 6](https://www.advancedcustomfields.com/resources/whats-new-with-acf-blocks-in-acf-6/)
* New - ACF Blocks no longer use Block IDs saved in the block comment. See [What's new with ACF Blocks in ACF 6](https://www.advancedcustomfields.com/resources/whats-new-with-acf-blocks-in-acf-6/#block-id) for more information.
* Enhancement - Bulk actions for field groups now include "Activate" and "Deactivate" options
* Fix - ACF will no longer perform a multisite database upgrade check on every admin load once each upgrade has been performed
* Fix - ACF Blocks preloading now works for blocks saved in edit mode
* Fix - ACF Blocks edit forms now behave correctly if they are not visible when loaded 
* Fix - ACF Blocks now always fire `render_block_preview` events when a block preview is displayed or redisplayed
* Fix - ACF Blocks with no fields now display advisory text and are selectable in the block editor. This message is filterable with the acf/blocks/no_fields_assigned_message filter, providing both the message to be displayed and the block name it's being displayed against
* Fix - Accordions inside ACF Blocks now match the current native block styling
* Fix - ACF Blocks which contain no fields now preload correctly
* Fix - Changes to an ACF Block's context now trigger a re-render
* Fix - A rare warning inside `wp_post_revision_field` will no longer be possible
* Fix - The field “move” option now no longer displays for fields when only one field group exists
* Fix - Language for field group activation state now standardized to "active" and "inactive"
* Fix - SVGs containing `foreignObject` tags now correctly render in JSX rendered ACF Blocks
* Fix - Server errors during ACF updates or version checks are now cached for 5 minutes rather than 24 hours
* Accessibility - The new ACF UI has significantly improved accessibility for screen readers and alternative input options
* i18n - All strings inside ACF are now translatable
* i18n - Accented term names in taxonomy fields are no longer corrupted at output
* i18n - ACF translations are now synced with contributions from translation.wordpress.org at each release, increasing ACF's supported languages and updating many other translations. PRO strings should still be submitted as pull requests on GitHub (Additional thanks to maximebj, emreerkan and Timothée Moulin for their contributions which are included here)

= 5.12.6 =
*Release Date 4th May 2023*

* Security Fix - This release resolves an XSS vulnerability in ACF's admin pages (Thanks to Rafie Muhammad for the responsible disclosure)

= 5.12.5 =
*Release Date 3rd April 2023*

* Security Fix - ACF's data serialization system will now prevent classes being unserialized. This prevents a potential security issue should an attacker know a vulnerable class is available, and can write malicious data to the database. See the [6.1.0 release post](https://www.advancedcustomfields.com/blog/acf-6-1-0-released/#security) for more information

= 5.12.4 =
*Release Date 18th October 2022*

* Security Fix - ACF shortcode security fixes from the ACF 6.0.3 release. See the [6.0.3 release post](https://www.advancedcustomfields.com/blog/acf-6-0-3-release-security-changes-to-the-acf-shortcode-and-ui-improvements/#acf-shortcode) for more information

= 5.12.3 =
*Release Date 14th July 2022*

* Security Fix - Inputs for basic file uploads are now nonced to prevent an issue which could allow arbitrary file uploads to forms with ACF fields (Thanks to James Golovich from Pritect, Inc.)

= 5.12.2 =
*Release Date 6th April 2022*

* Fix - Cloned fields in custom named options pages now behave correctly
* Fix - Default values and the `acf/load_value` filter are now applied if a field value load [fails security validation](https://www.advancedcustomfields.com/resources/acf-field-functions/#non-acf-data)
* Fix - The ACF field is no longer present in REST responses if the ACF REST API setting is disabled
* Fix - Duplicating a flexible content layout or repeater row now also replaces the field ID in `for` attributes

= 5.12.1 =
*Release Date 23rd March 2022*

* New - REST API now supports the comment route for displaying ACF fields.
* Fix - ACF now validates access to option page field values when accessing via field keys the same way as field names. [View More](https://www.advancedcustomfields.com/resources/acf-field-functions/#non-acf-data)
* Fix - REST API now correctly validates fields for POST update requests
* Fix - Fixed an issue where invalid field types caused an error during REST API requests
* Fix - Fixed a PHP warning when duplicating an empty field group
* Fix - Fixed a bug preventing block duplication detection changing an ACF Block's ID if it was nested deeper than one level inside another block
* Fix - Fixed a bug where the `acf-block-preview` wrapper might not appear around a block if it was saved in edit mode
* i18n - Updated several translations from user contributions (Thanks to Dariusz Zielonka, Mikko Kekki and Alberto!)

= 5.12 =
*Release Date 23rd February 2022*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-5-12-released/)
* New - ACF blocks now support the new Full Site Editor included in WordPress 5.9
* New - ACF blocks now support the WordPress Query Loop block
* New - Added block caching system to reduce the number of AJAX calls in the block editor
* Enhancement - Block preloading can now be disabled by using "acf_update_setting( 'preload_blocks', false );" in the "acf/init" action hook
* Enhancement - ACF and ACF PRO will now detect if each other are active and deactivate the other plugin on plugin activation
* Fix - Fixed an issue with the media library not working correctly in ACF Blocks in WordPress 5.9.1
* Fix - Fixed an issue where anchors weren't working correctly in WordPress 5.9
* Fix - Fixed an issue where the "unfiltered_html" capability wasn't being used by ACF blocks
* Fix - Fixed an issue where it was impossible to update an ACF block inside the widget block editor
* Fix - Fixed an issue where ACF fields might not appear in REST API calls made via internal WordPress functions
* Fix - Warnings and PHP 8.1 deprecation notices in REST API
* Fix - Better support for double byte characters in "acf_get_truncated()" (props @cabradb)
* i18n - Broken link in the Croatian translation
* i18n - Automated building of acf.pot for translators in each release

= 5.11.4 =
*Release Date - 2nd December 2021*

* Fix - Fixed several Select2.js conflicts with other plugins
* Fix - Fixed an issue where block name sanitization could change valid block names containing double hyphens
* Fix - Fixed an issue where blocks with integer IDs could fail to load example field data

= 5.11.3 =
*Release Date - 24th November 2021*

* Fix - Fixed a bug when accessing field values for options pages registered with a custom post_id

= 5.11.2 =
*Release Date - 24th November 2021*

* Fix - Previously implemented data access changes for get_field() and the_field() are now limited to the ACF shortcode only. [Learn more](https://www.advancedcustomfields.com/resources/acf-field-functions/)
* Fix - get_field() and the_field() functions can once again access meta values regardless of being registered with ACF, restoring functionality that existed before 5.11
* Fix - get_field() and the_field() functions now are only able to access site options which are ACF fields
* Fix - UI issues for select boxes related to Yoast and WooCommerce's select2 versions by upgrading our select2 version, and updating our CSS to support older versions
* Fix - User fields failed to load values when using the legacy select2 v3 option
* Fix - acf_slugify() now correctly supports special characters which solves issues with block names or field group names (during imports) containing those characters
* Fix - PHP Notice generated while processing a field group's postbox classes

= 5.11.1 =
*Release Date - 18 November 2021*

* Enhancement - Added "acf/admin/license_key_constant_message" filter to allow changing of the "Your license key is defined in wp-config.php" message
* Fix - Added warning for when get_field() or similar functions are called before ACF has initialized. [Learn more](https://www.advancedcustomfields.com/resources/acf-field-functions/)
* Fix - Fixed fields not appearing on user REST API endpoints if their field group location was set to a user form other than "all"
* Fix - Fixed warning in REST API if a custom field type did not have the "show_in_rest" property
* Fix - Fixed an error that could occur if value of WYSIWYG field was not a string

= 5.11 =
*Release Date - 10 November 2021*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-5-11-release-rest-api/)
* New - Fields can now be viewed and updated with the WordPress REST API (props @mishterk)
* New - License key can now be defined in code with the "ACF_PRO_LICENSE" constant
* Enhancement - Improved error handling for expired or deactivated licenses
* Enhancement - Improved support for various block editor features, such as block styles and padding/spacing
* Enhancement - Added support for using WordPress "Screen Options" to hide field groups in Classic Editor
* Enhancement - Support filters adding custom classes on date and time field inputs
* Enhancement - Support filtering ACF shortcode attributes (with the "shortcode_atts_acf" filter)
* Fix - Removed usages of PHP "extract()" function
* Fix - Fixed a security issue with user field
* Fix - Fixed a security issue with "acf_get_value()"
* Fix - Correctly set ".acf-block-preview" wrapper when previewing a block in auto mode
* Fix - Resolved an issue with select2 rendering for nav menu fields
* Fix - Fixed an issue with file validation that occurred when removing a file that failed validation
* Fix - Fixed a notice in "acf_prepare_field()"
* Fix - Prevented an issue where setting an empty string for the return format of date and time fields would cause JS errors
* Fix - Fix issues with conditional logic for multi-select fields (props @bhujagendra-ishaya)
* Fix - Added support for Google Maps schema change which prevented Google Maps fields from correctly saving the city for some areas
* Fix - Fixed an issue where removing the collapsed property of a repeater prevents viewing previously collapsed rows
* i18n - Updated Polish Translations (props @webnatural)
* Dev - Formatted JavaScript to WordPress code standards

[View the full changelog](https://www.advancedcustomfields.com/changelog/)

== Upgrade Notice ==
= 6.2.5 =
From ACF 6.2.5, the shortcode will now escape unsafe HTML automatically. This may be a breaking change. Please view [our release blog](https://www.advancedcustomfields.com/blog/acf-6-2-5-security-release/) for more information.