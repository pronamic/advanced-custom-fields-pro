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

/**
 * ACF_Repeater_Table
 *
 * Helper class for rendering repeater tables.
 *
 */
class ACF_Repeater_Table {

	/**
	 * The main field array used to render the repeater.
	 *
	 * @var array
	 */
	private $field;

	/**
	 * An array containing the subfields used in the repeater.
	 *
	 * @var array
	 */
	private $sub_fields;

	/**
	 * The value(s) of the repeater field.
	 *
	 * @var array
	 */
	private $value;

	/**
	 * If we should show the "Add Row" button.
	 *
	 * @var boolean
	 */
	private $show_add = true;

	/**
	 * If we should show the "Remove Row" button.
	 *
	 * @var boolean
	 */
	private $show_remove = true;

	/**
	 * If we should show the order of the fields.
	 *
	 * @var boolean
	 */
	private $show_order = true;

	/**
	 * Constructs the ACF_Repeater_Table class.
	 *
	 * @param array $field The main field array for the repeater being rendered.
	 */
	public function __construct( $field ) {
		$this->field      = $field;
		$this->sub_fields = $field['sub_fields'];

		// Default to non-paginated repeaters.
		if ( empty( $this->field['pagination'] ) ) {
			$this->field['pagination'] = false;
		}

		// We don't yet support pagination inside other repeaters or flexible content fields.
		if ( ! empty( $this->field['parent_repeater'] ) || ! empty( $this->field['parent_layout'] ) ) {
			$this->field['pagination'] = false;
		}

		// We don't yet support pagination in frontend forms or inside blocks.
		if ( ! is_admin() || acf_get_data( 'acf_inside_rest_call' ) || doing_action( 'wp_ajax_acf/ajax/fetch-block' ) ) {
			$this->field['pagination'] = false;
		}

		$this->setup();
	}

