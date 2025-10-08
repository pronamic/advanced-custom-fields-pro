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
?>
<div class="rule-group" data-id="<?php echo esc_attr( $group_id ); ?>">

	<h4><?php echo ( $group_id == 'group_0' ) ? esc_html__( 'Show this field group if', 'acf' ) : esc_html__( 'or', 'acf' ); ?></h4>
	
	<table class="acf-table -clear">
		<tbody>
			<?php
			foreach ( $group as $i => $rule ) :

				// validate rule
				$rule = acf_validate_location_rule( $rule );

				// append id and group
				$rule['id']    = "rule_{$i}";
				$rule['group'] = $group_id;

				// view
				acf_get_view(
					'acf-field-group/location-rule',
					array(
						'rule' => $rule,
					)
				);
			endforeach;
			?>
		</tbody>
	</table>
	
</div>
