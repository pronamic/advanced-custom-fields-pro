<?php

if ( ! class_exists( 'acf_field_gallery' ) ) :

	class acf_field_gallery extends acf_field {


		/**
		 * This function will setup the field type data
		 *
		 * @type    function
		 * @date    5/03/2014
		 * @since   5.0.0
		 *
		 * @param   n/a
		 * @return  n/a
		 */

		function initialize() {

			// vars
			$this->name          = 'gallery';
			$this->label         = __( 'Gallery', 'acf' );
			$this->category      = 'content';
			$this->description   = __( 'An interactive interface for managing a collection of attachments, such as images.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-gallery.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/gallery/', 'docs', 'field-type-selection' );
			$this->tutorial_url  = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/how-to-use-the-gallery-field/', 'docs', 'field-type-selection' );
			$this->pro           = true;
			$this->defaults      = array(
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'insert'        => 'append',
				'library'       => 'all',
				'min'           => 0,
				'max'           => 0,
				'min_width'     => 0,
				'min_height'    => 0,
				'min_size'      => 0,
				'max_width'     => 0,
				'max_height'    => 0,
				'max_size'      => 0,
				'mime_types'    => '',
			);

			// actions
			add_action( 'wp_ajax_acf/fields/gallery/get_attachment', array( $this, 'ajax_get_attachment' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/gallery/get_attachment', array( $this, 'ajax_get_attachment' ) );

			add_action( 'wp_ajax_acf/fields/gallery/update_attachment', array( $this, 'ajax_update_attachment' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/gallery/update_attachment', array( $this, 'ajax_update_attachment' ) );

			add_action( 'wp_ajax_acf/fields/gallery/get_sort_order', array( $this, 'ajax_get_sort_order' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/gallery/get_sort_order', array( $this, 'ajax_get_sort_order' ) );
		}

		/**
		 * description
		 *
		 * @type    function
		 * @date    16/12/2015
		 * @since   5.3.2
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */

		function input_admin_enqueue_scripts() {

			// localize
			acf_localize_text(
				array(
					'Add Image to Gallery'      => __( 'Add Image to Gallery', 'acf' ),
					'Maximum selection reached' => __( 'Maximum selection reached', 'acf' ),
				)
			);
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    13/12/2013
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */

		function ajax_get_attachment() {

			// Validate requrest.
			if ( ! acf_verify_ajax() ) {
				die();
			}

			// Get args.
			$args = acf_request_args(
				array(
					'id'        => 0,
					'field_key' => '',
				)
			);

			// Cast args.
			$args['id'] = (int) $args['id'];

			// Bail early if no id.
			if ( ! $args['id'] ) {
				die();
			}

			// Load field.
			$field = acf_get_field( $args['field_key'] );
			if ( ! $field ) {
				die();
			}

			// Render.
			$this->render_attachment( $args['id'], $field );
			die;
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    13/12/2013
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */

		function ajax_update_attachment() {

			if ( ! isset( $_POST['nonce'] ) ) {
				wp_send_json_error();
			}

			// validate nonce
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'acf_nonce' ) ) {
				wp_send_json_error();
			}

			// bail early if no attachments
			if ( empty( $_POST['attachments'] ) ) {
				wp_send_json_error();
			}

			// loop over attachments
			foreach ( $_POST['attachments'] as $id => $changes ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized by WP core when saved.

				if ( ! current_user_can( 'edit_post', $id ) ) {
					wp_send_json_error();
				}

				$post = get_post( $id, ARRAY_A );

				if ( 'attachment' != $post['post_type'] ) {
					wp_send_json_error();
				}

				if ( isset( $changes['title'] ) ) {
					$post['post_title'] = $changes['title'];
				}

				if ( isset( $changes['caption'] ) ) {
					$post['post_excerpt'] = $changes['caption'];
				}

				if ( isset( $changes['description'] ) ) {
					$post['post_content'] = $changes['description'];
				}

				if ( isset( $changes['alt'] ) ) {
					$alt = wp_unslash( $changes['alt'] );
					if ( $alt != get_post_meta( $id, '_wp_attachment_image_alt', true ) ) {
						$alt = wp_strip_all_tags( $alt, true );
						update_post_meta( $id, '_wp_attachment_image_alt', wp_slash( $alt ) );
					}
				}

				// save post
				wp_update_post( $post );

				/** This filter is documented in wp-admin/includes/media.php */
				// - seems off to run this filter AFTER the update_post function, but there is a reason
				// - when placed BEFORE, an empty post_title will be populated by WP
				// - this filter will still allow 3rd party to save extra image data!
				$post = apply_filters( 'attachment_fields_to_save', $post, $changes );

				// save meta
				acf_save_post( $id );
			}

			// return
			wp_send_json_success();
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    13/12/2013
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */

		function ajax_get_sort_order() {

			// vars
			$r     = array();
			$order = 'DESC';
			$args  = acf_parse_args(
				$_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified below.
				array(
					'ids'       => 0,
					'sort'      => 'date',
					'field_key' => '',
					'nonce'     => '',
				)
			);

			// validate
			if ( ! wp_verify_nonce( $args['nonce'], 'acf_nonce' ) ) {
				wp_send_json_error();
			}

			// reverse
			if ( $args['sort'] == 'reverse' ) {
				$ids = array_reverse( $args['ids'] );

				wp_send_json_success( $ids );
			}

			if ( $args['sort'] == 'title' ) {
				$order = 'ASC';
			}

			// find attachments (DISTINCT POSTS)
			$ids = get_posts(
				array(
					'post_type'   => 'attachment',
					'numberposts' => -1,
					'post_status' => 'any',
					'post__in'    => $args['ids'],
					'order'       => $order,
					'orderby'     => $args['sort'],
					'fields'      => 'ids',
				)
			);

			// success
			if ( ! empty( $ids ) ) {
				wp_send_json_success( $ids );
			}

			// failure
			wp_send_json_error();
		}

		/**
		 * Renders the sidebar HTML shown when selecting an attachmemnt.
		 *
		 * @date    13/12/2013
		 * @since   5.0.0
		 *
		 * @param   integer $id    The attachment ID.
		 * @param   array   $field The field array.
		 * @return  void
		 */
		function render_attachment( $id, $field ) {
			// Load attachmenet data.
			$attachment = wp_prepare_attachment_for_js( $id );
			$compat     = get_compat_media_markup( $id );

			// Get attachment thumbnail (video).
			if ( isset( $attachment['thumb']['src'] ) ) {
				$thumb = $attachment['thumb']['src'];

				// Look for thumbnail size (image).
			} elseif ( isset( $attachment['sizes']['thumbnail']['url'] ) ) {
				$thumb = $attachment['sizes']['thumbnail']['url'];

				// Use url for svg.
			} elseif ( $attachment['type'] === 'image' ) {
				$thumb = $attachment['url'];

				// Default to icon.
			} else {
				$thumb = wp_mime_type_icon( $id );
			}

			// Get attachment dimensions / time / size.
			$dimensions = '';
			if ( $attachment['type'] === 'audio' ) {
				$dimensions = __( 'Length', 'acf' ) . ': ' . $attachment['fileLength'];
			} elseif ( ! empty( $attachment['width'] ) ) {
				$dimensions = $attachment['width'] . ' x ' . $attachment['height'];
			}
			if ( ! empty( $attachment['filesizeHumanReadable'] ) ) {
				$dimensions .= ' (' . $attachment['filesizeHumanReadable'] . ')';
			}

			?>
		<div class="acf-gallery-side-info">
			<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $attachment['alt'] ); ?>" />
			<p class="filename"><strong><?php echo esc_html( $attachment['filename'] ); ?></strong></p>
			<p class="uploaded"><?php echo esc_html( $attachment['dateFormatted'] ); ?></p>
			<p class="dimensions"><?php echo esc_html( $dimensions ); ?></p>
			<p class="actions">
				<a href="#" class="acf-gallery-edit" data-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Edit', 'acf' ); ?></a>
				<a href="#" class="acf-gallery-remove" data-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Remove', 'acf' ); ?></a>
			</p>
		</div>
		<table class="form-table">
			<tbody>
				<?php

				// Render fields.
				$prefix = 'attachments[' . $id . ']';

				acf_render_field_wrap(
					array(
						// 'key'     => "{$field['key']}-title",
						'name'   => 'title',
						'prefix' => $prefix,
						'type'   => 'text',
						'label'  => __( 'Title', 'acf' ),
						'value'  => $attachment['title'],
					),
					'tr'
				);

					acf_render_field_wrap(
						array(
							// 'key'     => "{$field['key']}-caption",
							'name'   => 'caption',
							'prefix' => $prefix,
							'type'   => 'textarea',
							'label'  => __( 'Caption', 'acf' ),
							'value'  => $attachment['caption'],
						),
						'tr'
					);

					acf_render_field_wrap(
						array(
							// 'key'     => "{$field['key']}-alt",
							'name'   => 'alt',
							'prefix' => $prefix,
							'type'   => 'text',
							'label'  => __( 'Alt Text', 'acf' ),
							'value'  => $attachment['alt'],
						),
						'tr'
					);

					acf_render_field_wrap(
						array(
							// 'key'     => "{$field['key']}-description",
							'name'   => 'description',
							'prefix' => $prefix,
							'type'   => 'textarea',
							'label'  => __( 'Description', 'acf' ),
							'value'  => $attachment['description'],
						),
						'tr'
					);

				?>
			</tbody>
		</table>
			<?php

			// Display compat fields.
			echo $compat['item']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside get_compat_media_markup().
		}

		/**
		 * Create the HTML interface for your field
		 *
		 * @param   $field - an array holding all the field's data
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 */

		function render_field( $field ) {

			// Enqueue uploader assets.
			acf_enqueue_uploader();

			// Control attributes.
			$attrs = array(
				'id'                => $field['id'],
				'class'             => "acf-gallery {$field['class']}",
				'data-library'      => $field['library'],
				'data-preview_size' => $field['preview_size'],
				'data-min'          => $field['min'],
				'data-max'          => $field['max'],
				'data-mime_types'   => $field['mime_types'],
				'data-insert'       => $field['insert'],
				'data-columns'      => 4,
			);

			// Set gallery height with deafult of 400px and minimum of 200px.
			$height         = acf_get_user_setting( 'gallery_height', 400 );
			$height         = max( $height, 200 );
			$attrs['style'] = "height:{$height}px";

			// Load attachments.
			$attachments = array();
			if ( $field['value'] ) {

				// Clean value into an array of IDs.
				$attachment_ids = array_map( 'intval', acf_array( $field['value'] ) );

				// Find posts in database (ensures all results are real).
				$posts = acf_get_posts(
					array(
						'post_type'              => 'attachment',
						'post__in'               => $attachment_ids,
						'update_post_meta_cache' => true,
						'update_post_term_cache' => false,
					)
				);

				// Load attatchment data for each post.
				$attachments = array_map( 'acf_get_attachment', $posts );
			}

			?>
<div <?php echo acf_esc_attrs( $attrs ); ?>>
	<input type="hidden" name="<?php echo esc_attr( $field['name'] ); ?>" value="" />
	<div class="acf-gallery-main">
		<div class="acf-gallery-attachments">
			<?php if ( $attachments ) : ?>
				<?php
				foreach ( $attachments as $i => $attachment ) :

					// Vars
					$a_id       = $attachment['ID'];
					$a_title    = $attachment['title'];
					$a_type     = $attachment['type'];
					$a_filename = $attachment['filename'];
					$a_class    = "acf-gallery-attachment -{$a_type}";

					// Get thumbnail.
					$a_thumbnail = acf_get_post_thumbnail( $a_id, $field['preview_size'] );
					$a_class    .= ( $a_thumbnail['type'] === 'icon' ) ? ' -icon' : '';

					?>
					<div class="<?php echo esc_attr( $a_class ); ?>" data-id="<?php echo esc_attr( $a_id ); ?>">
						<input type="hidden" name="<?php echo esc_attr( $field['name'] ); ?>[]" value="<?php echo esc_attr( $a_id ); ?>" />
						<div class="margin">
							<div class="thumbnail">
								<img src="<?php echo esc_url( $a_thumbnail['url'] ); ?>" alt="" />
							</div>
							<?php if ( $a_type !== 'image' ) : ?>
								<div class="filename"><?php echo acf_esc_html( acf_get_truncated( $a_filename, 30 ) ); ?></div>	
							<?php endif; ?>
						</div>
						<div class="actions">
							<a class="acf-icon -cancel dark acf-gallery-remove" href="#" data-id="<?php echo esc_attr( $a_id ); ?>" title="<?php esc_html_e( 'Remove', 'acf' ); ?>"></a>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<div class="acf-gallery-toolbar">
			<ul class="acf-hl">
				<li>
					<a href="#" class="acf-button button button-primary acf-gallery-add"><?php esc_html_e( 'Add to gallery', 'acf' ); ?></a>
				</li>
				<li class="acf-fr">
					<select class="acf-gallery-sort">
						<option value=""><?php esc_html_e( 'Bulk actions', 'acf' ); ?></option>
						<option value="date"><?php esc_html_e( 'Sort by date uploaded', 'acf' ); ?></option>
						<option value="modified"><?php esc_html_e( 'Sort by date modified', 'acf' ); ?></option>
						<option value="title"><?php esc_html_e( 'Sort by title', 'acf' ); ?></option>
						<option value="reverse"><?php esc_html_e( 'Reverse current order', 'acf' ); ?></option>
					</select>
				</li>
			</ul>
		</div>
	</div>
	<div class="acf-gallery-side">
		<div class="acf-gallery-side-inner">
			<div class="acf-gallery-side-data"></div>
			<div class="acf-gallery-toolbar">
				<ul class="acf-hl">
					<li>
						<a href="#" class="acf-button button acf-gallery-close"><?php esc_html_e( 'Close', 'acf' ); ?></a>
					</li>
					<li class="acf-fr">
						<a class="acf-button button button-primary acf-gallery-update" href="#"><?php esc_html_e( 'Update', 'acf' ); ?></a>
					</li>
				</ul>
			</div>
		</div>	
	</div>
</div>
			<?php
		}


		/**
		 * Create extra options for your field. This is rendered when editing a field.
		 * The value of $field['name'] can be used (like bellow) to save extra data to the $field
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $field  - an array holding all the field's data
		 */

		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Format', 'acf' ),
					'instructions' => '',
					'type'         => 'radio',
					'name'         => 'return_format',
					'layout'       => 'horizontal',
					'choices'      => array(
						'array' => __( 'Image Array', 'acf' ),
						'url'   => __( 'Image URL', 'acf' ),
						'id'    => __( 'Image ID', 'acf' ),
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Library', 'acf' ),
					'instructions' => __( 'Limit the media library choice', 'acf' ),
					'type'         => 'radio',
					'name'         => 'library',
					'layout'       => 'horizontal',
					'choices'      => array(
						'all'        => __( 'All', 'acf' ),
						'uploadedTo' => __( 'Uploaded to post', 'acf' ),
					),
				)
			);
		}

		/**
		 * Renders the field settings used in the "Validation" tab.
		 *
		 * @since 6.0
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		function render_field_validation_settings( $field ) {
			// Clear numeric settings.
			$clear = array(
				'min',
				'max',
				'min_width',
				'min_height',
				'min_size',
				'max_width',
				'max_height',
				'max_size',
			);

			foreach ( $clear as $k ) {
				if ( empty( $field[ $k ] ) ) {
					$field[ $k ] = '';
				}
			}

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Minimum Selection', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'min',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Maximum Selection', 'acf' ),
					'instructions' => '',
					'type'         => 'number',
					'name'         => 'max',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'   => __( 'Minimum', 'acf' ),
					'hint'    => __( 'Restrict which images can be uploaded', 'acf' ),
					'type'    => 'text',
					'name'    => 'min_width',
					'prepend' => __( 'Width', 'acf' ),
					'append'  => 'px',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'   => '',
					'type'    => 'text',
					'name'    => 'min_height',
					'prepend' => __( 'Height', 'acf' ),
					'append'  => 'px',
					'_append' => 'min_width',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'   => '',
					'type'    => 'text',
					'name'    => 'min_size',
					'prepend' => __( 'File size', 'acf' ),
					'append'  => 'MB',
					'_append' => 'min_width',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'   => __( 'Maximum', 'acf' ),
					'hint'    => __( 'Restrict which images can be uploaded', 'acf' ),
					'type'    => 'text',
					'name'    => 'max_width',
					'prepend' => __( 'Width', 'acf' ),
					'append'  => 'px',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'   => '',
					'type'    => 'text',
					'name'    => 'max_height',
					'prepend' => __( 'Height', 'acf' ),
					'append'  => 'px',
					'_append' => 'max_width',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'   => '',
					'type'    => 'text',
					'name'    => 'max_size',
					'prepend' => __( 'File size', 'acf' ),
					'append'  => 'MB',
					'_append' => 'max_width',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label' => __( 'Allowed File Types', 'acf' ),
					'hint'  => __( 'Comma separated list. Leave blank for all types', 'acf' ),
					'type'  => 'text',
					'name'  => 'mime_types',
				)
			);
		}

		/**
		 * Renders the field settings used in the "Presentation" tab.
		 *
		 * @since 6.0
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		function render_field_presentation_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Insert', 'acf' ),
					'instructions' => __( 'Specify where new attachments are added', 'acf' ),
					'type'         => 'select',
					'name'         => 'insert',
					'choices'      => array(
						'append'  => __( 'Append to the end', 'acf' ),
						'prepend' => __( 'Prepend to the beginning', 'acf' ),
					),
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Preview Size', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'preview_size',
					'choices'      => acf_get_image_sizes(),
				)
			);
		}

		/**
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value (mixed) the value which was loaded from the database
		 * @param   $post_id (mixed) the post_id from which the value was loaded
		 * @param   $field (array) the field array holding all the field options
		 *
		 * @return  $value (mixed) the modified value
		 */

		function format_value( $value, $post_id, $field ) {

			// Bail early if no value.
			if ( ! $value ) {
				return false;
			}

			// Clean value into an array of IDs.
			$attachment_ids = array_map( 'intval', acf_array( $value ) );

			// Find posts in database (ensures all results are real).
			$posts = acf_get_posts(
				array(
					'post_type'              => 'attachment',
					'post__in'               => $attachment_ids,
					'update_post_meta_cache' => true,
					'update_post_term_cache' => false,
				)
			);

			// Bail early if no posts found.
			if ( ! $posts ) {
				return false;
			}

			// Format values using field settings.
			$value = array();
			foreach ( $posts as $post ) {

				// Return object.
				if ( $field['return_format'] == 'object' ) {
					$item = $post;

					// Return array.
				} elseif ( $field['return_format'] == 'array' ) {
					$item = acf_get_attachment( $post );

					// Return URL.
				} elseif ( $field['return_format'] == 'url' ) {
					$item = wp_get_attachment_url( $post->ID );

					// Return ID.
				} else {
					$item = $post->ID;
				}

				// Append item.
				$value[] = $item;
			}

			// Return.
			return $value;
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    11/02/2014
		 * @since   5.0.0
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */

		function validate_value( $valid, $value, $field, $input ) {

			if ( empty( $value ) || ! is_array( $value ) ) {
				$value = array();
			}

			if ( count( $value ) < $field['min'] ) {
				$valid = _n( '%1$s requires at least %2$s selection', '%1$s requires at least %2$s selections', $field['min'], 'acf' );
				$valid = sprintf( $valid, $field['label'], $field['min'] );
			}

			return $valid;
		}


		/**
		 * This filter is appied to the $value before it is updated in the db
		 *
		 * @type    filter
		 * @since   3.6
		 * @date    23/01/13
		 *
		 * @param   $value - the value which will be saved in the database
		 * @param   $post_id - the post_id of which the value will be saved
		 * @param   $field - the field array holding all the field options
		 *
		 * @return  $value - the modified value
		 */

		function update_value( $value, $post_id, $field ) {

			// Bail early if no value.
			if ( empty( $value ) ) {
				return $value;
			}

			// Convert to array.
			$value = acf_array( $value );

			// Format array of values.
			// - ensure each value is an id.
			// - Parse each id as string for SQL LIKE queries.
			$value = array_map( 'acf_idval', $value );
			$value = array_map( 'strval', $value );

			// Return value.
			return $value;
		}

		/**
		 * Validates file fields updated via the REST API.
		 *
		 * @param  boolean $valid The current validity booleean
		 * @param  integer $value The value of the field
		 * @param  array   $field The field array
		 * @return boolean|WP
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			if ( ! $valid || ! is_array( $value ) ) {
				return $valid;
			}

			foreach ( $value as $attachment_id ) {
				$file_valid = acf_get_field_type( 'file' )->validate_rest_value( $valid, $attachment_id, $field );

				if ( is_wp_error( $file_valid ) ) {
					return $file_valid;
				}
			}

			return $valid;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema = array(
				'type'     => array( 'array', 'null' ),
				'required' => ! empty( $field['required'] ),
				'items'    => array(
					'type' => 'number',
				),
			);

			if ( ! empty( $field['min'] ) ) {
				$schema['minItems'] = (int) $field['min'];
			}

			if ( ! empty( $field['max'] ) ) {
				$schema['maxItems'] = (int) $field['max'];
			}

			return $schema;
		}

		/**
		 * @see \acf_field::get_rest_links()
		 * @param mixed          $value   The raw (unformatted) field value.
		 * @param integer|string $post_id
		 * @param array          $field
		 * @return array
		 */
		public function get_rest_links( $value, $post_id, array $field ) {
			$links = array();

			if ( empty( $value ) ) {
				return $links;
			}

			foreach ( (array) $value as $object_id ) {
				$links[] = array(
					'rel'        => 'acf:attachment',
					'href'       => rest_url( '/wp/v2/media/' . $object_id ),
					'embeddable' => true,
				);
			}

			return $links;
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @param mixed          $value
		 * @param string|integer $post_id
		 * @param array          $field
		 * @return mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			return acf_format_numerics( $value );
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_gallery' );
endif; // class_exists check

?>
