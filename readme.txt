=== Advanced Custom Fields (ACF®) PRO ===
Contributors: deliciousbrains, wpengine, elliotcondon, mattshaw, lgladdy, antpb, johnstonphilip, dalewilliams, polevaultweb
Tags: acf, fields, custom fields, meta, repeater
Requires at least: 6.0
Tested up to: 6.8.1
Requires PHP: 7.4
Stable tag: 6.4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

ACF helps customize WordPress with powerful, professional and intuitive fields. Proudly powering over 2 million sites, WordPress developers love ACF.

== Description ==

Advanced Custom Fields (ACF®) turns WordPress sites into a fully-fledged content management system by giving you all the tools to do more with your data.

Use the ACF plugin to take full control of your WordPress edit screens, custom field data, and more.

https://www.youtube.com/watch?v=9C6_roqghZQ&rel=0

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

= 6.4.3 =
*Release Date 22nd July 2025*

* Security - Unsafe HTML in field group labels is now correctly escaped for conditionally loaded field groups, resolving a JS execution vulnerability in the classic editor
* Security - HTML is now escaped from field group labels when output in the ACF admin
* Security - Bidirectional and Conditional Logic Select2 elements no longer render HTML in field labels or post titles
* Security - The `acf.escHtml` function now uses the third party DOMPurify library to ensure all unsafe HTML is removed. A new `esc_html_dompurify_config` JS filter can be used to modify the default behaviour
* Security - Post titles are now correctly escaped whenever they are output by ACF code. Thanks to Shogo Kumamaru of LAC Co., Ltd. for the responsible disclosure
* Security - An admin notice is now displayed when version 3 of the Select2 library is used, as it has now been deprecated in favor of version 4

= 6.4.2 =
*Release Date 20th May 2025*

* New - In ACF PRO, fields can now be added to WooCommerce Subscriptions when using HPOS
* Security - Changing a field type no longer enables the "Allow Access to Value in Editor UI" setting
* Fix - Paginated Repeater fields no longer save duplicate values when saving to a WooCommerce Order with HPOS disabled
* Fix - Blocks registered via acf_register_block_type() with a `parent` value of `null` no longer fail to register

= 6.4.1 =
*Release Date 8th May 2025*

