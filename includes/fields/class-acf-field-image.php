<?php

if ( ! class_exists( 'acf_field_image' ) ) :

	class acf_field_image extends acf_field {


		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type    function
		*  @date    5/03/2014
		*  @since   5.0.0
		*
		*  @param   n/a
		*  @return  n/a
		*/

		function initialize() {

			// vars
			$this->name          = 'image';
			$this->label         = __( 'Image', 'acf' );
			$this->category      = 'content';
			$this->description   = __( 'Uses the native WordPress media picker to upload, or choose images.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-image.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/image/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'return_format' => 'array',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'min_width'     => 0,
				'min_height'    => 0,
				'min_size'      => 0,
				'max_width'     => 0,
				'max_height'    => 0,
				'max_size'      => 0,
				'mime_types'    => '',
			);

			// filters
			add_filter( 'get_media_item_args', array( $this, 'get_media_item_args' ) );

		}


		/*
		*  input_admin_enqueue_scripts
		*
		*  description
		*
		*  @type    function
		*  @date    16/12/2015
		*  @since   5.3.2
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function input_admin_enqueue_scripts() {

			// localize
			acf_localize_text(
				array(
					'Select Image' => __( 'Select Image', 'acf' ),
					'Edit Image'   => __( 'Edit Image', 'acf' ),
					'Update Image' => __( 'Update Image', 'acf' ),
					'All images'   => __( 'All images', 'acf' ),
				)
			);
		}

		/**
		 * Renders the field HTML.
		 *
		 * @date    23/01/13
		 * @since   3.6.0
		 *
		 * @param   array $field The field settings.
		 * @return  void
		 */
		function render_field( $field ) {
			$uploader = acf_get_setting( 'uploader' );

			// Enqueue uploader scripts
			if ( $uploader === 'wp' ) {
				acf_enqueue_uploader();
			}

			// Elements and attributes.
			$value     = '';
			$div_attrs = array(
				'class'             => 'acf-image-uploader',
				'data-preview_size' => $field['preview_size'],
				'data-library'      => $field['library'],
				'data-mime_types'   => $field['mime_types'],
				'data-uploader'     => $uploader,
			);
			$img_attrs = array(
				'src'       => '',
				'alt'       => '',
				'data-name' => 'image',
			);

			// Detect value.
			if ( $field['value'] && is_numeric( $field['value'] ) ) {
				$image = wp_get_attachment_image_src( $field['value'], $field['preview_size'] );
				if ( $image ) {
					$value               = $field['value'];
					$img_attrs['src']    = $image[0];
					$img_attrs['alt']    = get_post_meta( $field['value'], '_wp_attachment_image_alt', true );
					$div_attrs['class'] .= ' has-value';
				}
			}

			// Add "preview size" max width and height style.
			// Apply max-width to wrap, and max-height to img for max compatibility with field widths.
			$size               = acf_get_image_size( $field['preview_size'] );
			$size_w             = $size['width'] ? $size['width'] . 'px' : '100%';
			$size_h             = $size['height'] ? $size['height'] . 'px' : '100%';
			$img_attrs['style'] = sprintf( 'max-height: %s;', $size_h );

			// Render HTML.
			?>
<div <?php echo acf_esc_attrs( $div_attrs ); ?>>
			<?php
			acf_hidden_input(
				array(
					'name'  => $field['name'],
					'value' => $value,
				)
			);
			?>
	<div class="show-if-value image-wrap" style="max-width: <?php echo esc_attr( $size_w ); ?>">
		<img <?php echo acf_esc_attrs( $img_attrs ); ?> />
		<div class="acf-actions -hover">
			<?php if ( $uploader !== 'basic' ) : ?>
			<a class="acf-icon -pencil dark" data-name="edit" href="#" title="<?php _e( 'Edit', 'acf' ); ?>"></a>
			<?php endif; ?>
			<a class="acf-icon -cancel dark" data-name="remove" href="#" title="<?php _e( 'Remove', 'acf' ); ?>"></a>
		</div>
	</div>
	<div class="hide-if-value">
			<?php if ( $uploader === 'basic' ) : ?>
				<?php if ( $field['value'] && ! is_numeric( $field['value'] ) ) : ?>
				<div class="acf-error-message"><p><?php echo acf_esc_html( $field['value'] ); ?></p></div>
			<?php endif; ?>
			<label class="acf-basic-uploader">
				<?php
				acf_file_input(
					array(
						'name' => $field['name'],
						'id'   => $field['id'],
						'key'  => $field['key'],
					)
				);
				?>
			</label>
		<?php else : ?>
			<p><?php _e( 'No image selected', 'acf' ); ?> <a data-name="add" class="acf-button button" href="#"><?php _e( 'Add Image', 'acf' ); ?></a></p>
		<?php endif; ?>
	</div>
</div>
			<?php
		}


		/*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field  - an array holding all the field's data
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
					'label'        => __( 'Allowed File Types', 'acf' ),
					'instructions' => __( 'Comma separated list. Leave blank for all types', 'acf' ),
					'type'         => 'text',
					'name'         => 'mime_types',
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
					'label'        => __( 'Preview Size', 'acf' ),
					'instructions' => '',
					'type'         => 'select',
					'name'         => 'preview_size',
					'choices'      => acf_get_image_sizes(),
				)
			);
		}

		/*
		*  format_value()
		*
		*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value (mixed) the value which was loaded from the database
		*  @param   $post_id (mixed) the $post_id from which the value was loaded
		*  @param   $field (array) the field array holding all the field options
		*
		*  @return  $value (mixed) the modified value
		*/

		function format_value( $value, $post_id, $field ) {

			// bail early if no value
			if ( empty( $value ) ) {
				return false;
			}

			// bail early if not numeric (error message)
			if ( ! is_numeric( $value ) ) {
				return false;
			}

			// convert to int
			$value = intval( $value );

			// format
			if ( $field['return_format'] == 'url' ) {

				return wp_get_attachment_url( $value );

			} elseif ( $field['return_format'] == 'array' ) {

				return acf_get_attachment( $value );

			}

			// return
			return $value;

		}


		/*
		*  get_media_item_args
		*
		*  description
		*
		*  @type    function
		*  @date    27/01/13
		*  @since   3.6.0
		*
		*  @param   $vars (array)
		*  @return  $vars
		*/

		function get_media_item_args( $vars ) {

			$vars['send'] = true;
			return( $vars );

		}


		/*
		*  update_value()
		*
		*  This filter is appied to the $value before it is updated in the db
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $value - the value which will be saved in the database
		*  @param   $post_id - the $post_id of which the value will be saved
		*  @param   $field - the field array holding all the field options
		*
		*  @return  $value - the modified value
		*/

		function update_value( $value, $post_id, $field ) {

			return acf_get_field_type( 'file' )->update_value( $value, $post_id, $field );

		}


		/**
		 *  validate_value
		 *
		 *  This function will validate a basic file input
		 *
		 *  @type    function
		 *  @date    11/02/2014
		 *  @since   5.0.0
		 *
		 *  @param   $post_id (int)
		 *  @return  $post_id (int)
		 */
		function validate_value( $valid, $value, $field, $input ) {
			return acf_get_field_type( 'file' )->validate_value( $valid, $value, $field, $input );
		}

		/**
		 * Additional validation for the image field when submitted via REST.
		 *
		 * @param bool  $valid
		 * @param int   $value
		 * @param array $field
		 *
		 * @return bool|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			return acf_get_field_type( 'file' )->validate_rest_value( $valid, $value, $field );
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			return acf_get_field_type( 'file' )->get_rest_schema( $field );
		}

		/**
		 * Apply basic formatting to prepare the value for default REST output.
		 *
		 * @param mixed      $value
		 * @param string|int $post_id
		 * @param array      $field
		 * @return mixed
		 */
		public function format_value_for_rest( $value, $post_id, array $field ) {
			return acf_format_numerics( $value );
		}

	}


	// initialize
	acf_register_field_type( 'acf_field_image' );

endif; // class_exists check

?>
