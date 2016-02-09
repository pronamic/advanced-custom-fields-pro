<?php

/*
*  ACF File Field Class
*
*  All the logic for this field type
*
*  @class 		acf_field_file
*  @extends		acf_field
*  @package		ACF
*  @subpackage	Fields
*/

if( ! class_exists('acf_field_file') ) :

class acf_field_file extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// vars
		$this->name = 'file';
		$this->label = __("File",'acf');
		$this->category = 'content';
		$this->defaults = array(
			'return_format'	=> 'array',
			'library' 		=> 'all',
			'min_size'		=> 0,
			'max_size'		=> 0,
			'mime_types'	=> ''
		);
		$this->l10n = array(
			'select'		=> __("Select File",'acf'),
			'edit'			=> __("Edit File",'acf'),
			'update'		=> __("Update File",'acf'),
			'uploadedTo'	=> __("uploaded to this post",'acf'),
		);
		
		
		// filters
		add_filter('get_media_item_args',			array($this, 'get_media_item_args'));
		add_filter('wp_prepare_attachment_for_js',	array($this, 'wp_prepare_attachment_for_js'), 10, 3);
		
		
		// do not delete!
    	parent::__construct();
    	
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		
		// vars
		$uploader = acf_get_setting('uploader');
		
		
		// enqueue
		if( $uploader == 'wp' ) {
			
			acf_enqueue_uploader();
			
		}
		
		
		// vars
		$o = array(
			'icon'		=> '',
			'title'		=> '',
			'size'		=> '',
			'url'		=> '',
			'name'		=> '',
		);
		
		$div = array(
			'class'				=> 'acf-file-uploader acf-cf',
			'data-library' 		=> $field['library'],
			'data-mime_types'	=> $field['mime_types'],
			'data-uploader'		=> $uploader
		);
		
		
		// has value
		if( $field['value'] && is_numeric($field['value']) ) {
			
			$file = get_post( $field['value'] );
			
			if( $file ) {
				
				$div['class'] .= ' has-value';
				
				$o['icon'] = wp_mime_type_icon( $file->ID );
				$o['title']	= $file->post_title;
				$o['size'] = @size_format(filesize( get_attached_file( $file->ID ) ));
				$o['url'] = wp_get_attachment_url( $file->ID );
				
				$explode = explode('/', $o['url']);
				$o['name'] = end( $explode );	
							
			}
			
		}
				
?>
<div <?php acf_esc_attr_e($div); ?>>
	<div class="acf-hidden">
		<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => $field['value'], 'data-name' => 'id' )); ?>
	</div>
	<div class="show-if-value file-wrap acf-soh">
		<div class="file-icon">
			<img data-name="icon" src="<?php echo $o['icon']; ?>" alt=""/>
		</div>
		<div class="file-info">
			<p>
				<strong data-name="title"><?php echo $o['title']; ?></strong>
			</p>
			<p>
				<strong><?php _e('File Name', 'acf'); ?>:</strong>
				<a data-name="name" href="<?php echo $o['url']; ?>" target="_blank"><?php echo $o['name']; ?></a>
			</p>
			<p>
				<strong><?php _e('File Size', 'acf'); ?>:</strong>
				<span data-name="size"><?php echo $o['size']; ?></span>
			</p>
			
			<ul class="acf-hl acf-soh-target">
				<?php if( $uploader != 'basic' ): ?>
					<li><a class="acf-icon -pencil dark" data-name="edit" href="#"></a></li>
				<?php endif; ?>
				<li><a class="acf-icon -cancel dark" data-name="remove" href="#"></a></li>
			</ul>
		</div>
	</div>
	<div class="hide-if-value">
		<?php if( $uploader == 'basic' ): ?>
			
			<?php if( $field['value'] && !is_numeric($field['value']) ): ?>
				<div class="acf-error-message"><p><?php echo $field['value']; ?></p></div>
			<?php endif; ?>
			
			<input type="file" name="<?php echo $field['name']; ?>" id="<?php echo $field['id']; ?>" />
			
		<?php else: ?>
			
			<p style="margin:0;"><?php _e('No File selected','acf'); ?> <a data-name="add" class="acf-button button" href="#"><?php _e('Add File','acf'); ?></a></p>
			
		<?php endif; ?>
		
	</div>
</div>
<?php
		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// clear numeric settings
		$clear = array(
			'min_size',
			'max_size'
		);
		
		foreach( $clear as $k ) {
			
			if( empty($field[$k]) ) {
				
				$field[$k] = '';
				
			}
			
		}
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value','acf'),
			'instructions'	=> __('Specify the returned value on front end','acf'),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("File Array",'acf'),
				'url'			=> __("File URL",'acf'),
				'id'			=> __("File ID",'acf')
			)
		));
		
		
		// library
		acf_render_field_setting( $field, array(
			'label'			=> __('Library','acf'),
			'instructions'	=> __('Limit the media library choice','acf'),
			'type'			=> 'radio',
			'name'			=> 'library',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', 'acf'),
				'uploadedTo'	=> __('Uploaded to post', 'acf')
			)
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum','acf'),
			'instructions'	=> __('Restrict which files can be uploaded','acf'),
			'type'			=> 'text',
			'name'			=> 'min_size',
			'prepend'		=> __('File size', 'acf'),
			'append'		=> 'MB',
		));
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum','acf'),
			'instructions'	=> __('Restrict which files can be uploaded','acf'),
			'type'			=> 'text',
			'name'			=> 'max_size',
			'prepend'		=> __('File size', 'acf'),
			'append'		=> 'MB',
		));
		
		
		// allowed type
		acf_render_field_setting( $field, array(
			'label'			=> __('Allowed file types','acf'),
			'instructions'	=> __('Comma separated list. Leave blank for all types','acf'),
			'type'			=> 'text',
			'name'			=> 'mime_types',
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) {
		
			return false;
			
		}
		
		
		// bail early if not numeric (error message)
		if( !is_numeric($value) ) {
			
			return false;
				
		}
		
		
		// convert to int
		$value = intval($value);
		
		
		// format
		if( $field['return_format'] == 'url' ) {
		
			return wp_get_attachment_url($value);
			
		} elseif( $field['return_format'] == 'array' ) {
			
			return acf_get_attachment( $value );
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  get_media_item_args
	*
	*  description
	*
	*  @type	function
	*  @date	27/01/13
	*  @since	3.6.0
	*
	*  @param	$vars (array)
	*  @return	$vars
	*/
	
	function get_media_item_args( $vars ) {
	
	    $vars['send'] = true;
	    return($vars);
	    
	}
	   	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		
		// array?
		if( is_array($value) && isset($value['ID']) ) {
		
			return $value['ID'];	
			
		}
		
		
		// object?
		if( is_object($value) && isset($value->ID) ) {
		
			return $value->ID;
			
		}
		
		
		// return
		return $value;
	}
	
	
	/*
	*  wp_prepare_attachment_for_js
	*
	*  this filter allows ACF to add in extra data to an attachment JS object
	*
	*  @type	function
	*  @date	1/06/13
	*
	*  @param	{int}	$post_id
	*  @return	{int}	$post_id
	*/
	
	function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		
		// default
		$fs = '0 kb';
		
		
		// supress PHP warnings caused by corrupt images
		if( $i = @filesize( get_attached_file( $attachment->ID ) ) ) {
		
			$fs = size_format( $i );
			
		}
		
		
		// update JSON
		$response['filesize'] = $fs;
		
		
		// return
		return $response;
		
	}
	
}

new acf_field_file();

endif;

?>
