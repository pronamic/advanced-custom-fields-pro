<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Admin_Tool_Export' ) ) :

	class ACF_Admin_Tool_Export extends ACF_Admin_Tool {

		/** @var string View context */
		var $view = '';


		/** @var array Export data */
		var $json = '';


		/**
		 *  initialize
		 *
		 *  This function will initialize the admin tool
		 *
		 *  @date    10/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function initialize() {

			// vars
			$this->name  = 'export';
			$this->title = __( 'Export Field Groups', 'acf' );

			// active
			if ( $this->is_active() ) {
				$this->title .= ' - ' . __( 'Generate PHP', 'acf' );
			}

		}


		/**
		 *  submit
		 *
		 *  This function will run when the tool's form has been submit
		 *
		 *  @date    10/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function submit() {

			// vars
			$action = acf_maybe_get_POST( 'action' );

			// download
			if ( $action === 'download' ) {

				$this->submit_download();

				// generate
			} elseif ( $action === 'generate' ) {

				$this->submit_generate();

			}

		}


		/**
		 *  submit_download
		 *
		 *  description
		 *
		 *  @date    17/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function submit_download() {

			// vars
			$json = $this->get_selected();

			// validate
			if ( $json === false ) {
				return acf_add_admin_notice( __( 'No field groups selected', 'acf' ), 'warning' );
			}

			// headers
			$file_name = 'acf-export-' . date( 'Y-m-d' ) . '.json';
			header( 'Content-Description: File Transfer' );
			header( "Content-Disposition: attachment; filename={$file_name}" );
			header( 'Content-Type: application/json; charset=utf-8' );

			// return
			echo acf_json_encode( $json ) . "\r\n";
			die;

		}


		/**
		 *  submit_generate
		 *
		 *  description
		 *
		 *  @date    17/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function submit_generate() {

			// vars
			$keys = $this->get_selected_keys();

			// validate
			if ( ! $keys ) {
				return acf_add_admin_notice( __( 'No field groups selected', 'acf' ), 'warning' );
			}

			// url
			$url = add_query_arg( 'keys', implode( '+', $keys ), $this->get_url() );

			// redirect
			wp_redirect( $url );
			exit;

		}


		/**
		 *  load
		 *
		 *  description
		 *
		 *  @date    21/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function load() {

			// active
			if ( $this->is_active() ) {

				// get selected keys
				$selected = $this->get_selected_keys();

				// add notice
				if ( $selected ) {
					$count = count( $selected );
					$text  = sprintf( _n( 'Exported 1 item.', 'Exported %s items.', $count, 'acf' ), $count );
					acf_add_admin_notice( $text, 'success' );
				}
			}

		}


		/**
		 *  html
		 *
		 *  This function will output the metabox HTML
		 *
		 *  @date    10/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function html() {

			// single (generate PHP)
			if ( $this->is_active() ) {

				$this->html_single();

				// archive
			} else {

				$this->html_archive();

			}

		}


		/**
		 * Renders the checkboxes to select items to export.
		 *
		 * @date 24/10/17
		 * @since 5.6.3
		 *
		 * @return void
		 */
		public function html_field_selection() {
			// Ensure `l10n_var_export` is always false at the point we're outputting the options.
			acf_update_setting( 'l10n_var_export', false );
			// Reset the field-groups store which may have been corrupted by export.
			$store = acf_get_store( 'field-groups' );
			if ( $store ) {
				$store->reset();
			}

			$choices      = array();
			$selected     = $this->get_selected_keys();
			$field_groups = acf_get_internal_post_type_posts( 'acf-field-group' );

			if ( $field_groups ) {
				foreach ( $field_groups as $field_group ) {
					$choices[ $field_group['key'] ] = esc_html( $field_group['title'] );
				}
			}

			acf_render_field_wrap(
				array(
					'label'   => __( 'Select Field Groups', 'acf' ),
					'type'    => 'checkbox',
					'name'    => 'keys',
					'prefix'  => false,
					'value'   => $selected,
					'toggle'  => true,
					'choices' => $choices,
				)
			);

			$choices    = array();
			$selected   = $this->get_selected_keys();
			$post_types = acf_get_internal_post_type_posts( 'acf-post-type' );

			if ( $post_types ) {
				foreach ( $post_types as $post_type ) {
					$choices[ $post_type['key'] ] = esc_html( $post_type['title'] );
				}

				acf_render_field_wrap(
					array(
						'label'   => __( 'Select Post Types', 'acf' ),
						'type'    => 'checkbox',
						'name'    => 'post_type_keys',
						'prefix'  => false,
						'value'   => $selected,
						'toggle'  => true,
						'choices' => $choices,
					)
				);
			}

			$choices    = array();
			$selected   = $this->get_selected_keys();
			$taxonomies = acf_get_internal_post_type_posts( 'acf-taxonomy' );

			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {
					$choices[ $taxonomy['key'] ] = esc_html( $taxonomy['title'] );
				}

				acf_render_field_wrap(
					array(
						'label'   => __( 'Select Taxonomies', 'acf' ),
						'type'    => 'checkbox',
						'name'    => 'taxonomy_keys',
						'prefix'  => false,
						'value'   => $selected,
						'toggle'  => true,
						'choices' => $choices,
					)
				);
			}
		}

		/**
		 * Renders the side panel for selecting ACF items to export via PHP.
		 *
		 * @date 21/10/17
		 * @since 5.6.3
		 *
		 * @return void
		 */
		public function html_panel_selection() {
			?>
			<div class="acf-panel acf-panel-selection">
				<?php $this->html_field_selection(); ?>
			</div>
			<?php
		}

		/**
		 *  html_panel_settings
		 *
		 *  description
		 *
		 *  @date    21/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function html_panel_settings() {

			?>
		<div class="acf-panel acf-panel-settings">
			<h3 class="acf-panel-title"><?php _e( 'Settings', 'acf' ); ?> <i class="dashicons dashicons-arrow-right"></i></h3>
			<div class="acf-panel-inside">
				<?php

				/*
				acf_render_field_wrap(array(
					'label'     => __('Empty settings', 'acf'),
					'type'      => 'select',
					'name'      => 'minimal',
					'prefix'    => false,
					'value'     => '',
					'choices'   => array(
						'all'       => __('Include all settings', 'acf'),
						'minimal'   => __('Ignore empty settings', 'acf'),
					)
				));
				*/

				?>
			</div>
		</div>
			<?php

		}


		/**
		 *  html_archive
		 *
		 *  description
		 *
		 *  @date    20/10/17
		 *  @since   5.6.3
		 *
		 *  @param   n/a
		 *  @return  n/a
		 */

		function html_archive() {

			?>
		<div class="acf-postbox-header">
			<h2 class="acf-postbox-title"><?php esc_html_e( 'Export', 'acf' ); ?></h2>
			<div class="acf-tip"><i tabindex="0" class="acf-icon acf-icon-help acf-js-tooltip" title="<?php esc_attr_e( 'Select the items you would like to export and then select your export method. Export As JSON to export to a .json file which you can then import to another ACF installation. Generate PHP to export to PHP code which you can place in your theme.', 'acf' ); ?>">?</i></div>
		</div>
		<div class="acf-postbox-inner">
			<div class="acf-fields">
				<?php $this->html_field_selection(); ?>
			</div>
			<p class="acf-submit acf-actions-strip">
				<button type="submit" name="action" class="acf-btn acf-button-primary" value="download"><?php _e( 'Export As JSON', 'acf' ); ?></button>
				<button type="submit" name="action" class="acf-btn acf-btn-secondary" value="generate"><?php _e( 'Generate PHP', 'acf' ); ?></button>
			</p>
		</div>
			<?php

		}

		/**
		 * Renders the PHP export screen.
		 *
		 * @date 20/10/17
		 * @since 5.6.3
		 *
		 * @return void
		 */
		public function html_single() {
			?>
			<div class="acf-postbox-header">
				<h2 class="acf-postbox-title"><?php esc_html_e( 'Export - Generate PHP', 'acf' ); ?></h2>
				<i tabindex="0" class="acf-icon acf-icon-help acf-js-tooltip" title="<?php esc_attr_e( "The following code can be used to register a local version of the selected items. Storing field groups, post types, or taxonomies locally can provide many benefits such as faster load times, version control & dynamic fields/settings. Simply copy and paste the following code to your theme's functions.php file or include it within an external file, then deactivate or delete the items from the ACF admin.", 'acf' ); ?>">?</i>
			</div>
			<div class="acf-postbox-columns">
				<div class="acf-postbox-main">
					<?php $this->html_generate(); ?>
				</div>
				<div class="acf-postbox-side">
					<?php $this->html_panel_selection(); ?>
					<p class="acf-submit">
						<button type="submit" name="action" class="acf-btn" value="generate"><?php esc_html_e( 'Generate PHP', 'acf' ); ?></button>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * Generates the HTML for the PHP export functionality.
		 *
		 * @date    17/10/17
		 * @since   5.6.3
		 *
		 * @return void
		 */
		public function html_generate() {
			// Prevent default translation and fake __() within string.
			acf_update_setting( 'l10n_var_export', true );

			$json      = $this->get_selected();
			$to_export = array();

			// Sort by ACF post type first so we can wrap them in related functions.
			foreach ( $json as $post ) {
				$post_type = acf_determine_internal_post_type( $post['key'] );

				if ( $post_type ) {
					$to_export[ $post_type ][] = $post;
				}
			}

			echo '<textarea id="acf-export-textarea" readonly="readonly">';

			foreach ( $to_export as $post_type => $posts ) {
				if ( 'acf-field-group' === $post_type ) {
					echo "add_action( 'acf/include_fields', function() {\r\n";
					echo "\tif ( ! function_exists( 'acf_add_local_field_group' ) ) {\r\n\t\treturn;\r\n\t}\r\n\r\n";
				} elseif ( 'acf-post-type' === $post_type || 'acf-taxonomy' === $post_type ) {
					echo "add_action( 'init', function() {\r\n";
				}

				$count = 0;
				foreach ( $posts as $post ) {
					if ( $count !== 0 ) {
						echo "\r\n";
					}

					echo "\t" . acf_export_internal_post_type_as_php( $post, $post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- esc_textarea() used earlier.
					$count++;
				}

				if ( in_array( $post_type, array( 'acf-post-type', 'acf-taxonomy', 'acf-field-group' ), true ) ) {
					echo "} );\r\n\r\n";
				}
			}

			echo '</textarea>';
			?>
			<p class="acf-submit">
				<a class="button" id="acf-export-copy"><?php _e( 'Copy to clipboard', 'acf' ); ?></a>
			</p>
			<script type="text/javascript">
			(function($){
				const $a = $('#acf-export-copy');
				const $textarea = $('#acf-export-textarea');

				// Remove $a if 'copy' is not supported.
				if( !document.queryCommandSupported('copy') ) {
					return $a.remove();
				}

				$a.on('click', function( e ){
					e.preventDefault();

					$textarea.get(0).select();

					try {
						var copy = document.execCommand('copy');
						if ( ! copy ) {
							return;
						}

						acf.newTooltip({
							text: 		"<?php esc_html_e( 'Copied', 'acf' ); ?>",
							timeout:	250,
							target: 	$(this),
						});
					} catch (err) {
						// Do nothing.
					}
				});
			})(jQuery);
			</script>
			<?php
		}

		/**
		 * Return an array of keys that have been selected in the export tool.
		 *
		 * @date 20/10/17
		 * @since 5.6.3
		 *
		 * @return array|bool
		 */
		public function get_selected_keys() {
			$key_names = array( 'keys', 'post_type_keys', 'taxonomy_keys' );
			$all_keys  = array();

			foreach ( $key_names as $key_name ) {
				if ( $keys = acf_maybe_get_POST( $key_name ) ) {
					$all_keys = array_merge( $all_keys, (array) $keys );
				} elseif ( $keys = acf_maybe_get_GET( $key_name ) ) {
					$keys     = str_replace( ' ', '+', $keys );
					$keys     = explode( '+', $keys );
					$all_keys = array_merge( $all_keys, (array) $keys );
				}
			}

			if ( ! empty( $all_keys ) ) {
				return $all_keys;
			}

			return false;
		}

		/**
		 * Returns the JSON data for given $_POST args.
		 *
		 * @date  17/10/17
		 * @since 5.6.3
		 *
		 * @return array|bool
		 */
		public function get_selected() {
			$selected = $this->get_selected_keys();
			$json     = array();

			if ( ! $selected ) {
				return false;
			}

			foreach ( $selected as $key ) {
				$post_type = acf_determine_internal_post_type( $key );
				$post      = acf_get_internal_post_type( $key, $post_type );

				if ( empty( $post ) ) {
					continue;
				}

				if ( 'acf-field-group' === $post_type ) {
					$post['fields'] = acf_get_fields( $post );
				}

				$post   = acf_prepare_internal_post_type_for_export( $post, $post_type );
				$json[] = $post;
			}

			return $json;
		}

	}

	// initialize
	acf_register_admin_tool( 'ACF_Admin_Tool_Export' );

endif; // class_exists check

?>
