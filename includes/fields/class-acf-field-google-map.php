<?php

if ( ! class_exists( 'acf_field_google_map' ) ) :
	class acf_field_google_map extends acf_field {


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
			$this->name           = 'google_map';
			$this->label          = __( 'Google Map', 'acf' );
			$this->category       = 'advanced';
			$this->description    = __( 'An interactive UI for selecting a location using Google Maps. Requires a Google Maps API key and additional configuration to display correctly.', 'acf' );
			$this->preview_image  = acf_get_url() . '/assets/images/field-type-previews/field-preview-google-map.png';
			$this->doc_url        = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/google-map/', 'docs', 'field-type-selection' );
			$this->defaults       = array(
				'height'     => '',
				'center_lat' => '',
				'center_lng' => '',
				'zoom'       => '',
			);
			$this->default_values = array(
				'height'     => '400',
				'center_lat' => '-37.81411',
				'center_lng' => '144.96328',
				'zoom'       => '14',
			);
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
					'Sorry, this browser does not support geolocation'  => __( 'Sorry, this browser does not support geolocation', 'acf' ),
				)
			);

			// bail early if no enqueue
			if ( ! acf_get_setting( 'enqueue_google_maps' ) ) {
				return;
			}

			// vars
			$api = array(
				'key'       => acf_get_setting( 'google_api_key' ),
				'client'    => acf_get_setting( 'google_api_client' ),
				'libraries' => 'places',
				'ver'       => 3,
				'callback'  => 'Function.prototype',
				'language'  => acf_get_locale(),
			);

			// filter
			$api = apply_filters( 'acf/fields/google_map/api', $api );

			// remove empty
			if ( empty( $api['key'] ) ) {
				unset( $api['key'] );
			}
			if ( empty( $api['client'] ) ) {
				unset( $api['client'] );
			}

			// construct url
			$url = add_query_arg( $api, 'https://maps.googleapis.com/maps/api/js' );

			// localize
			acf_localize_data(
				array(
					'google_map_api' => $url,
				)
			);
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

			// Apply defaults.
			foreach ( $this->default_values as $k => $v ) {
				if ( ! $field[ $k ] ) {
					$field[ $k ] = $v;
				}
			}

			// Attrs.
			$attrs = array(
				'id'        => $field['id'],
				'class'     => "acf-google-map {$field['class']}",
				'data-lat'  => $field['center_lat'],
				'data-lng'  => $field['center_lng'],
				'data-zoom' => $field['zoom'],
			);

			$search = '';
			if ( $field['value'] ) {
				$attrs['class'] .= ' -value';
				$search          = $field['value']['address'];
			} else {
				$field['value'] = '';
			}

			?>
<div <?php echo acf_esc_attrs( $attrs ); ?>>

			<?php
			acf_hidden_input(
				array(
					'name'  => $field['name'],
					'value' => $field['value'],
				)
			);
			?>

	<div class="title">

		<div class="acf-actions -hover">
			<a href="#" data-name="search" class="acf-icon -search grey" title="<?php esc_attr_e( 'Search', 'acf' ); ?>"></a>
			<a href="#" data-name="clear" class="acf-icon -cancel grey" title="<?php esc_attr_e( 'Clear location', 'acf' ); ?>"></a>
			<a href="#" data-name="locate" class="acf-icon -location grey" title="<?php esc_attr_e( 'Find current location', 'acf' ); ?>"></a>
		</div>

		<input class="search" type="text" placeholder="<?php esc_attr_e( 'Search for address...', 'acf' ); ?>" value="<?php echo esc_attr( $search ); ?>" />
		<i class="acf-loading"></i>

	</div>

	<div class="canvas" style="<?php echo esc_attr( 'height: ' . $field['height'] . 'px' ); ?>"></div>

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

			// center_lat
			acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Center', 'acf' ),
					'hint'        => __( 'Center the initial map', 'acf' ),
					'type'        => 'text',
					'name'        => 'center_lat',
					'prepend'     => 'lat',
					'placeholder' => $this->default_values['center_lat'],
				)
			);

			// center_lng
			acf_render_field_setting(
				$field,
				array(
					'label'       => __( 'Center', 'acf' ),
					'hint'        => __( 'Center the initial map', 'acf' ),
					'type'        => 'text',
					'name'        => 'center_lng',
					'prepend'     => 'lng',
					'placeholder' => $this->default_values['center_lng'],
					'_append'     => 'center_lat',
				)
			);

			// zoom
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Zoom', 'acf' ),
					'instructions' => __( 'Set the initial zoom level', 'acf' ),
					'type'         => 'text',
					'name'         => 'zoom',
					'placeholder'  => $this->default_values['zoom'],
				)
			);

			// allow_null
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Height', 'acf' ),
					'instructions' => __( 'Customize the map height', 'acf' ),
					'type'         => 'text',
					'name'         => 'height',
					'append'       => 'px',
					'placeholder'  => $this->default_values['height'],
				)
			);
		}

		/**
		 * load_value
		 *
		 * Filters the value loaded from the database.
		 *
		 * @date    16/10/19
		 * @since   5.8.1
		 *
		 * @param   mixed $value   The value loaded from the database.
		 * @param   mixed $post_id The post ID where the value is saved.
		 * @param   array $field   The field settings array.
		 * @return  (array|false)
		 */
		function load_value( $value, $post_id, $field ) {

			// Ensure value is an array.
			if ( $value ) {
				return wp_parse_args(
					$value,
					array(
						'address' => '',
						'lat'     => 0,
						'lng'     => 0,
					)
				);
			}

			// Return default.
			return false;
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

			// decode JSON string.
			if ( is_string( $value ) ) {
				$value = json_decode( wp_unslash( $value ), true );
			}

			// Ensure value is an array.
			if ( $value ) {
				return (array) $value;
			}

			// Return default.
			return false;
		}

		/**
		 * Return the schema array for the REST API.
		 *
		 * @param array $field
		 * @return array
		 */
		public function get_rest_schema( array $field ) {
			return array(
				'type'       => array( 'object', 'null' ),
				'required'   => ! empty( $field['required'] ),
				'properties' => array(
					'address'           => array(
						'type' => 'string',
					),
					'lat'               => array(
						'type' => array( 'string', 'float' ),
					),
					'lng'               => array(
						'type' => array( 'string', 'float' ),
					),
					'zoom'              => array(
						'type' => array( 'string', 'int' ),
					),
					'place_id'          => array(
						'type' => 'string',
					),
					'name'              => array(
						'type' => 'string',
					),
					'street_number'     => array(
						'type' => array( 'string', 'int' ),
					),
					'street_name'       => array(
						'type' => 'string',
					),
					'street_name_short' => array(
						'type' => 'string',
					),
					'city'              => array(
						'type' => 'string',
					),
					'state'             => array(
						'type' => 'string',
					),
					'state_short'       => array(
						'type' => 'string',
					),
					'post_code'         => array(
						'type' => array( 'string', 'int' ),
					),
					'country'           => array(
						'type' => 'string',
					),
					'country_short'     => array(
						'type' => 'string',
					),
				),
			);
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
			if ( ! $value ) {
				return null;
			}

			return acf_format_numerics( $value );
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_google_map' );
endif; // class_exists check

?>