* New - Select fields can now be configured to allow creating new options when editing the field's value (requires the "Stylized UI" and "Multiple" field settings to be enabled)
* Enhancement - The "Escaped HTML" warning notice [introduced in ACF 6.2.5](https://www.advancedcustomfields.com/blog/acf-6-2-5-security-release/) is now disabled by default
* Enhancement - The Icon Picker field now supports supplying an array of icons to a custom tab via a new `acf/fields/icon_picker/{tab_name}/icons` filter
* Fix - ACF Blocks are now forced into preview mode when editing a synced pattern
* Fix - The free ACF plugin once again works with the Classic Widgets plugin and the legacy ACF Options Page addon
* Fix - ACF no longer causes an infinite loop in bbPress when editing replies

= 6.4.0.1 =
*Release Date 8th April 2025*

* Fix - Calling `acf_get_reference()` with an invalid field name no longer causes a fatal error

= 6.4.0 =
*Release Date 7th April 2025*

* New - In ACF PRO, fields can now be added to WooCommerce orders when using HPOS
* Enhancement - ACF now uses Composer to autoload some classes
* Fix - Repeater pagination now works when the Repeater is inside a Group field
* Fix - Various translations are no longer called before the WordPress `init` action hook
* Security - Link field no longer has a minor local XSS vulnerability
* i18n - Various British English translation strings no longer have a quoting issue breaking links
* i18n - Added Dutch (formal) translations (props @toineenzo)

= 6.3.12 =
*Release Date 21st January 2025*

* Enhancement - Error messages that occur when field validation fails due an insufficient security nonce now have additional context
* Fix - Duplicated ACF blocks no longer lose their field values after the initial save when block preloading is enabled
* Fix - ACF Blocks containing complex field types now behave correctly when React StrictMode is enabled

= 6.3.11 =
*Release Date 12th November 2024*

* Enhancement - Field Group keys are now copyable on click
* Fix - Repeater tables with fields hidden by conditional logic now render correctly
* Fix - ACF Blocks now behave correctly in React StrictMode
* Fix - Edit mode is no longer available to ACF Blocks with an WordPress Block API version of 3 as field editing is not supported in the iframe

= 6.3.10.2 =
*Release Date 29th October 2024*
*Free Only Release*

* Fix - ACF Free no longer causes a fatal error when any unsupported legacy ACF addons are active

= 6.3.10.1 =
*Release Date 29th October 2024*
*Free Only Release*

* Fix - ACF Free no longer causes a fatal error when WPML is active

= 6.3.10 =
*Release Date 29th October 2024*

* Security - Setting a metabox callback for custom post types and taxonomies now requires being an admin, or super admin for multisite installs
* Security - Field specific ACF nonces are now prefixed, resolving an issue where third party nonces could be treated as valid for AJAX calls
* Enhancement - A new “Close and Add Field” option is now available when editing a field group, inserting a new field inline after the field being edited
* Enhancement - ACF and ACF PRO now share the same plugin updater for improved reliability and performance
* Fix - Exporting post types and taxonomies containing metabox callbacks now correctly exports the user defined callback

= 6.3.9 =
*Release Date 15th October 2024*

* Security - Editing an ACF Field in the Field Group editor can no longer execute a stored XSS vulnerability. Thanks to Duc Luong Tran (janlele91) from Viettel Cyber Security for the responsible disclosure
* Security - Post Type and Taxonomy metabox callbacks no longer have access to any superglobal values, hardening the original fix from 6.3.8 further
* Fix - ACF fields now correctly validate when used in the block editor and attached to the sidebar

= 6.3.8 =
*Release Date 7th October 2024*

* Security - ACF defined Post Type and Taxonomy metabox callbacks no longer have access to $_POST data. (Thanks to the Automattic Security Team for the disclosure)

= 6.3.7 =
*Release Date 2nd October 2024*

* Security - ACF Free now uses its own update mechanism from WP Engine servers

= 6.3.6 =
*Release Date 28th August 2024*

* Security - Newly added fields now have to be explicitly set to allow access in the content editor (when using the ACF shortcode or Block Bindings) to increase the security around field permissions. [See the release notes for more details](https://www.advancedcustomfields.com/blog/acf-6-3-6/#field-value-access-editor)
* Security Fix - Field labels are now correctly escaped when rendered in the Field Group editor, to prevent a potential XSS issue. Thanks to Ryo Sotoyama of Mitsui Bussan Secure Directions, Inc. for the responsible disclosure
* Fix - Validation and Block AJAX requests nonces will no longer be overridden by third party plugins
* Fix - Detection of third party select2 libraries will now default to v4 rather than v3
* Fix - Block previews will now display an error if the render template PHP file is not found

= 6.3.5 =
*Release Date 1st August 2024*

* Fix - The ACF Shortcode now correctly outputs a comma separated list of values for arrays
* Fix - ACF Blocks rendered in auto mode now correctly re-render their previews after editing fields
* Fix - ACF Block validation no longer raises required validation messages if HTML will automatically select the first value when rendered
* Fix - ACF Block validation no longer raises required validation messages if a default value will be rendered as the field value
* Fix - ACF Block validation no longer raises required validation messages for fields hidden by conditional logic when adding a new block

= 6.3.4 =
*Release Date 18th July 2024*

* Security Fix - The ACF shortcode now prevents access to fields from different private posts by default. View the [release notes](https://www.advancedcustomfields.com/blog/acf-6-3-4) for more information
* Fix - Users without the `edit_posts` capability but with custom capabilities for a editing a custom post type, can now correctly load field groups loaded via conditional location rules
* Fix - Block validation no longer validates a field’s sub fields on page load, only on edit. This resolves inconsistent validation errors on page load or when first adding a block
* Fix - Deactivating an ACF PRO license will now remove the license key even if the server call fails
* Fix - Field types returning objects no longer cause PHP warnings and errors when output via `the_field`, `the_sub_field` or the ACF shortcode, or when retrieved by a `get_` function with the escape html parameter set
* Fix - Server side errors during block rendering now gracefully displays an error to the editor

= 6.3.3 =
*Release Date 27th June 2024*

* Enhancement - All dashicons are now available to the icon picker field type
* Fix - The True/False field now correctly shows it’s description message beside the switch when using the Stylized UI setting
* Fix - Conditional logic values now correctly load options when loaded over AJAX
* Fix - ACF PRO will no longer trigger license validation calls when loading a front-end page
* i18n - Fixed an untranslatable string on Option Page previews

= 6.3.2.1 =
*Release Date 24th June 2024*
*PRO Only Release*

* Fix - ACF Blocks no longer trigger a JavaScript error when fetched via AJAX

= 6.3.2 =
*Release Date 24th June 2024*

* Security Fix - ACF now generates different nonces for each AJAX-enabled field, preventing subscribers or front-end form users from querying other field results
* Security Fix - ACF now correctly verifies permissions for certain editor only actions, preventing subscribers performing those actions
* Security Fix - Deprecated a legacy private internal field type (output) to prevent it being able to output unsafe HTML
* Security Fix - Improved handling of some SQL filters and other internal functions to ensure output is always correctly escaped
* Security Fix - ACF now includes blank index.php files in all folders to prevent directory listing of ACF plugin folders for incorrectly configured web servers

= 6.3.1.2 =
*Release Date 6th June 2024*
*PRO Only Release*

* Fix - ACF Blocks in widget areas no longer cause a fatal error when no context is available
* Fix - ACF Blocks with no fields assigned no longer show a gap in the sidebar where the form would render

= 6.3.1.1 =
*Release Date 6th June 2024*
*PRO Only Release*

* Fix - Repeater and Flexible Content fields no longer error when duplicating or removing rows containing Icon Picker subfields
* Fix - ACF Blocks containing Flexible Content fields now correctly load their edit form
* Fix - ACF Blocks no longer have a race condition where the data store is not initialized when read
* Fix - ACF Blocks no longer trigger a JS error for blocks without fields and with an empty no-fields message
* Fix - ACF Block preloading now works correctly for fields consuming custom block context
* Fix - ACF Block JavaScript debug messages now correctly appear when SCRIPT_DEBUG is true

= 6.3.1 =
*Release Date 4th June 2024*

* Enhancement - Options Pages registered in the UI can now be duplicated
* Fix - ACF Block validation now correctly validates Repeater, Group, and Flexible Content fields
* Fix - ACF Block validation now correctly validates when a field is using a non-default return type
* Fix - Fields moved between field groups now correctly updates both JSON files
* Fix - Icon Picker fields now render correctly when using left-aligned labels
* Fix - Icon Picker fields no longer renders tabs if only one tab is selected for display
* Fix - Icon Picker fields no longer crash the post editor if no icon picker tabs are selected for displayed
* Fix - True/False field now better handles longer On/Off labels
* Fix - Select2 results loaded by AJAX for multi-select Taxonomy fields no longer double encode HTML entities

= 6.3.0.1 =
*Release Date 22nd May 2024*

* Fix - A possible fatal error no longer occurs in the new site health functionality for ACF PRO users
* Fix - A possible undefined index error no longer occurs in ACF Blocks for ACF PRO users

= 6.3.0 =
*Release Date 22nd May 2024*

* New - ACF now requires WordPress version 6.0 or newer, and PHP 7.4 or newer.
* New - ACF Blocks now support validation rules for fields. View the [release notes](https://www.advancedcustomfields.com/blog/acf-6-3-0-released) for more information
* New - ACF Blocks now supports storing field data in the postmeta table rather than in the post content
* New - Conditional logic rules for fields now support selecting specific values for post objects, page links, taxonomies, relationships and users rather than having to enter the ID
* New - New Icon Picker field type for ACF and ACF PRO
* New - Icon selection for a custom post type menu icon
* New - Icon selection for an options page menu icon
* New - ACF now surfaces debug and status information in the WordPress Site Health area
* New - The escaped html notice can now be permanently dismissed
* Enhancement - Tab field now supports a `selected` attribute to specify which should be selected by default, and support class attributes
* Fix - Block Preloading now works reliably in WordPress 6.5 or newer
* Fix - Select2 results loaded by AJAX for post object fields no longer double encode HTML entities
* Fix - Custom post types registered with ACF will now have custom field support enabled by default to better support revisions
* Fix - The first preview after publishing a post in the classic editor now displays ACF fields correctly
* Fix - ACF fields and Flexible Content layouts are now correctly positioned while dragging
* Fix - Copying the title of a field inside a Flexible Content layout no longer adds whitespace to the copied value
* Fix - Flexible Content layout names are no longer converted to lowercase when edited
* Fix - ACF Blocks with attributes without a default now correctly register
* Fix - User fields no longer trigger a 404 when loading results if the nonce generated only contains numbers
* Fix - Description fields for ACF items now support being solely numeric characters
* Fix - The field group header no longer appears above the WordPress admin menu on small screens
* Fix - The `acf/json/save_file_name` filter now correctly applies when deleting JSON files
* i18n - All errors raised during ACF PRO license or update checks are now translatable
* Other - The ACF Shortcode is now disabled by default for new installations of ACF as discussed in the [ACF 6.2.7 release notes](https://www.advancedcustomfields.com/blog/acf-6-2-7-security-release/#security-and-the-acf-shortcode)

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

[View the full changelog](https://www.advancedcustomfields.com/changelog/)

== Upgrade Notice ==