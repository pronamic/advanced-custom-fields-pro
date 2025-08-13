<?php
/**
 * A helper class for rendering an individual Flexible Content Layout.
 *
 * @package ACF
 */

namespace ACF\Pro\Fields\FlexibleContent;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Layout {

	/**
	 * The Flexible Content field the layout belongs to.
	 *
	 * @var array
	 */
	private $field;

	/**
	 * The layout being rendered.
	 *
	 * @var array
	 */
	private $layout;

	/**
	 * The order of the layout.
	 *
	 * @var integer|string
	 */
	private $order;

	/**
	 * The value of the layout.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * The input prefix.
	 *
	 * @var string
	 */
	private $prefix;

	/**
	 * If the layout is disabled.
	 *
	 * @var boolean
	 */
	private $disabled;

	/**
	 * If the layout has been renamed, the new name of the layout.
	 *
	 * @var string
	 */
	private $renamed;

	/**
	 * Constructs the class.
	 *
	 * @since 6.5
	 *
	 * @param array          $field    The Flexible Content field the layout belongs to.
	 * @param array          $layout   The layout to render.
	 * @param integer|string $order    The order of the layout.
	 * @param mixed          $value    The value of the layout.
	 * @param boolean        $disabled If the layout is disabled.
	 * @param string         $renamed  If the layout has been renamed, the new name of the layout.
	 */
	public function __construct( $field, $layout, $order, $value, $disabled = false, $renamed = '' ) {
		$this->field    = $field;
		$this->layout   = $layout;
		$this->order    = $order;
		$this->value    = $value;
		$this->disabled = $disabled;
		$this->renamed  = $renamed;
	}