	/**
	 * Sets up the field for rendering.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	private function setup() {
		if ( $this->field['collapsed'] ) {
			foreach ( $this->sub_fields as &$sub_field ) {
				// Add target class.
				if ( $sub_field['key'] == $this->field['collapsed'] ) {
					$sub_field['wrapper']['class'] .= ' -collapsed-target';
				}
			}
		}

		if ( $this->field['max'] ) {
			// If max 1 row, don't show order.
			if ( 1 == $this->field['max'] ) {
				$this->show_order = false;
			}

			// If max == min, don't show add or remove buttons.
			if ( $this->field['max'] <= $this->field['min'] ) {
				$this->show_remove = false;
				$this->show_add    = false;
			}
		}

		if ( empty( $this->field['rows_per_page'] ) ) {
			$this->field['rows_per_page'] = 20;
		}

		if ( (int) $this->field['rows_per_page'] < 1 ) {
			$this->field['rows_per_page'] = 20;
		}

		$this->value = $this->prepare_value();
	}

	/**
	 * Prepares the repeater values for rendering.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	private function prepare_value() {
		$value = is_array( $this->field['value'] ) ? $this->field['value'] : array();

		if ( empty( $this->field['pagination'] ) ) {
			// If there are fewer values than min, populate the extra values.
			if ( $this->field['min'] ) {
				$value = array_pad( $value, $this->field['min'], array() );
			}

			// If there are more values than max, remove some values.
			if ( $this->field['max'] ) {
				$value = array_slice( $value, 0, $this->field['max'] );
			}
		}

		$value['acfcloneindex'] = array();

		return $value;
	}

	/**
	 * Renders the full repeater table.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function render() {
		// Attributes for main wrapper div.
		$div = array(
			'class'           => 'acf-repeater -' . $this->field['layout'],
			'data-min'        => $this->field['min'],
			'data-max'        => $this->field['max'],
			'data-pagination' => ! empty( $this->field['pagination'] ),
			'data-prefix'     => $this->field['prefix'],
		);

		if ( $this->field['pagination'] ) {
			$div['data-per_page']   = $this->field['rows_per_page'];
			$div['data-total_rows'] = $this->field['total_rows'];
			$div['data-orig_name']  = $this->field['orig_name'];
			$div['data-nonce']      = wp_create_nonce( 'acf_field_' . $this->field['type'] . '_' . $this->field['key'] );
		}

		if ( empty( $this->value ) ) {
			$div['class'] .= ' -empty';
		}
		?>
		<div <?php echo acf_esc_attrs( $div ); ?>>
			<?php
			acf_hidden_input(
				array(
					'name'  => $this->field['name'],
					'value' => '',
					'class' => 'acf-repeater-hidden-input',
				)
			);
			?>
			<table class="acf-table">
				<?php $this->thead(); ?>
				<tbody>
					<?php $this->rows(); ?>
				</tbody>
			</table>
			<?php $this->table_actions(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the table head.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function thead() {
		if ( 'table' !== $this->field['layout'] ) {
			return;
		}
		?>
		<thead>
			<tr>
				<?php if ( $this->show_order ) : ?>
					<th class="acf-row-handle"></th>
				<?php endif; ?>

				<?php
				foreach ( $this->sub_fields as $sub_field ) :
					// Prepare field (allow sub fields to be removed).
					$sub_field = acf_prepare_field( $sub_field );
					if ( ! $sub_field ) {
						continue;
					}

					// Define attrs.
					$attrs = array(
						'class'     => 'acf-th',
						'data-name' => $sub_field['_name'],
						'data-type' => $sub_field['type'],
						'data-key'  => $sub_field['key'],
					);

					if ( $sub_field['wrapper']['width'] ) {
						$attrs['data-width'] = $sub_field['wrapper']['width'];
						$attrs['style']      = 'width: ' . $sub_field['wrapper']['width'] . '%;';
					}

					// Remove "id" to avoid "for" attribute on <label>.
					$sub_field['id'] = '';
					?>
					<th <?php echo acf_esc_attrs( $attrs ); ?>>
						<?php acf_render_field_label( $sub_field ); ?>
						<?php acf_render_field_instructions( $sub_field ); ?>
					</th>
				<?php endforeach; ?>

				<?php if ( $this->show_remove ) : ?>
					<th class="acf-row-handle"></th>
				<?php endif; ?>
			</tr>
		</thead>
		<?php
	}

	/**
	 * Renders or returns rows for the repeater field table.
	 *
	 * @since 6.0.0
	 *
	 * @param boolean $return If we should return the rows or render them.
	 * @return array|void
	 */
	public function rows( $return = false ) {
		$rows = array();

		// Don't include the clone when rendering via AJAX.
		if ( $return && isset( $this->value['acfcloneindex'] ) ) {
			unset( $this->value['acfcloneindex'] );
		}

		foreach ( $this->value as $i => $row ) {
			$rows[ $i ] = $this->row( $i, $row, $return );
		}

		if ( $return ) {
			return $rows;
		}

		echo implode( PHP_EOL, $rows ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML already escaped by generating functions.
	}

	/**
	 * Renders an individual row.
	 *
	 * @since 6.0.0
	 *
	 * @param integer $i      The row number.
	 * @param array   $row    An array containing the row values.
	 * @param boolean $return If we should return the row or render it.
	 * @return string|void
	 */
	public function row( $i, $row, $return = false ) {
		if ( $return ) {
			ob_start();
		}

		$id    = "row-$i";
		$class = 'acf-row';

		if ( 'acfcloneindex' === $i ) {
			$id     = 'acfcloneindex';
			$class .= ' acf-clone';
		}

		$el            = 'td';
		$before_fields = '';
		$after_fields  = '';

		if ( 'row' === $this->field['layout'] ) {
			$el            = 'div';
			$before_fields = '<td class="acf-fields -left">';
			$after_fields  = '</td>';
		} elseif ( 'block' === $this->field['layout'] ) {
			$el            = 'div';
			$before_fields = '<td class="acf-fields">';
			$after_fields  = '</td>';
		}

		printf(
			'<tr class="%s" data-id="%s">',
			esc_attr( $class ),
			esc_attr( $id )
		);

		$this->row_handle( $i );

		echo $before_fields; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- string only contains guarenteed safe HTML.

		foreach ( $this->sub_fields as $sub_field ) {
			if ( isset( $row[ $sub_field['key'] ] ) ) {
				$sub_field['value'] = $row[ $sub_field['key'] ];
			} elseif ( isset( $sub_field['default_value'] ) ) {
				$sub_field['value'] = $sub_field['default_value'];
			}

			// Update prefix to allow for nested values.
			$sub_field['prefix'] = $this->field['name'] . '[' . $id . ']';

			acf_render_field_wrap( $sub_field, $el );
		}

		echo $after_fields; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- string only contains guarenteed safe HTML.

		$this->row_actions();

		echo '</tr>';

		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Renders the row handle at the start of each row.
	 *
	 * @since 6.0.0
	 *
	 * @param integer $i The current row number.
	 * @return void
	 */
	public function row_handle( $i ) {
		if ( ! $this->show_order ) {
			return;
		}

		$hr_row_num   = intval( $i ) + 1;
		$classes      = 'acf-row-handle order';
		$title        = __( 'Drag to reorder', 'acf' );
		$row_num_html = sprintf(
			'<span class="acf-row-number" title="%s">%d</span>',
			esc_html__( 'Click to reorder', 'acf' ),
			$hr_row_num
		);

		if ( ! empty( $this->field['pagination'] ) ) {
			$classes     .= ' pagination';
			$title        = '';
			$input        = sprintf( '<input type="number" class="acf-order-input" value="%d" style="display: none;" />', $hr_row_num );
			$row_num_html = '<div class="acf-order-input-wrap">' . $input . $row_num_html . '</div>';
		}
		?>
		<td class="<?php echo esc_attr( $classes ); ?>" title="<?php echo esc_attr( $title ); ?>">
			<?php if ( $this->field['collapsed'] ) : ?>
				<a class="acf-icon -collapse small" href="#" data-event="collapse-row" title="<?php esc_attr_e( 'Click to toggle', 'acf' ); ?>"></a>
			<?php endif; ?>
			<?php echo $row_num_html; ?><?php //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped where necessary on generation. ?>
		</td>
		<?php
	}

	/**
	 * Renders the actions displayed at the end of each row.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function row_actions() {
		if ( ! $this->show_remove ) {
			return;
		}
		?>
		<td class="acf-row-handle remove">
			<a class="acf-icon -plus small acf-js-tooltip hide-on-shift" href="#" data-event="add-row" title="<?php esc_attr_e( 'Add row', 'acf' ); ?>"></a>
			<a class="acf-icon -duplicate small acf-js-tooltip show-on-shift" href="#" data-event="duplicate-row" title="<?php esc_attr_e( 'Duplicate row', 'acf' ); ?>"></a>
			<a class="acf-icon -minus small acf-js-tooltip" href="#" data-event="remove-row" title="<?php esc_attr_e( 'Remove row', 'acf' ); ?>"></a>
		</td>
		<?php
	}

	/**
	 * Renders the actions displayed underneath the table.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function table_actions() {
		if ( ! $this->show_add ) {
			return;
		}
		?>
		<div class="acf-actions">
			<a class="acf-button acf-repeater-add-row button button-primary" href="#" data-event="add-row"><?php echo acf_esc_html( $this->field['button_label'] ); ?></a>
			<?php $this->pagination(); ?>
			<div class="clear"></div>
		</div>
		<?php
	}

	/**
	 * Renders the table pagination.
	 * Mostly lifted from the WordPress core WP_List_Table class.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function pagination() {
		if ( empty( $this->field['pagination'] ) ) {
			return;
		}

		$total_rows  = isset( $this->field['total_rows'] ) ? (int) $this->field['total_rows'] : 0;
		$total_pages = ceil( $total_rows / (int) $this->field['rows_per_page'] );
		$total_pages = max( $total_pages, 1 );

		$html_current_page = sprintf(
			"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' />",
			'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page', 'acf' ) . '</label>',
			1,
			strlen( $total_pages )
		);

		$html_total_pages = sprintf( "<span class='acf-total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		?>
		<div class="acf-tablenav tablenav-pages">
			<a class="first-page button acf-nav" aria-hidden="true" data-event="first-page" title="<?php esc_attr_e( 'First Page', 'acf' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'First Page', 'acf' ); ?></span>
				<span aria-hidden="true">&laquo;</span>
			</a>
			<a class="prev-page button acf-nav" aria-hidden="true" data-event="prev-page" title="<?php esc_attr_e( 'Previous Page', 'acf' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Previous Page', 'acf' ); ?></span>
				<span aria-hidden="true">&lsaquo;</span>
			</a>
			<span class="paging-input">
				<label for="current-page-selector" class="screen-reader-text"><?php esc_html_e( 'Current Page', 'acf' ); ?></label>
				<span class="tablenav-paging-text" title="<?php esc_attr_e( 'Current Page', 'acf' ); ?>">
				<?php
				printf(
					/* translators: 1: Current page, 2: Total pages. */
					esc_html_x( '%1$s of %2$s', 'paging', 'acf' ),
					$html_current_page, //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape not necessary.
					$html_total_pages //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape not necessary.
				);
				?>
				</span>
			</span>
			<a class="next-page button acf-nav" data-event="next-page" title="<?php esc_attr_e( 'Next Page', 'acf' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Next Page', 'acf' ); ?></span>
				<span aria-hidden="true">&rsaquo;</span>
			</a>
			<a class="last-page button acf-nav" data-event="last-page" title="<?php esc_attr_e( 'Last Page', 'acf' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Last Page', 'acf' ); ?></span>
				<span aria-hidden="true">&raquo;</span>
			</a>
		</div>
		<?php
	}
}
