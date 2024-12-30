<?php

if ( ! class_exists( 'acf_field_oembed' ) ) :
	#[AllowDynamicProperties]
	class acf_field_oembed extends acf_field {


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
			$this->name          = 'oembed';
			$this->label         = __( 'oEmbed', 'acf' );
			$this->category      = 'content';
			$this->description   = __( 'An interactive component for embedding videos, images, tweets, audio and other content by making use of the native WordPress oEmbed functionality.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-oembed.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/oembed/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'width'  => '',
				'height' => '',
			);
			$this->width         = 640;
			$this->height        = 390;
			$this->supports      = array(
				'escaping_html' => true, // The OEmbed field only produces html safe content from format_value.
			);

			// extra
			add_action( 'wp_ajax_acf/fields/oembed/search', array( $this, 'ajax_query' ) );
			add_action( 'wp_ajax_nopriv_acf/fields/oembed/search', array( $this, 'ajax_query' ) );
		}


		/**
		 * This function will prepare the field for input
		 *
		 * @type    function
		 * @date    14/2/17
		 * @since   5.5.8
		 *
		 * @param   $field (array)
		 * @return  (int)
		 */
		function prepare_field( $field ) {

			// defaults
			if ( ! $field['width'] ) {
				$field['width'] = $this->width;
			}
			if ( ! $field['height'] ) {
				$field['height'] = $this->height;
			}

			// return
			return $field;
		}

		/**
		 * Attempts to fetch the HTML for the provided URL using oEmbed.
		 *
		 * @date    24/01/2014
		 * @since   5.0.0
		 *
		 * @param string         $url    The URL that should be embedded.
		 * @param integer|string $width  Optional maxwidth value passed to the provider URL.
		 * @param integer|string $height Optional maxheight value passed to the provider URL.
		 * @return string|false The embedded HTML on success, false on failure.
		 */
		function wp_oembed_get( $url = '', $width = 0, $height = 0 ) {
			$embed = false;
			$res   = array(
				'width'  => $width,
				'height' => $height,
			);

			if ( function_exists( 'wp_oembed_get' ) ) {
				$embed = wp_oembed_get( $url, $res );
			}

			// try shortcode
			if ( ! $embed ) {
				global $wp_embed;
				$embed = $wp_embed->shortcode( $res, $url );
			}

			return $embed;
		}

		/**
		 * Returns AJAX results for the oEmbed field.
		 *
		 * @since 5.0.0
		 *
		 * @return void
		 */
		public function ajax_query() {
			$args = acf_request_args(
				array(
					'nonce'     => '',
					'field_key' => '',
				)
			);

			if ( ! acf_verify_ajax( $args['nonce'], $args['field_key'], true ) ) {
				die();
			}

			wp_send_json( $this->get_ajax_query( $_POST ) );
		}

		/**
		 * This function will return an array of data formatted for use in a select2 AJAX response
		 *
		 * @type    function
		 * @date    15/10/2014
		 * @since   5.0.9
		 *
		 * @param   $options (array)
		 * @return  (array)
		 */
		function get_ajax_query( $args = array() ) {

			// defaults
			$args = acf_parse_args(
				$args,
				array(
					's'         => '',
					'field_key' => '',
				)
			);

			// load field
			$field = acf_get_field( $args['field_key'] );
			if ( ! $field ) {
				return false;
			}

			// prepare field to correct width and height
			$field = $this->prepare_field( $field );

			// vars
			$response = array(
				'url'  => $args['s'],
				'html' => $this->wp_oembed_get( $args['s'], $field['width'], $field['height'] ),
			);

			// return
			return $response;
		}


		/**
		 * Renders the oEmbed field.
		 *
		 * @since 3.6
		 *
		 * @param array $field The field settings array.
		 * @return void
		 */
		public function render_field( $field ) {
			$atts = array(
				'class'      => 'acf-oembed',
				'data-nonce' => wp_create_nonce( 'acf_field_' . $this->name . '_' . $field['key'] ),
			);

			if ( $field['value'] ) {
				$atts['class'] .= ' has-value';
			}

			?>
<div <?php echo acf_esc_attrs( $atts ); ?>>
	
			<?php
			acf_hidden_input(
				array(
					'class' => 'input-value',
					'name'  => $field['name'],
					'value' => $field['value'],
				)
			);
			?>
	
	<div class="title">
			<?php
			acf_text_input(
				array(
					'class'        => 'input-search',
					'value'        => $field['value'],
					'placeholder'  => __( 'Enter URL', 'acf' ),
					'autocomplete' => 'off',
				)
			);
			?>
		<div class="acf-actions -hover">
			<a data-name="clear-button" href="#" class="acf-icon -cancel grey"></a>
		</div>
	</div>
	
	<div class="canvas">
		<div class="canvas-media">
			<?php
			if ( $field['value'] ) {
				echo $this->wp_oembed_get( $field['value'], $field['width'], $field['height'] ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_ombed_get generates HTML safe output.
			}
			?>
		</div>
		<i class="acf-icon -picture hide-if-value"></i>
	</div>
	
</div>
			<?php
		}


		/**
		 * Create extra options for your field. This is rendered when editing a field.
		 * The value of $field['name'] can be used (like bellow) to save extra data to the $field
		 *
		 * @param   $field  - an array holding all the field's data
		 *
		 * @type    action
		 * @since   3.6
		 * @date    23/01/13
		 */
		function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Embed Size', 'acf' ),
					'type'        => 'text',
					'name'        => 'width',
					'prepend'     => __( 'Width', 'acf' ),
					'append'      => 'px',
					'placeholder' => $this->width,
				)
			);

			acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Embed Size', 'acf' ),
					'type'        => 'text',
					'name'        => 'height',
					'prepend'     => __( 'Height', 'acf' ),
					'append'      => 'px',
					'placeholder' => $this->height,
					'_append'     => 'width',
				)
			);
		}

		/**
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template.
		 *
		 * @type    filter
		 * @since   3.6
		 *
		 * @param  mixed $value   The value which was loaded from the database.
		 * @param  mixed $post_id The $post_id from which the value was loaded.
		 * @param  array $field   The field array holding all the field options.
		 * @return mixed the modified value
		 */
		public function format_value( $value, $post_id, $field ) {
			// bail early if no value
			if ( empty( $value ) ) {
				return $value;
			}

			// prepare field to correct width and height
			$field = $this->prepare_field( $field );

			// get oembed
			$value = $this->wp_oembed_get( $value, $field['width'], $field['height'] );

			// return
			return $value;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			$schema           = parent::get_rest_schema( $field );
			$schema['format'] = 'uri';

			return $schema;
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_oembed' );
endif; // class_exists check

?>
