<?php
/**
 * @package ACF
 * @author  WP Engine
 *
 * © 2026 Advanced Custom Fields (ACF®). All rights reserved.
 * "ACF" is a trademark of WP Engine.
 * Licensed under the GNU General Public License v2 or later.
 * https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace ACF\Pro\Datastore;

/**
 * Enqueues the ACF datastore script and localizes field group definitions
 * and values for the @wordpress/data store consumed by the JS datastore.
 *
 * Independently listens to the same enqueue_block_editor_assets WP action
 * the free ACF_Form_Gutenberg uses, so no free-side touchpoint is required.
 */
class Localization {

	/**
	 * Constructor.
	 *
	 * @since 6.8.1
	 */
	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
		add_filter( 'acf/ajax/query_users/args', array( $this, 'add_user_query_include' ), 20, 3 );
	}

	/**
	 * Allows the JS datastore to look up specific users by ID via the user
	 * query endpoint, so revision restores and programmatic acf.store.set()
	 * calls can render user labels for values not in the page-rendered options.
	 *
	 * @since 6.8.1
	 *
	 * @param array           $args    The query args.
	 * @param array           $request The query request.
	 * @param \ACF_Ajax_Query $query   The query object.
	 * @return array
	 */
	public function add_user_query_include( $args, $request, $query ) {
		if ( ! acf_is_using_datastore() ) {
			return $args;
		}
		if ( empty( $request['include'] ) ) {
			return $args;
		}
		$args['include'] = array( absint( $request['include'] ) );
		return $args;
	}

	/**
	 * Enqueues the datastore script and localizes the field store data
	 * when the datastore is enabled.
	 *
	 * @since 6.8.1
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! acf_is_using_datastore() ) {
			return;
		}

		wp_enqueue_script( 'acf-datastore' );
		$this->localize_field_store_data();
	}

	/**
	 * Localizes field group definitions and values for the ACF @wordpress/data store.
	 *
	 * Gathers all field groups visible on the current post, serializes their
	 * field definitions and current values, and passes them to JS via
	 * acf_localize_data(). This data initializes the 'acf/fields' store
	 * which powers JS-side block bindings and developer access.
	 *
	 * @since 6.8.1
	 *
	 * @return void
	 */
	private function localize_field_store_data() {
		global $post;

		if ( ! $post ) {
			return;
		}

		$field_groups = acf_get_field_groups( array( 'post_id' => $post->ID ) );

		if ( empty( $field_groups ) ) {
			return;
		}

		$store_data = array(
			'context'     => array(
				'postId'   => $post->ID,
				'postType' => $post->post_type,
			),
			'fields'      => array(),
			'values'      => array(),
			'fieldGroups' => array(),
		);

		foreach ( $field_groups as $field_group ) {
			$store_data['fieldGroups'][] = array(
				'key'                   => $field_group['key'],
				'title'                 => acf_esc_html( acf_get_field_group_title( $field_group ) ),
				'position'              => $field_group['position'],
				'style'                 => $field_group['style'],
				'label_placement'       => $field_group['label_placement'],
				'instruction_placement' => $field_group['instruction_placement'],
				'edit_url'              => esc_url( acf_get_field_group_edit_link( $field_group['ID'] ) ),
			);

			$fields = acf_get_fields( $field_group );
			if ( $fields ) {
				$this->collect_field_data( $fields, $post->ID, $field_group['key'], $store_data );
			}
		}

		acf_localize_data( array( 'storeData' => $store_data ) );
	}

	/**
	 * Recursively collects field definitions and values for the store.
	 *
	 * Processes an array of fields, loading each field's value and adding
	 * it to the store data structure. For complex fields (repeater, group,
	 * flexible content), recurses into sub-fields to build nested values.
	 *
	 * @since 6.8.1
	 *
	 * @param array   $fields          Array of field arrays.
	 * @param integer $post_id         The post ID to load values for.
	 * @param string  $field_group_key The parent field group's key.
	 * @param array   $store_data      Reference to the store data being built.
	 * @return void
	 */
	public function collect_field_data( $fields, $post_id, $field_group_key, &$store_data ) {
		foreach ( $fields as $field ) {
			$field = apply_filters( 'acf/prepare_field', $field );
			if ( ! $field ) {
				continue;
			}

			$field_def = array(
				'key'           => $field['key'],
				'name'          => $field['name'],
				'type'          => $field['type'],
				'label'         => acf_esc_html( $field['label'] ),
				'parent'        => $field['parent'],
				'fieldGroupKey' => $field_group_key,
			);

			if ( ! acf_field_type_supports( $field['type'], 'bindings', true ) ) {
				$field_def['supportsBindings'] = false;
			}

			if ( isset( $field['allow_in_bindings'] ) ) {
				$field_def['allowInBindings'] = (bool) $field['allow_in_bindings'];
			}

			// Repeaters with pagination enabled need unique handling in the datastore.
			if ( 'repeater' === $field['type'] ) {
				$field_def['pagination'] = ! empty( $field['pagination'] );
			}

			// Include sub_fields metadata for complex types.
			if ( ! empty( $field['sub_fields'] ) ) {
				$field_def['subFields'] = array();
				foreach ( $field['sub_fields'] as $sub_field ) {
					if ( apply_filters( 'acf/prepare_field', $sub_field ) ) {
						$field_def['subFields'][] = $sub_field['key'];
					}
				}
			}

			// Include layouts for flexible content.
			if ( ! empty( $field['layouts'] ) ) {
				$field_def['layouts'] = array();
				foreach ( $field['layouts'] as $layout ) {
					$layout_def = array(
						'key'   => $layout['key'],
						'name'  => $layout['name'],
						'label' => acf_esc_html( $layout['label'] ),
					);
					if ( ! empty( $layout['sub_fields'] ) ) {
						$layout_def['subFields'] = array();
						foreach ( $layout['sub_fields'] as $sub_field ) {
							if ( apply_filters( 'acf/prepare_field', $sub_field ) ) {
								$layout_def['subFields'][] = $sub_field['key'];
							}
						}
					}
					$field_def['layouts'][] = $layout_def;
				}
			}

			$store_data['fields'][ $field['key'] ] = $field_def;

			// Load the field value.
			//
			// Skip paginated repeaters: the value would be discarded by
			// reconcileWithDOM in JS (visible-page DOM rows overwrite it
			// on init), and acf_get_value() would cache the unsliced row
			// set here -- load_value()'s pagination slice is gated on
			// $is_rendering, which isn't set until pre_render_fields fires
			// for the metabox. The cached unsliced value would then be
			// reused by the render and show all rows on page 1.
			if ( 'repeater' === $field['type'] && ! empty( $field['pagination'] ) ) {
				$store_data['values'][ $field['key'] ] = array();
			} else {
				$value                                 = acf_get_value( $post_id, $field );
				$store_data['values'][ $field['key'] ] = $this->serialize_field_value( $field, $value, $post_id );
			}

			// Register sub-fields in the store for complex types.
			if ( ! empty( $field['sub_fields'] ) ) {
				$this->register_sub_fields( $field['sub_fields'], $field_group_key, $store_data );
			}

			// Register layout sub-fields for flexible content.
			if ( ! empty( $field['layouts'] ) ) {
				foreach ( $field['layouts'] as $layout ) {
					if ( ! empty( $layout['sub_fields'] ) ) {
						$this->register_sub_fields( $layout['sub_fields'], $field_group_key, $store_data );
					}
				}
			}
		}
	}

	/**
	 * Registers sub-field definitions in the store (without loading values).
	 *
	 * Sub-field values are stored as part of their parent's value structure,
	 * but their definitions need to be in the store for metadata access.
	 *
	 * @since 6.8.1
	 *
	 * @param array  $sub_fields      Array of sub-field arrays.
	 * @param string $field_group_key The parent field group's key.
	 * @param array  $store_data      Reference to the store data being built.
	 * @return void
	 */
	public function register_sub_fields( $sub_fields, $field_group_key, &$store_data ) {
		foreach ( $sub_fields as $sub_field ) {
			$sub_field = apply_filters( 'acf/prepare_field', $sub_field );
			if ( ! $sub_field ) {
				continue;
			}

			// Bindings::get_value can't resolve sub-field keys at the
			// post level (no row index for repeater/flex; no parent-chain
			// prefix walk for group/clone), so the picker must exclude them.
			$field_def = array(
				'key'              => $sub_field['key'],
				'name'             => $sub_field['name'],
				'type'             => $sub_field['type'],
				'label'            => acf_esc_html( $sub_field['label'] ),
				'parent'           => $sub_field['parent'],
				'fieldGroupKey'    => $field_group_key,
				'supportsBindings' => false,
			);

			if ( ! empty( $sub_field['sub_fields'] ) ) {
				$field_def['subFields'] = array();
				foreach ( $sub_field['sub_fields'] as $nested ) {
					if ( apply_filters( 'acf/prepare_field', $nested ) ) {
						$field_def['subFields'][] = $nested['key'];
					}
				}
				// Recurse for nested complex fields.
				$this->register_sub_fields( $sub_field['sub_fields'], $field_group_key, $store_data );
			}

			if ( ! empty( $sub_field['layouts'] ) ) {
				$field_def['layouts'] = array();
				foreach ( $sub_field['layouts'] as $layout ) {
					$layout_def = array(
						'key'   => $layout['key'],
						'name'  => $layout['name'],
						'label' => acf_esc_html( $layout['label'] ),
					);
					if ( ! empty( $layout['sub_fields'] ) ) {
						$layout_def['subFields'] = array();
						foreach ( $layout['sub_fields'] as $nested ) {
							if ( apply_filters( 'acf/prepare_field', $nested ) ) {
								$layout_def['subFields'][] = $nested['key'];
							}
						}
						$this->register_sub_fields( $layout['sub_fields'], $field_group_key, $store_data );
					}
					$field_def['layouts'][] = $layout_def;
				}
			}

			$store_data['fields'][ $sub_field['key'] ] = $field_def;
		}
	}

	/**
	 * Serializes a field value into the structure expected by the JS store.
	 *
	 * For simple fields, returns the raw value. For complex fields (repeater,
	 * group, flexible content), builds a nested structure matching the ACF
	 * REST API format.
	 *
	 * @since 6.8.1
	 *
	 * @param array   $field   The field array.
	 * @param mixed   $value   The raw field value.
	 * @param integer $post_id The post ID.
	 * @return mixed The serialized value.
	 */
	public function serialize_field_value( $field, $value, $post_id ) {
		switch ( $field['type'] ) {
			case 'repeater':
				return $this->serialize_repeater_value( $field, $value, $post_id );

			case 'group':
				return $this->serialize_group_value( $field, $value, $post_id );

			case 'flexible_content':
				return $this->serialize_flexible_content_value( $field, $value, $post_id );

			case 'wysiwyg':
				return is_string( $value ) ? acf_esc_html( $value ) : $value;

			default:
				return $value;
		}
	}

	/**
	 * Serializes a repeater field value.
	 *
	 * acf_get_value() returns the loaded value from the repeater's load_value()
	 * method -- an array of rows keyed by sub-field key, not the raw row count
	 * stored in the database.
	 *
	 * @since 6.8.1
	 *
	 * @param array   $field   The repeater field array.
	 * @param mixed   $value   The loaded value (array of rows from load_value).
	 * @param integer $post_id The post ID.
	 * @return array Array of row objects.
	 */
	public function serialize_repeater_value( $field, $value, $post_id ) {
		if ( empty( $field['sub_fields'] ) || ! is_array( $value ) ) {
			return array();
		}

		$result = array();

		foreach ( $value as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$serialized_row = array();

			foreach ( $field['sub_fields'] as $sub_field ) {
				$sub_value                           = isset( $row[ $sub_field['key'] ] ) ? $row[ $sub_field['key'] ] : null;
				$serialized_row[ $sub_field['key'] ] = $this->serialize_field_value( $sub_field, $sub_value, $post_id );
			}

			$result[] = $serialized_row;
		}

		return $result;
	}

	/**
	 * Serializes a group field value.
	 *
	 * When called from a parent complex field (repeater, flex content), $value
	 * is the already-loaded array from load_value() keyed by sub-field key.
	 * For top-level groups, $value may also be a loaded array. Falls back to
	 * loading from the database when the loaded value is not available.
	 *
	 * @since 6.8.1
	 *
	 * @param array   $field   The group field array.
	 * @param mixed   $value   The loaded value (array of sub-field values, or raw).
	 * @param integer $post_id The post ID.
	 * @return array|\stdClass Associative array of sub-field values keyed by field key.
	 */
	public function serialize_group_value( $field, $value, $post_id ) {
		if ( empty( $field['sub_fields'] ) ) {
			return new \stdClass();
		}

		$result = array();

		foreach ( $field['sub_fields'] as $sub_field ) {
			if ( is_array( $value ) && array_key_exists( $sub_field['key'], $value ) ) {
				$sub_value = $value[ $sub_field['key'] ];
			} else {
				$sub_field['name'] = $field['name'] . '_' . $sub_field['name'];
				$sub_value         = acf_get_value( $post_id, $sub_field );
			}

			$result[ $sub_field['key'] ] = $this->serialize_field_value( $sub_field, $sub_value, $post_id );
		}

		return $result;
	}

	/**
	 * Serializes a flexible content field value.
	 *
	 * acf_get_value() returns the loaded value from the flex content's
	 * load_value() method -- an array of layout row objects, each containing
	 * an 'acf_fc_layout' key and sub-field values keyed by field key.
	 *
	 * @since 6.8.1
	 *
	 * @param array   $field   The flexible content field array.
	 * @param mixed   $value   The loaded value (array of layout rows from load_value).
	 * @param integer $post_id The post ID.
	 * @return array Array of layout objects.
	 */
	public function serialize_flexible_content_value( $field, $value, $post_id ) {
		if ( ! is_array( $value ) || empty( $field['layouts'] ) ) {
			return array();
		}

		// Build a lookup of layouts by name for sub-field definitions.
		$layouts_by_name = array();
		foreach ( $field['layouts'] as $layout ) {
			$layouts_by_name[ $layout['name'] ] = $layout;
		}

		$result = array();

		foreach ( $value as $row ) {
			if ( ! is_array( $row ) || empty( $row['acf_fc_layout'] ) ) {
				continue;
			}

			$layout_name = $row['acf_fc_layout'];

			if ( ! isset( $layouts_by_name[ $layout_name ] ) ) {
				continue;
			}

			$layout     = $layouts_by_name[ $layout_name ];
			$layout_row = array( 'acf_fc_layout' => $layout_name );

			if ( ! empty( $layout['sub_fields'] ) ) {
				foreach ( $layout['sub_fields'] as $sub_field ) {
					$sub_value                       = isset( $row[ $sub_field['key'] ] ) ? $row[ $sub_field['key'] ] : null;
					$layout_row[ $sub_field['key'] ] = $this->serialize_field_value( $sub_field, $sub_value, $post_id );
				}
			}

			$result[] = $layout_row;
		}

		return $result;
	}
}
