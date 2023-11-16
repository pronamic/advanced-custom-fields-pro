=== Advanced Custom Fields PRO ===
Contributors: elliotcondon
Tags: acf, fields, custom fields, meta, repeater
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.0
Stable tag: 6.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Advanced Custom Fields (ACF) helps you easily customize WordPress with powerful, professional and intuitive fields. Proudly powering over 2 million websites, Advanced Custom Fields is the plugin WordPress developers love.

== Description ==

Advanced Custom Fields (ACF) turns WordPress sites into a fully-fledged content management system by giving you all the tools to do more with your data.

Use the ACF plugin to take full control of your WordPress edit screens, custom field data, and more.

**Add fields on demand.**
The ACF field builder allows you to quickly and easily add fields to WP edit screens with only the click of a few buttons! Whether it’s something simple like adding an “author” field to a book review post, or something more complex like the structured data needs of an ecommerce site or marketplace, ACF makes adding fields to your content model easy.

**Add them anywhere.**
Fields can be added all over WordPress including posts, pages, users, taxonomy terms, media, comments and even custom options pages! It couldn’t be simpler to bring structure to the WordPress content creation experience.

**Show them everywhere.**
Load and display your custom field values in any theme template file with our hassle-free, developer friendly functions! Whether you need to display a single value or generate content based on a more complex query, the out-of-the-box functions of ACF make templating a dream for developers of all levels of experience.

**Any Content, Fast.**
Turning WordPress into a true content management system is not just about custom fields. Creating new custom post types and taxonomies is an essential part of building custom WordPress sites. Registering post types and taxonomies is now possible right in the ACF UI, speeding up the content modeling workflow without the need to touch code or use another plugin.

**Simply beautiful and intentionally accessible.**
For content creators and those tasked with data entry, the field user experience is as intuitive as they could desire while fitting neatly into the native WordPress experience. Accessibility standards are regularly reviewed and applied, ensuring ACF is able to empower as close to anyone as possible.

**Documentation and developer guides.**
Over 10 plus years of vibrant community contribution alongside an ongoing commitment to clear documentation means that you’ll be able to find the guidance you need to build what you want.

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
* Enhancement - ACF PRO will now warn when it can’t update due to PHP version incompatibilities
* Enhancement - ACF PRO will now work correctly with WordPress automatic updates
* Enhancement - The internal ACF Blocks template attribute parser function `parseNodeAttr` can now be shortcut with the new `acf_blocks_parse_node_attr` filter.
* Enhancement - Removed legacy code for supporting WordPress versions under 5.8
* Fix - The "Menu Position" setting is no longer hidden for child options pages
* Fix - The tabs for the "Advanced" settings in Post Types and Taxonomies are now rendered inside a wrapper div
* Fix - Options pages will no longer display as a child page in the list view when set to a top level page after previously being a child
* Fix - Conflict with Elementor CSS breaking the ACF PRO banner
* Fix - Errors generated during the block editor’s `savePost` function will no longer be caught and ignored by ACF

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
* Fix - Block templates that include InnerBlocks on the DOM’s first level no longer trigger JS warnings
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
* Fix - ACF 6’s new Tab background colors no longer apply outside ACF admin screens, increasing readability
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

* Security Fix - This release resolves an XSS vulnerability in ACF’s admin pages (Thanks to Rafie Muhammad for the responsible disclosure)

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
* Fix - Fixed a bug preventing block duplication detection changing an ACF Block’s ID if it was nested deeper than one level inside another block
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
* Fix - UI issues for select boxes related to Yoast and WooCommerce’s select2 versions by upgrading our select2 version, and updating our CSS to support older versions
* Fix - User fields failed to load values when using the legacy select2 v3 option
* Fix - acf_slugify() now correctly supports special characters which solves issues with block names or field group names (during imports) containing those characters
* Fix - PHP Notice generated while processing a field group’s postbox classes

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

= 5.10.2 =
*Release Date - 31 August 2021*

