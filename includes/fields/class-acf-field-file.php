<?php

if ( ! class_exists( 'acf_field_file' ) ) :

	class acf_field_file extends acf_field {


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
			$this->name     = 'file';
			$this->label    = __( 'File', 'acf' );
			$this->category = 'content';
			$this->defaults = array(
				'return_format' => 'array',
				'library'       => 'all',
				'min_size'      => 0,
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
					'Select File' => __( 'Select File', 'acf' ),
					'Edit File'   => __( 'Edit File', 'acf' ),
					'Update File' => __( 'Update File', 'acf' ),
				)
			);
		}


		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param   $field - an array holding all the field's data
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*/

		function render_field( $field ) {

			// vars
			$uploader = acf_get_setting( 'uploader' );

			// allow custom uploader
			$uploader = acf_maybe_get( $field, 'uploader', $uploader );

			// enqueue
			if ( $uploader == 'wp' ) {
				acf_enqueue_uploader();
			}

			// vars
			$o = array(
				'icon'     => '',
				'title'    => '',
				'url'      => '',
				'filename' => '',
				'filesize' => '',
			);

			$div = array(
				'class'           => 'acf-file-uploader',
				'data-library'    => $field['library'],
				'data-mime_types' => $field['mime_types'],
				'data-uploader'   => $uploader,
			);

			// has value?
			if ( $field['value'] ) {

				$attachment = acf_get_attachment( $field['value'] );
				if ( $attachment ) {

					// has value
					$div['class'] .= ' has-value';

					// update
					$o['icon']     = $attachment['icon'];
					$o['title']    = $attachment['title'];
					$o['url']      = $attachment['url'];
					$o['filename'] = $attachment['filename'];
					if ( $attachment['filesize'] ) {
						$o['filesize'] = size_format( $attachment['filesize'] );
					}
				}
			}

			?>
<div <?php echo acf_esc_attrs( $div ); ?>>
			<?php
			acf_hidden_input(
				array(
					'name'      => $field['name'],
					'value'     => $field['value'],
					'data-name' => 'id',
				)
			);
			?>
	<div class="show-if-value file-wrap">
		<div class="file-icon">
			<img data-name="icon" src="<?php echo esc_url( $o['icon'] ); ?>" alt=""/>
		</div>
		<div class="file-info">
			<p>
				<strong data-name="title"><?php echo esc_html( $o['title'] ); ?></strong>
			</p>
			<p>
				<strong><?php _e( 'File name', 'acf' ); ?>:</strong>
				<a data-name="filename" href="<?php echo esc_url( $o['url'] ); ?>" target="_blank"><?php echo esc_html( $o['filename'] ); ?></a>
			</p>
			<p>
				<strong><?php _e( 'File size', 'acf' ); ?>:</strong>
				<span data-name="filesize"><?php echo esc_html( $o['filesize'] ); ?></span>
			</p>
		</div>
		<div class="acf-actions -hover">
			<?php if ( $uploader != 'basic' ) : ?>
			<a class="acf-icon -pencil dark" data-name="edit" href="#" title="<?php _e( 'Edit', 'acf' ); ?>"></a>
			<?php endif; ?>
			<a class="acf-icon -cancel dark" data-name="remove" href="#" title="<?php _e( 'Remove', 'acf' ); ?>"></a>
		</div>
	</div>
	<div class="hide-if-value">
			<?php if ( $uploader == 'basic' ) : ?>
			
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
			
			<p><?php _e( 'No file selected', 'acf' ); ?> <a data-name="add" class="acf-button button" href="#"><?php _e( 'Add File', 'acf' ); ?></a></p>
			
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
					'label'        => __( 'Return Value', 'acf' ),
					'instructions' => __( 'Specify the returned value on front end', 'acf' ),
					'type'         => 'radio',
					'name'         => 'return_format',
					'layout'       => 'horizontal',
					'choices'      => array(
						'array' => __( 'File Array', 'acf' ),
						'url'   => __( 'File URL', 'acf' ),
						'id'    => __( 'File ID', 'acf' ),
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
				'min_size',
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
					'label'        => __( 'Minimum', 'acf' ),
					'instructions' => __( 'Restrict which files can be uploaded', 'acf' ),
					'type'         => 'text',
					'name'         => 'min_size',
					'prepend'      => __( 'File size', 'acf' ),
					'append'       => 'MB',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Maximum', 'acf' ),
					'instructions' => __( 'Restrict which files can be uploaded', 'acf' ),
					'type'         => 'text',
					'name'         => 'max_size',
					'prepend'      => __( 'File size', 'acf' ),
					'append'       => 'MB',
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label' => __( 'Allowed file types', 'acf' ),
					'hint'  => __( 'Comma separated list. Leave blank for all types', 'acf' ),
					'type'  => 'text',
					'name'  => 'mime_types',
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

			// Bail early if no value.
			if ( empty( $value ) ) {
				return $value;
			}

			// Parse value for id.
			$attachment_id = acf_idval( $value );

			// Connect attacment to post.
			acf_connect_attachment_to_post( $attachment_id, $post_id );

			// Return id.
			return $attachment_id;
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

			// bail early if empty
			if ( empty( $value ) ) {
				return $valid;
			}

			// bail early if is numeric
			if ( is_numeric( $value ) ) {
				return $valid;
			}

			// bail early if not basic string
			if ( ! is_string( $value ) ) {
				return $valid;
			}

			// decode value
			$file = null;
			parse_str( $value, $file );

			// bail early if no attachment
			if ( empty( $file ) ) {
				return $valid;
			}

			// get errors
			$errors = acf_validate_attachment( $file, $field, 'basic_upload' );

			// append error
			if ( ! empty( $errors ) ) {
				$valid = implode( "\n", $errors );
			}

			// return
			return $valid;
		}

		/**
		 * Validates file fields updated via the REST API.
		 *
		 * @param bool  $valid
		 * @param int   $value
		 * @param array $field
		 *
		 * @return bool|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			if ( is_null( $value ) && empty( $field['required'] ) ) {
				return $valid;
			}

			/**
			 * A bit of a hack, but we use `wp_prepare_attachment_for_js()` here
			 * since it returns all the data we need to validate the file, and we use this anyways
			 * to validate fields updated via UI.
			 */
			$attachment = wp_prepare_attachment_for_js( $value );
			$param      = sprintf( '%s[%s]', $field['prefix'], $field['name'] );
			$data       = array(
				'param' => $param,
				'value' => (int) $value,
			);

			if ( ! $attachment ) {
				$error = sprintf( __( '%s requires a valid attachment ID.', 'acf' ), $param );
				return new WP_Error( 'rest_invalid_param', $error, $data );
			}

			$errors = acf_validate_attachment( $attachment, $field, 'prepare' );

			if ( ! empty( $errors ) ) {
				$error = $param . ' - ' . implode( ' ', $errors );
				return new WP_Error( 'rest_invalid_param', $error, $data );
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
				'type'     => array( 'integer', 'null' ),
				'required' => isset( $field['required'] ) && $field['required'],
			);

			if ( ! empty( $field['min_width'] ) ) {
				$schema['minWidth'] = (int) $field['min_width'];
			}

			if ( ! empty( $field['min_height'] ) ) {
				$schema['minHeight'] = (int) $field['min_height'];
			}

			if ( ! empty( $field['min_size'] ) ) {
				$schema['minSize'] = $field['min_size'];
			}

			if ( ! empty( $field['max_width'] ) ) {
				$schema['maxWidth'] = (int) $field['max_width'];
			}

			if ( ! empty( $field['max_height'] ) ) {
				$schema['maxHeight'] = (int) $field['max_height'];
			}

			if ( ! empty( $field['max_size'] ) ) {
				$schema['maxSize'] = $field['max_size'];
			}

			if ( ! empty( $field['mime_types'] ) ) {
				$schema['mimeTypes'] = $field['mime_types'];
			}

			return $schema;
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
	acf_register_field_type( 'acf_field_file' );

endif; // class_exists check

?>