	/**
	 * Renders the layout.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	public function render() {
		$id    = 'row-' . $this->order;
		$class = 'layout';

		if ( 'acfcloneindex' === $this->order ) {
			$id     = 'acfcloneindex';
			$class .= ' acf-clone';
		}

		$this->prefix = $this->field['name'] . '[' . $id . ']';

		$div_attrs = array(
			'class'        => $class,
			'data-id'      => $id,
			'data-layout'  => $this->layout['name'],
			'data-label'   => $this->layout['label'],
			'data-min'     => $this->layout['min'],
			'data-max'     => $this->layout['max'],
			'data-enabled' => $this->disabled ? 0 : 1,
			'data-renamed' => empty( $this->renamed ) ? 0 : 1,
		);

		echo '<div ' . acf_esc_attrs( $div_attrs ) . '>'; // Layout wrapper div.

		acf_hidden_input(
			array(
				'name'  => $this->prefix . '[acf_fc_layout]',
				'value' => $this->layout['name'],
			)
		);

		acf_hidden_input(
			array(
				'class' => 'acf-fc-layout-disabled',
				'name'  => $this->prefix . '[acf_fc_layout_disabled]',
				'value' => $this->disabled ? 1 : 0,
			)
		);

		acf_hidden_input(
			array(
				'class' => 'acf-fc-layout-custom-label',
				'name'  => $this->prefix . '[acf_fc_layout_custom_label]',
				'value' => $this->renamed,
			)
		);

		$this->action_buttons();

		if ( ! empty( $this->layout['sub_fields'] ) ) {
			if ( 'table' === $this->layout['display'] ) {
				$this->render_as_table();
			} else {
				$this->render_as_div();
			}
		}

		echo '</div>'; // End layout wrapper div.
	}

	/**
	 * Renders a layout as a table.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function render_as_table() {
		$sub_fields = $this->layout['sub_fields'];
		?>
		<table class="acf-table">
			<thead>
				<tr>
					<?php
					foreach ( $sub_fields as $sub_field ) {
						// Set prefix to generate correct "for" attribute on <label>.
						$sub_field['prefix'] = $this->prefix;

						// Prepare field (allow sub fields to be removed).
						$sub_field = acf_prepare_field( $sub_field );
						if ( ! $sub_field ) {
							continue;
						}

						$th_attrs = array(
							'class'     => 'acf-th',
							'data-name' => $sub_field['_name'],
							'data-type' => $sub_field['type'],
							'data-key'  => $sub_field['key'],
						);

						if ( $sub_field['wrapper']['width'] ) {
							$th_attrs['data-width'] = $sub_field['wrapper']['width'];
							$th_attrs['style']      = 'width: ' . $sub_field['wrapper']['width'] . '%;';
						}

						echo '<th ' . acf_esc_attrs( $th_attrs ) . '>';
						acf_render_field_label( $sub_field );
						acf_render_field_instructions( $sub_field );
						echo '</th>';
					}
					?>
				</tr>
			</thead>
			<tbody>
				<tr><?php $this->sub_fields(); ?></tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Renders a layout as a div.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function render_as_div() {
		$class = 'acf-fields';

		if ( 'row' === $this->layout['display'] ) {
			$class .= ' -left';
		}

		echo '<div class="' . esc_attr( $class ) . '">';
		$this->sub_fields();
		echo '</div>';
	}

	/**
	 * Renders the layout actions (Add, Duplicate, Rename).
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function action_buttons() {
		$title = $this->get_title();
		$order = is_numeric( $this->order ) ? $this->order + 1 : 0;
		?>
		<div class="acf-fc-layout-actions-wrap">
			<div class="acf-fc-layout-handle" title="<?php esc_attr_e( 'Drag to reorder', 'acf' ); ?>" data-name="collapse-layout">
				<span class="acf-fc-layout-order"><?php echo (int) $order; ?></span>
				<span class="acf-fc-layout-draggable-icon"></span>
				<span class="acf-fc-layout-title">
					<?php echo ! empty( $this->renamed ) ? esc_html( $this->renamed ) : $title; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped earlier in function. ?>
				</span>
				<span class="acf-fc-layout-original-title">
					(<?php echo $title; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped earlier in function. ?>)
				</span>
				<span class="acf-layout-disabled"><?php esc_html_e( 'Disabled', 'acf' ); ?></span>
			</div>
			<div class="acf-fc-layout-controls">
				<a class="acf-js-tooltip" href="#" data-name="add-layout" data-context="layout" title="<?php esc_attr_e( 'Add layout', 'acf' ); ?>"><span class="acf-icon -plus-alt "></span></a>
				<a class="acf-js-tooltip" href="#" data-name="duplicate-layout" title="<?php esc_attr_e( 'Duplicate layout', 'acf' ); ?>"><span class="acf-icon -duplicate-alt"></span></a>
				<a class="acf-js-tooltip" aria-haspopup="menu" href="#" data-name="more-layout-actions" title="<?php esc_attr_e( 'More layout actions...', 'acf' ); ?>"><span class="acf-icon -more-actions"></span></a>
				<div class="acf-layout-collapse">
					<a class="acf-icon -collapse -clear" href="#" data-name="collapse-layout" aria-label="<?php esc_attr_e( 'Toggle layout', 'acf' ); ?>"></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the subfields for a layout.
	 *
	 * @since 6.5
	 * @return void
	 */
	private function sub_fields() {
		foreach ( $this->layout['sub_fields'] as $sub_field ) {

			// add value
			if ( isset( $this->value[ $sub_field['key'] ] ) ) {

				// this is a normal value
				$sub_field['value'] = $this->value[ $sub_field['key'] ];
			} elseif ( isset( $sub_field['default_value'] ) ) {

				// no value, but this sub field has a default value
				$sub_field['value'] = $sub_field['default_value'];
			}

			// update prefix to allow for nested values
			$sub_field['prefix'] = $this->prefix;

			// Render the input.
			$el = 'table' === $this->layout['display'] ? 'td' : 'div';
			acf_render_field_wrap( $sub_field, $el );
		}
	}

	/**
	 * Returns the filtered layout title.
	 *
	 * @since 6.5
	 *
	 * @return string
	 */
	public function get_title() {
		$rows                 = array();
		$rows[ $this->order ] = $this->value;

		acf_add_loop(
			array(
				'selector' => $this->field['name'],
				'name'     => $this->field['name'],
				'value'    => $rows,
				'field'    => $this->field,
				'i'        => $this->order,
				'post_id'  => 0,
			)
		);

		// Make the title filterable.
		$title = esc_html( $this->layout['label'] );
		$title = apply_filters( 'acf/fields/flexible_content/layout_title', $title, $this->field, $this->layout, $this->order );
		$title = apply_filters( 'acf/fields/flexible_content/layout_title/name=' . $this->field['_name'], $title, $this->field, $this->layout, $this->order );
		$title = apply_filters( 'acf/fields/flexible_content/layout_title/key=' . $this->field['key'], $title, $this->field, $this->layout, $this->order );
		$title = acf_esc_html( $title );

		acf_remove_loop();
		reset_rows(); // TODO: Make sure this is actually where this should go if needed at all.

		return $title;
	}
}
