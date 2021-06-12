<?php 

// Define input name prefix using unique identifier.
$input_prefix = 'acf_fields[' . $field['ID'] . ']';
$input_id = acf_idify( $input_prefix );

// Update field props.
$field['prefix'] = $input_prefix;

// Elements.
$div_attrs = array(
	'class' 	=> 'acf-field-object acf-field-object-' . acf_slugify( $field['type'] ),
	'data-id'	=> $field['ID'],
	'data-key'	=> $field['key'],
	'data-type'	=> $field['type'],
);

// Misc template vars.
$field_label = acf_get_field_label( $field, 'admin' );
$field_type_label = acf_get_field_type_label( $field['type'] );

?>
<div <?php echo acf_esc_attr( $div_attrs ); ?>>
	
	<div class="meta">
		<?php 
		$meta_inputs = array(
			'ID'			=> $field['ID'],
			'key'			=> $field['key'],
			'parent'		=> $field['parent'],
			'menu_order'	=> $i,
			'save'			=> ''
		);
		foreach( $meta_inputs as $k => $v ):
			acf_hidden_input(array( 'name' => $input_prefix . '[' . $k . ']', 'value' => $v, 'id' => $input_id . '-' . $k ));
		endforeach; ?>
	</div>
	
	<div class="handle">
		<ul class="acf-hl acf-tbody">
			<li class="li-field-order">
				<span class="acf-icon acf-sortable-handle" title="<?php _e('Drag to reorder','acf'); ?>"><?php echo ($i + 1); ?></span>
			</li>
			<li class="li-field-label">
				<strong>
					<a class="edit-field" title="<?php _e("Edit field",'acf'); ?>" href="#"><?php echo acf_esc_html( $field_label ); ?></a>
				</strong>
				<div class="row-options">
					<a class="edit-field" title="<?php _e("Edit field",'acf'); ?>" href="#"><?php _e("Edit",'acf'); ?></a>
					<a class="duplicate-field" title="<?php _e("Duplicate field",'acf'); ?>" href="#"><?php _e("Duplicate",'acf'); ?></a>
					<a class="move-field" title="<?php _e("Move field to another group",'acf'); ?>" href="#"><?php _e("Move",'acf'); ?></a>
					<a class="delete-field" title="<?php _e("Delete field",'acf'); ?>" href="#"><?php _e("Delete",'acf'); ?></a>
				</div>
			</li>
			<?php // whitespace before field name looks odd but fixes chrome bug selecting all text in row ?>
			<li class="li-field-name"> <?php echo esc_html( $field['name'] ); ?></li>
			<li class="li-field-key"> <?php echo esc_html( $field['key'] ); ?></li>
			<li class="li-field-type"> <?php echo esc_html( $field_type_label ); ?></li>
		</ul>
	</div>
	
	<div class="settings">			
		<table class="acf-table">
			<tbody class="acf-field-settings">
				<?php 
				
				// label
				acf_render_field_setting($field, array(
					'label'			=> __('Field Label','acf'),
					'instructions'	=> __('This is the name which will appear on the EDIT page','acf'),
					'name'			=> 'label',
					'type'			=> 'text',
					'class'			=> 'field-label'
				), true);
				
				
				// name
				acf_render_field_setting($field, array(
					'label'			=> __('Field Name','acf'),
					'instructions'	=> __('Single word, no spaces. Underscores and dashes allowed','acf'),
					'name'			=> 'name',
					'type'			=> 'text',
					'class'			=> 'field-name'
				), true);
				
				
				// type
				acf_render_field_setting($field, array(
					'label'			=> __('Field Type','acf'),
					'instructions'	=> '',
					'type'			=> 'select',
					'name'			=> 'type',
					'choices' 		=> acf_get_grouped_field_types(),
					'class'			=> 'field-type'
				), true);
				
				
				// instructions
				acf_render_field_setting($field, array(
					'label'			=> __('Instructions','acf'),
					'instructions'	=> __('Instructions for authors. Shown when submitting data','acf'),
					'type'			=> 'textarea',
					'name'			=> 'instructions',
					'rows'			=> 5
				), true);
				
				
				// required
				acf_render_field_setting($field, array(
					'label'			=> __('Required?','acf'),
					'instructions'	=> '',
					'type'			=> 'true_false',
					'name'			=> 'required',
					'ui'			=> 1,
					'class'			=> 'field-required'
				), true);
				
				
				// 3rd party settings
				do_action('acf/render_field_settings', $field);
				
				
				// type specific settings
				do_action("acf/render_field_settings/type={$field['type']}", $field);
				
				
				// conditional logic
				acf_get_view('field-group-field-conditional-logic', array( 'field' => $field ));
				
				
				// wrapper
				acf_render_field_wrap(array(
					'label'			=> __('Wrapper Attributes','acf'),
					'instructions'	=> '',
					'type'			=> 'number',
					'name'			=> 'width',
					'prefix'		=> $field['prefix'] . '[wrapper]',
					'value'			=> $field['wrapper']['width'],
					'prepend'		=> __('width', 'acf'),
					'append'		=> '%',
					'wrapper'		=> array(
						'data-name' => 'wrapper',
						'class' => 'acf-field-setting-wrapper'
					)
				), 'tr');
				
				acf_render_field_wrap(array(
					'label'			=> '',
					'instructions'	=> '',
					'type'			=> 'text',
					'name'			=> 'class',
					'prefix'		=> $field['prefix'] . '[wrapper]',
					'value'			=> $field['wrapper']['class'],
					'prepend'		=> __('class', 'acf'),
					'wrapper'		=> array(
						'data-append' => 'wrapper'
					)
				), 'tr');
				
				acf_render_field_wrap(array(
					'label'			=> '',
					'instructions'	=> '',
					'type'			=> 'text',
					'name'			=> 'id',
					'prefix'		=> $field['prefix'] . '[wrapper]',
					'value'			=> $field['wrapper']['id'],
					'prepend'		=> __('id', 'acf'),
					'wrapper'		=> array(
						'data-append' => 'wrapper'
					)
				), 'tr');
				
				?>
				<tr class="acf-field acf-field-save">
					<td class="acf-label"></td>
					<td class="acf-input">
						<ul class="acf-hl">
							<li>
								<a class="button edit-field" title="<?php _e("Close Field",'acf'); ?>" href="#"><?php _e("Close Field",'acf'); ?></a>
							</li>
						</ul>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	
</div>