* Fix - Fixed block duplication issues which created blocks with duplicate block IDs
* Fix - Fixed an issue with ACF errors displaying in the media library outside of ACF fields
* Fix - Changed label of "Enable Opacity?" to "Allow transparency" in the colour picker
* Fix - Revert "style" attributes of ACF Blocks to 5.9.x behaviour for template compatibility
* Fix - Allow safe HTML inside select2 field labels
* Fix - Don't render the "acf-block-preview" div when preloading blocks in edit mode

= 5.10.1 =
*Release Date - 26 August 2021*

* Fix - Fixed conflict with WooCommerce loading SelectWoo which is not directly compatible with Select2.

= 5.10 =
*Release Date - 25 August 2021*

* [View Release Post](https://www.advancedcustomfields.com/blog/acf-5-10-release-html-escaping-blocks-api-v2-block-preloading-and-more/)
* Enhancement - Improved security by running all user-generated content through `wp_kses()` by default
* Enhancement - New ACF Blocks features
    * Switched to v2 of the Blocks API for WordPress 5.6+
    * Block preloading now enabled by default
    * Block preloading now supports blocks set to "Edit" mode
    * Add support for full height alignment setting
* Enhancement - Added setting to color picker field to enable an opacity slider
* Enhancement - Allow deletion of first field group location rule if multiple rules have been added thanks to Arthur Shlain
* Fix - Fixed vulnerability with `acf_shortcode()` where users with subscriber role could view arbitrary ACF data, thanks to Keitaro Yamazaki
* Fix - Fixed vulnerability where users with subscriber role could move fields and view field groups, thanks to Keitaro Yamazaki
* Fix - Fixed issue where fields in legacy widgets weren't saving in new widget block editor
* Fix - Fixed issue with custom field validation in scheduled posts
* Fix - Fixed warnings thrown by clone field if the cloned field group is empty
* Fix - Fixed issue where Select2 search input wouldn't have focus in WordPress 5.8+
* Fix - Fixed issue with Select2 value sorting when Yoast SEO is installed
* Fix - Fixed deprecation warnings in block editor in WordPress 5.6+
* i18n - Updated Swedish translation thanks to Erik Betshammar

= 5.9.9 =
*Release Date - 20 July 2021*

* Fix - Fixed warning when deleting fields which don't exist
* Fix - Fixed issues with older browsers and the blocks JavaScript
* Fix - Fixed file size & file type validation for front end forms using the basic uploader

= 5.9.8 =
*Release Date - 08 July 2021*

* Fix - Fixed bug causing multiple image fields to not validate files properly
* Fix - Fixed bug preventing case-sensitive HTML tags from working in blocks
* Fix - Fixed bug causing JSX-enabled blocks to improperly remove whitespace in preview
* Fix - Fixed bug causing text fields to remove HTML entities when editing saved fields
* Fix - Fixed deprecated jQuery notices on "Add Field Group" page

= 5.9.7 =
*Release Date - 22 June 2021*

* Fix - Fixed PHP warnings logged due to incorrect parameter type for `add_menu_page()`/`add_submenu_page()`
* Fix - Fixed bug causing WYSIWYG field to not keep line breaks
* Fix - Fixed bug causing Email field to incorrectly invalidate emails with unicode characters
* Fix - Fixed bug causing file type validation to fail in some cases
* Fix - Fixed bug where newly uploaded or selected images do not contain custom preview size data

= 5.9.6 =
*Release Date - 20 May 2021*

* Enhancement - Added 'position' setting compatibility for Options Page submenus.
* Enhancement - Visually highlight "High" metabox area when dragging metaboxes.
* Fix - Fixed compatibility issue between Block matrix alignment setting and the latest version of Gutenberg (10.6).
* Fix - Fixed bug breaking WYSIWYG field after reordering a child block via the block's toolbar up/down buttons.
* Fix - Added missing "readonly" and "disabled" attributes to DateTime and Time picker fields.
* Fix - Fixed bug incorrectly validating Email field values containing special characters.
* Fix - Fixed missing "dashicons" asset dependency from front-end forms.
* Fix - Fixed bug causing Review JSON diff modal to appear with narrow column since WP 5.7.
* Dev - Added label elements to Repeater, Flexible Content and Clone field's table header titles.
* Dev - Added new `ACF_EXPERIMENTAL_ESC_HTML` constant. [Read more](https://github.com/AdvancedCustomFields/acf/issues/500)

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
