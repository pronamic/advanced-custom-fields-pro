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

if ( ! class_exists( 'acf_field_link' ) ) :

	class acf_field_link extends acf_field {


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
			$this->name          = 'link';
			$this->label         = __( 'Link', 'acf' );
			$this->category      = 'relational';
			$this->description   = __( 'Allows you to specify a link and its properties such as title and target using the WordPress native link picker.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-link.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/link/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'return_format' => 'array',
			);
		}


		/**
		 * description
		 *
		 * @type    function
		 * @date    16/5/17
		 * @since   5.5.13
		 *
		 * @param   $post_id (int)
		 * @return  $post_id (int)
		 */
		function get_link( $value = '' ) {

			// vars
			$link = array(
				'title'  => '',
				'url'    => '',
				'target' => '',
			);

			// array (ACF 5.6.0)
			if ( is_array( $value ) ) {
				$link = array_merge( $link, $value );

				// post id (ACF < 5.6.0)
			} elseif ( is_numeric( $value ) ) {
				$link['title'] = get_the_title( $value );
				$link['url']   = get_permalink( $value );

				// string (ACF < 5.6.0)
			} elseif ( is_string( $value ) ) {
				$link['url'] = $value;
			}

			// return
			return $link;
		}


		/**
		 * Create the HTML interface for your field
		 *
		 * @param   $field - an array holding all the field's data
		 *
		 * @type    action
		 * @since   3.6
		 */
		public function render_field( $field ) {

			// vars
			$div = array(
				'id'    => $field['id'],
				'class' => $field['class'] . ' acf-link',
			);

			// render scripts/styles
			acf_enqueue_uploader();

			// get link
			$link = $this->get_link( $field['value'] );

			// classes
			if ( $link['url'] ) {
				$div['class'] .= ' -value';
			}

			if ( $link['target'] === '_blank' ) {
				$div['class'] .= ' -external';
			}
			?>
<div <?php echo acf_esc_attrs( $div ); ?>>
	
	<div class="acf-hidden">
		<a class="link-node" href="<?php echo esc_url( $link['url'] ); ?>" target="<?php echo esc_attr( $link['target'] ); ?>"><?php echo esc_html( $link['title'] ); ?></a>
			<?php foreach ( $link as $k => $v ) : ?>
				<?php
				acf_hidden_input(
					array(
						'class' => "input-$k",
						'name'  => $field['name'] . "[$k]",
						'value' => $v,
					)
				);
				?>
		<?php endforeach; ?>
	</div>
	
	<a href="#" class="button" data-name="add" target=""><?php esc_html_e( 'Select Link', 'acf' ); ?></a>
	
	<div class="link-wrap">
		<span class="link-title"><?php echo esc_html( $link['title'] ); ?></span>
		<a class="link-url" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank"><?php echo esc_html( $link['url'] ); ?></a>
		<i class="acf-icon -link-ext acf-js-tooltip" title="<?php esc_attr_e( 'Opens in a new window/tab', 'acf' ); ?>"></i><a class="acf-icon -pencil -clear acf-js-tooltip" data-name="edit" href="#" title="<?php esc_attr_e( 'Edit', 'acf' ); ?>"></a><a class="acf-icon -cancel -clear acf-js-tooltip" data-name="remove" href="#" title="<?php esc_attr_e( 'Remove', 'acf' ); ?>"></a>
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
					'label'        => __( 'Return Value', 'acf' ),
					'instructions' => __( 'Specify the returned value on front end', 'acf' ),
					'type'         => 'radio',
					'name'         => 'return_format',
					'layout'       => 'horizontal',
					'choices'      => array(
						'array' => __( 'Link Array', 'acf' ),
						'url'   => __( 'Link URL', 'acf' ),
					),
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

			// bail early if no value
			if ( empty( $value ) ) {
				return $value;
			}

			// get link
			$link = $this->get_link( $value );

			// format value
			if ( $field['return_format'] == 'url' ) {
				return $link['url'];
			}

			// return link
			return $link;
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

			// bail early if not required
			if ( ! $field['required'] ) {
				return $valid;
			}

			// URL is required
			if ( empty( $value ) || empty( $value['url'] ) ) {
				return false;
			}

			// return
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

			// Check if value is an empty array and convert to empty string.
			if ( empty( $value ) || empty( $value['url'] ) ) {
				$value = '';
			}

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
			return array(
				'type'       => array( 'object', 'null' ),
				'required'   => ! empty( $field['required'] ),
				'properties' => array(
					'title'  => array(
						'type' => 'string',
					),
					'url'    => array(
						'type'     => 'string',
						'required' => true,
						'format'   => 'uri',
					),
					'target' => array(
						'type' => 'string',
					),
				),
			);
		}
	}


	// initialize
	acf_register_field_type( 'acf_field_link' );
endif; // class_exists check

?>
