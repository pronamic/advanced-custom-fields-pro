<?php

// vars
$disabled = false;

// empty
if ( empty( $field['conditional_logic'] ) ) {
	$disabled                   = true;
	$field['conditional_logic'] = array(

		// group 0
		array(

			// rule 0
			array(),
		),
	);
}

?>
<div class="acf-field acf-field-true-false acf-field-setting-conditional_logic" data-type="true_false" data-name="conditional_logic">
	<div class="acf-conditional-toggle">
		<div class="acf-label">
			<?php $acf_label_for = acf_idify( $field['prefix'] . '[conditional_logic]' ); ?>
			<label for="<?php echo esc_attr( $acf_label_for ); ?>"><?php esc_html_e( 'Conditional Logic', 'acf' ); ?></label>
		</div>
		<div class="acf-input">
			<?php

			acf_render_field(
				array(
					'type'   => 'true_false',
					'name'   => 'conditional_logic',
					'prefix' => $field['prefix'],
					'value'  => $disabled ? 0 : 1,
					'ui'     => 1,
					'class'  => 'conditions-toggle',
				)
			);

			?>

		</div>
	</div>
	<div class="rule-groups" 
	<?php
	if ( $disabled ) {
		echo ' style="display:none"';
	}
	?>
	>
		<?php
		foreach ( $field['conditional_logic'] as $group_id => $group ) :

			// validate
			if ( empty( $group ) ) {
				continue;
			}


			// vars
			// $group_id must be completely different to $rule_id to avoid JS issues
			$group_id = "group_{$group_id}";
			$h4       = ( $group_id == 'group_0' ) ? __( 'Show this field if', 'acf' ) : __( 'or', 'acf' );

			?>
			<div class="rule-group" data-id="<?php echo esc_attr( $group_id ); ?>">

				<h4><?php echo esc_html( $h4 ); ?></h4>

				<table class="acf-table -clear">
					<tbody>
					<?php
					foreach ( $group as $rule_id => $rule ) :

						// valid rule
						$rule = wp_parse_args(
							$rule,
							array(
								'field'    => '',
								'operator' => '',
								'value'    => '',
							)
						);


						// vars
						// $group_id must be completely different to $rule_id to avoid JS issues
						$rule_id = "rule_{$rule_id}";
						$prefix  = "{$field['prefix']}[conditional_logic][{$group_id}][{$rule_id}]";

						// data attributes
						$attributes = array(
							'data-id'       => $rule_id,
							'data-field'    => $rule['field'],
							'data-operator' => $rule['operator'],
							'data-value'    => $rule['value'],
						);

						?>
						<tr class="rule" <?php echo acf_esc_attrs( $attributes ); ?>>
							<td class="param">
								<?php

								acf_render_field(
									array(
										'type'     => 'select',
										'prefix'   => $prefix,
										'name'     => 'field',
										'class'    => 'condition-rule-field',
										'disabled' => $disabled,
										'value'    => $rule['field'],
										'choices'  => array(
											$rule['field'] => $rule['field'],
										),
									)
								);

								?>
							</td>
							<td class="operator">
								<?php

								acf_render_field(
									array(
										'type'     => 'select',
										'prefix'   => $prefix,
										'name'     => 'operator',
										'class'    => 'condition-rule-operator',
										'disabled' => $disabled,
										'value'    => $rule['operator'],
										'choices'  => array(
											$rule['operator'] => $rule['operator'],
										),
									)
								);

								?>
							</td>
							<td class="value">
								<?php
								$conditional_field = get_field_object( $rule['field'] );

								/**
								 * Filters the choices available for a conditional logic rule.
								 *
								 * @since 6.3.0
								 *
								 * @param array $choices The available choices.
								 * @param array $conditional_field The field object for the conditional field.
								 * @param mixed $value The value of the rule.
								 */
								$choices = apply_filters( 'acf/conditional_logic/choices', array( $rule['value'] => $rule['value'] ), $conditional_field, $rule['value'] );

								acf_render_field(
									array(
										'type'     => 'select',
										'prefix'   => $prefix,
										'name'     => 'value',
										'class'    => 'condition-rule-value',
										'disabled' => $disabled,
										'value'    => $rule['value'],
										'choices'  => $choices,
									)
								);
								?>
							</td>
							<td class="add">
								<a href="#" class="button add-conditional-rule"><?php esc_html_e( 'and', 'acf' ); ?></a>
							</td>
							<td class="remove">
								<a href="#" class="acf-icon -minus remove-conditional-rule"></a>
							</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			</div>
		<?php endforeach; ?>

		<h4><?php esc_html_e( 'or', 'acf' ); ?></h4>

		<a href="#" class="button add-conditional-group"><?php esc_html_e( 'Add rule group', 'acf' ); ?></a>
	</div>							
</div>
