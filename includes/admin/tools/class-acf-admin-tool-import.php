<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2025 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ACF_Admin_Tool_Import' ) ) :

	class ACF_Admin_Tool_Import extends ACF_Admin_Tool {

		/**
		 * initialize
		 *
		 * This function will initialize the admin tool
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function initialize() {

			// vars
			$this->name  = 'import';
			$this->title = __( 'Import Field Groups', 'acf' );
			$this->icon  = 'dashicons-upload';
		}


		/**
		 * html
		 *
		 * This function will output the metabox HTML
		 *
		 * @date    10/10/17
		 * @since   5.6.3
		 *
		 * @param   n/a
		 * @return  n/a
		 */
		function html() {

			?>
		<div class="acf-postbox-header">
			<h2 class="acf-postbox-title"><?php esc_html_e( 'Import', 'acf' ); ?></h2>
			<div class="acf-tip"><i tabindex="0" class="acf-icon acf-icon-help acf-js-tooltip" title="<?php esc_attr_e( 'Choose an ACF JSON file to import. Use only files from trusted sources, then click Import.', 'acf' ); ?>">?</i></div>
		</div>
		<div class="acf-postbox-inner">
			<div class="acf-fields">
				<?php

				acf_render_field_wrap(
					array(
						'label'        => __( 'Select JSON File', 'acf' ),
						'type'         => 'file',
						'name'         => 'acf_import_file',
						'value'        => false,
						'uploader'     => 'basic',
						'mime_types'   => 'application/json,application/x-json,application/x-javascript,text/javascript,text/x-javascript,text/json',
						'instructions' => __( 'Import JSON containing field groups, post types, or taxonomies (trusted sources only)', 'acf' ),
					)
				);

				?>
			</div>
			<p class="acf-submit">
				<button type="submit" class="acf-btn" name="import_type" value="json">
					<?php esc_html_e( 'Import JSON', 'acf' ); ?>
				</button>
			</p>

			<?php
			if ( is_plugin_active( 'custom-post-type-ui/custom-post-type-ui.php' ) && acf_get_setting( 'enable_post_types' ) ) {
				$cptui_post_types  = get_option( 'cptui_post_types' );
				$cptui_taxonomies  = get_option( 'cptui_taxonomies' );
				$choices           = array();
				$overwrite_warning = false;

				if ( $cptui_post_types ) {
					$choices['post_types'] = __( 'Post Types', 'acf' );
					$existing_post_types   = acf_get_acf_post_types();

					foreach ( $existing_post_types as $existing_post_type ) {
						if ( isset( $cptui_post_types[ $existing_post_type['post_type'] ] ) ) {
							$overwrite_warning = true;
						}
					}
				}

				if ( $cptui_taxonomies ) {
					$choices['taxonomies'] = __( 'Taxonomies', 'acf' );

					if ( ! $overwrite_warning ) {
						$existing_taxonomies = acf_get_acf_taxonomies();
						foreach ( $existing_taxonomies as $existing_taxonomy ) {
							if ( isset( $cptui_taxonomies[ $existing_taxonomy['taxonomy'] ] ) ) {
								$overwrite_warning = true;
							}
						}
					}
				}

				if ( ! empty( $choices ) ) :
					?>
					<div class="acf-fields import-cptui">
						<?php
						acf_render_field_wrap(
							array(
								'label'   => __( 'Import from Custom Post Type UI', 'acf' ),
								'type'    => 'checkbox',
								'name'    => 'acf_import_cptui',
								'choices' => $choices,
								'toggle'  => true,
							)
						);
						?>
					</div>
					<?php
					if ( $overwrite_warning ) {
						echo '<p class="acf-inline-notice notice notice-info">' . esc_html__( 'Importing a Post Type or Taxonomy with the same key as one that already exists will overwrite the settings for the existing Post Type or Taxonomy with those of the import.', 'acf' ) . '</p>';
					}
					?>
					<p class="acf-submit">
						<button type="submit" class="acf-btn" name="import_type" value="cptui">
							<?php esc_html_e( 'Import from Custom Post Type UI', 'acf' ); ?>
						</button>
					</p>
					<?php
				endif;
			}
			?>
		</div>
			<?php
		}

		/**
		 * Imports the selected ACF posts and returns an admin notice on completion.
		 *
		 * @since 5.6.3
		 *
		 * @return ACF_Admin_Notice
		 */
		public function submit() {
			//phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce verified before this function is called.
			if ( 'cptui' === acf_request_arg( 'import_type', '' ) && ! empty( $_POST['acf_import_cptui'] ) ) {
				$import = acf_sanitize_request_args( $_POST['acf_import_cptui'] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- unslash not needed.
				return $this->import_cpt_ui( $import );
			}

			// Check file size.
			if ( empty( $_FILES['acf_import_file']['size'] ) ) {
				return acf_add_admin_notice( __( 'No file selected', 'acf' ), 'warning' );
			}

			$file = acf_sanitize_files_array( $_FILES['acf_import_file'] );

			// Check errors.
			if ( $file['error'] ) {
				return acf_add_admin_notice( __( 'Error uploading file. Please try again', 'acf' ), 'warning' );
			}

			// Check file type.
			if ( pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'json' ) {
				return acf_add_admin_notice( __( 'Incorrect file type', 'acf' ), 'warning' );
			}

			// Read JSON.
			$json = file_get_contents( $file['tmp_name'] );
			$json = json_decode( $json, true );

			// Check if empty.
			if ( ! $json || ! is_array( $json ) ) {
				return acf_add_admin_notice( __( 'Import file empty', 'acf' ), 'warning' );
			}

			// Ensure $json is an array of posts.
			if ( isset( $json['key'] ) ) {
				$json = array( $json );
			}

			// Remember imported post ids.
			$ids = array();

			// Loop over json.
			foreach ( $json as $to_import ) {
				// Search database for existing post.
				$post_type = acf_determine_internal_post_type( $to_import['key'] );
				$post      = acf_get_internal_post_type_post( $to_import['key'], $post_type );

				if ( $post ) {
					$to_import['ID'] = $post->ID;
				}

				// Import the post.
				$to_import = acf_import_internal_post_type( $to_import, $post_type );

				// Append message.
				$ids[] = $to_import['ID'];
			}

			// Count number of imported posts.
			$total = count( $ids );

			// Generate text.
			$text = sprintf( _n( 'Imported 1 item', 'Imported %s items', $total, 'acf' ), $total );

			// Add links to text.
			$links = array();
			foreach ( $ids as $id ) {
				$links[] = '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>';
			}
			$text .= ' ' . implode( ', ', $links );

			// Add notice.
			return acf_add_admin_notice( $text, 'success' );
			//phpcs:enable WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Handles the import of CPTUI post types and taxonomies.
		 *
		 * @since 6.1
		 *
		 * @param array $import_args What to import.
		 * @return ACF_Admin_Notice
		 */
		public function import_cpt_ui( $import_args ) {
			if ( ! is_array( $import_args ) ) {
				return acf_add_admin_notice( __( 'Nothing from Custom Post Type UI plugin selected for import.', 'acf' ), 'warning' );
			}

			$imported = array();

			// Import any post types.
			if ( in_array( 'post_types', $import_args, true ) ) {
				$cptui_post_types = get_option( 'cptui_post_types' );
				$instance         = acf_get_internal_post_type_instance( 'acf-post-type' );

				if ( ! is_array( $cptui_post_types ) || ! $instance ) {
					return acf_add_admin_notice( __( 'Failed to import post types.', 'acf' ), 'warning' );
				}

				foreach ( $cptui_post_types as $post_type ) {
					$result = $instance->import_cptui_post_type( $post_type );

					if ( is_array( $result ) && isset( $result['ID'] ) ) {
						$imported[] = (int) $result['ID'];
					}
				}
			}

			// Import any taxonomies.
			if ( in_array( 'taxonomies', $import_args, true ) ) {
				$cptui_taxonomies = get_option( 'cptui_taxonomies' );
				$instance         = acf_get_internal_post_type_instance( 'acf-taxonomy' );

				if ( ! is_array( $cptui_taxonomies ) || ! $instance ) {
					return acf_add_admin_notice( __( 'Failed to import taxonomies.', 'acf' ), 'warning' );
				}

				foreach ( $cptui_taxonomies as $taxonomy ) {
					$result = $instance->import_cptui_taxonomy( $taxonomy );

					if ( is_array( $result ) && isset( $result['ID'] ) ) {
						$imported[] = (int) $result['ID'];
					}
				}
			}

			if ( ! empty( $imported ) ) {
				// Generate text.
				$total = count( $imported );
				/* translators: %d - number of items imported from CPTUI */
				$text = sprintf( _n( 'Imported %d item from Custom Post Type UI -', 'Imported %d items from Custom Post Type UI -', $total, 'acf' ), $total );

				// Add links to text.
				$links = array();
				foreach ( $imported as $id ) {
					$links[] = '<a href="' . esc_url( get_edit_post_link( $id ) ) . '">' . esc_html( get_the_title( $id ) ) . '</a>';
				}

				$text .= ' ' . implode( ', ', $links );
				$text .= __( '. The Custom Post Type UI plugin can be deactivated.', 'acf' );

				return acf_add_admin_notice( $text, 'success' );
			}

			return acf_add_admin_notice( __( 'Nothing to import', 'acf' ), 'warning' );
		}
	}

	// Initialize.
	acf_register_admin_tool( 'ACF_Admin_Tool_Import' );
endif; // class_exists check.

?>
