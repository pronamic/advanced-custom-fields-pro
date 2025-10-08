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

namespace ACF\Pro\Fields\FlexibleContent;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Render {

	/**
	 * The main field array used to render the Flexible Content field.
	 *
	 * @var array
	 */
	private $field;

	/**
	 * An array of layouts used by the Flexible Content field.
	 *
	 * @var array
	 */
	private $layouts;

	/**
	 * An array of meta for the layouts being rendered.
	 *
	 * @var array
	 */
	private $layout_meta;

	/**
	 * Constructs the class.
	 *
	 * @since 6.5
	 *
	 * @param array $field       The flexible content field being rendered.
	 * @param array $layout_meta An array of meta for the layouts being rendered.
	 * @return void
	 */
	public function __construct( $field, $layout_meta ) {
		$this->field       = $field;
		$this->layout_meta = $layout_meta;
		$this->setup();
	}

	/**
	 * Prepares the field for rendering.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function setup() {
		$layouts = array();

		if ( ! empty( $this->field['layouts'] ) ) {
			foreach ( $this->field['layouts'] as $layout ) {
				$layouts[ $layout['name'] ] = $layout;
			}
		}

		$this->layouts = $layouts;
	}

	/**
	 * Renders the Flexible Content field.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	public function render() {
		$div_attrs = array(
			'class'             => 'acf-flexible-content',
			'data-min'          => $this->field['min'],
			'data-max'          => $this->field['max'],
			'data-button-label' => $this->field['button_label'],
		);

		if ( empty( $this->field['value'] ) ) {
			$div_attrs['class'] .= ' -empty';
		}

		echo '<div ' . acf_esc_attrs( $div_attrs ) . '>'; // Main wrapper div.

		acf_hidden_input( array( 'name' => $this->field['name'] ) );

		$this->actions( 'top' );
		$this->no_value_message();
		$this->clones();
		$this->layouts();
		$this->actions( 'bottom' );
		$this->add_layout_menu();
		$this->more_layout_actions();

		echo '</div>'; // End main wrapper div.
	}

	/**
	 * Renders the no value message.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function no_value_message() {
		// translators: %s the button label used for adding a new layout.
		$no_value_message = __( 'Click the "%s" button below to start creating your layout', 'acf' );
		$no_value_message = apply_filters( 'acf/fields/flexible_content/no_value_message', $no_value_message, $this->field );
		$no_value_message = sprintf( $no_value_message, $this->field['button_label'] );

		echo '<div class="no-value-message">' . acf_esc_html( $no_value_message ) . '</div>';
	}

	/**
	 * Renders the ACF clone indexes for the layouts.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function clones() {
		echo '<div class="clones">';

		if ( ! empty( $this->layouts ) ) {
			foreach ( $this->layouts as $layout ) {
				$clone = new Layout( $this->field, $layout, 'acfcloneindex', array() );
				$clone->render();
			}
		}

		echo '</div>';
	}

	/**
	 * Renders the layouts for a Flexible Content field.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function layouts() {
		echo '<div class="values">';

		$disabled_layouts = ! empty( $this->layout_meta['disabled'] ) ? $this->layout_meta['disabled'] : array();
		$renamed_layouts  = ! empty( $this->layout_meta['renamed'] ) ? $this->layout_meta['renamed'] : array();

		if ( ! empty( $this->field['value'] ) ) {
			foreach ( $this->field['value'] as $order => $value ) {
				if ( ! is_array( $value ) ) {
					continue;
				}

				if ( empty( $this->layouts[ $value['acf_fc_layout'] ] ) ) {
					continue;
				}

				$layout = new Layout(
					$this->field,
					$this->layouts[ $value['acf_fc_layout'] ],
					$order,
					$value,
					in_array( $order, $disabled_layouts, true ),
					! empty( $renamed_layouts[ $order ] ) ? $renamed_layouts[ $order ] : '',
				);

				$layout->render();
			}
		}

		echo '</div>';
	}

	/**
	 * Renders top-level actions for the Flexible Content field.
	 *
	 * @since 6.5
	 *
	 * @param string $which The location of the actions, either 'top' or 'bottom'.
	 * @return void
	 */
	private function actions( string $which = '' ) {
		if ( 'top' === $which ) {
			?>
			<div class="acf-actions acf-fc-top-actions">
				<button class="acf-btn acf-btn-clear acf-fc-expand-all">
					<?php esc_html_e( 'Expand All', 'acf' ); ?>
				</button>
				<button class="acf-btn acf-btn-clear acf-fc-collapse-all">
					<?php esc_html_e( 'Collapse All', 'acf' ); ?>
				</button>
				<span class="acf-separator"></span>
				<a class="acf-button button button-primary" href="#" data-name="add-layout" data-context="top-actions">
					<i class="acf-icon -plus small"></i>
					<?php echo acf_esc_html( $this->field['button_label'] ); ?>
				</a>
			</div>
			<?php
		} else {
			?>
			<div class="acf-actions">
				<a class="acf-button button button-primary" href="#" data-name="add-layout" data-context="bottom-actions">
					<i class="acf-icon -plus small"></i>
					<?php echo acf_esc_html( $this->field['button_label'] ); ?>
				</a>
			</div>
			<?php
		}
	}

	/**
	 * Renders the dropdown menu to add more layouts.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function add_layout_menu() {
		echo '<script type="text-html" class="tmpl-popup"><ul>';
		foreach ( $this->layouts as $layout ) {
			$safe_label = acf_esc_html( $layout['label'] );
			$atts       = array(
				'href'        => '#',
				'data-layout' => $layout['name'],
				'data-min'    => $layout['min'],
				'data-max'    => $layout['max'],
				'title'       => $safe_label,
			);
			printf( '<li><a %s>%s</a></li>', acf_esc_attrs( $atts ), $safe_label ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		}
		echo '</ul></script>';
	}

	/**
	 * Renders the dropdown menu for additional layout actions.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private function more_layout_actions() {
		?>
		<script type="text-html" class="tmpl-more-layout-actions">
			<ul role="menu" tabindex="-1">
				<li>
					<a class="acf-rename-layout" data-action="rename-layout" href="#" role="menuitem">
						<?php esc_html_e( 'Rename', 'acf' ); ?>
					</a>
				</li>
				<li>
					<a class="acf-toggle-layout disable" data-action="toggle-layout" href="#" role="menuitem">
						<?php esc_html_e( 'Disable', 'acf' ); ?>
					</a>
					<a class="acf-toggle-layout enable" data-action="toggle-layout" href="#" role="menuitem">
						<?php esc_html_e( 'Enable', 'acf' ); ?>
					</a>
				</li>
			</ul>
		</script>
		<?php
	}
}
