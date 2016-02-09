<?php 

// vars
// Note: $args is always passed to this view from above
$fields = array();
$layout = false;
$parent = 0;


// use fields if passed in
extract( $args );


// add clone
$fields[] = acf_get_valid_field(array(
	'ID'		=> 'acfcloneindex',
	'key'		=> 'acfcloneindex',
	'label'		=> __('New Field','acf'),
	'name'		=> 'new_field',
	'type'		=> 'text',
	'parent'	=> $parent
));


?>
<div class="acf-field-list-wrap">
	
	<ul class="acf-hl acf-thead">
		<li class="li-field-order"><?php _e('Order','acf'); ?></li>
		<li class="li-field-label"><?php _e('Label','acf'); ?></li>
		<li class="li-field-name"><?php _e('Name','acf'); ?></li>
		<li class="li-field-type"><?php _e('Type','acf'); ?></li>
	</ul>
	
	<div class="acf-field-list<?php if( $layout ){ echo " layout-{$layout}"; } ?>">
		
		<?php foreach( $fields as $i => $field ): ?>
			
			<?php acf_get_view('field-group-field', array( 'field' => $field, 'i' => $i )); ?>
			
		<?php endforeach; ?>
		
		<div class="no-fields-message" <?php if(count($fields) > 1){ echo 'style="display:none;"'; } ?>>
			<?php _e("No fields. Click the <strong>+ Add Field</strong> button to create your first field.",'acf'); ?>
		</div>
		
	</div>
	
	<ul class="acf-hl acf-tfoot">
		<li class="acf-fr">
			<a href="#" class="button button-primary button-large add-field"><?php _e('+ Add Field','acf'); ?></a>
		</li>
	</ul>

</div>
