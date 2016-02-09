(function($){        
	
	acf.field_group_pro = acf.model.extend({
		
		actions: {
			'open_field':			'update_field_parent',
			'sortstop':				'update_field_parent',
			'duplicate_field':		'duplicate_field',
			'delete_field':			'delete_field',
			'change_field_type':	'change_field_type'
		},
		
		
    	/*
    	*  fix_conditional_logic
    	*
    	*  This function will update sub field conditional logic rules after duplication
    	*
    	*  @type	function
    	*  @date	10/06/2014
    	*  @since	5.0.0
    	*
    	*  @param	$fields (jquery selection)
    	*  @return	n/a
    	*/
    	
    	fix_conditional_logic : function( $fields ){
	    	
	    	// build refernce
			var ref = {};
			
			$fields.each(function(){
				
				ref[ $(this).attr('data-orig') ] = $(this).attr('data-key');
				
			});
			
			
	    	$fields.find('.conditional-rule-param').each(function(){
		    	
		    	// vars
		    	var key = $(this).val();
		    	
		    	
		    	// bail early if val is not a ref key
		    	if( !(key in ref) ) {
			    	
			    	return;
			    	
		    	}
		    	
		    	
		    	// add option if doesn't yet exist
		    	if( ! $(this).find('option[value="' + ref[key] + '"]').exists() ) {
			    	
			    	$(this).append('<option value="' + ref[key] + '">' + ref[key] + '</option>');
			    	
		    	}
		    	
		    	
		    	// set new val
		    	$(this).val( ref[key] );
		    	
	    	});
	    	
    	},
    	
    	
    	/*
    	*  update_field_parent
    	*
    	*  This function will update field meta such as parent
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	update_field_parent: function( $el ){
	    	
	    	// bail early if not div.field (flexible content tr)
	    	if( !$el.hasClass('acf-field-object') ) {
		    	
		    	return;
		    	
	    	}
	    	
	    	
	    	// vars
	    	var $parent = $el.parent().closest('.acf-field-object'),
		    	val = acf.get('post_id');
		    
		    
		    // find parent
			if( $parent.exists() ) {
				
				// set as parent ID
				val = acf.field_group.get_field_meta( $parent, 'ID' );
				
				
				// if parent is new, no ID exists
				if( !val ) {
					
					val = acf.field_group.get_field_meta( $parent, 'key' );
					
				}
				
			}
			
			
			// update parent
			acf.field_group.update_field_meta( $el, 'parent', val );
	    	
	    	
	    	// action for 3rd party customization
			acf.do_action('update_field_parent', $el, $parent);
			
    	},
    	
    	
    	/*
    	*  duplicate_field
    	*
    	*  This function is triggered when duplicating a field
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	duplicate_field: function( $el ) {
	    	
	    	// vars
			var $fields = $el.find('.acf-field-object').not('[data-id="acfcloneindex"]');
				
			
			// bail early if $fields are empty
			if( !$fields.exists() ) {
				
				return;
				
			}
			
			
			// loop over sub fields
	    	$fields.each(function(){
		    	
		    	// vars
		    	var $parent = $(this).parent().closest('.acf-field-object'),
		    		key = acf.field_group.get_field_meta( $parent, 'key');
		    		
		    	
		    	// wipe field
		    	acf.field_group.wipe_field( $(this) );
		    	
		    	
		    	// update parent
		    	acf.field_group.update_field_meta( $(this), 'parent', key );
		    	
		    	
		    	// save field
		    	acf.field_group.save_field( $(this) );
		    	
		    	
	    	});
	    	
	    	
	    	// fix conditional logic rules
	    	this.fix_conditional_logic( $fields );
	    	
    	},
    	
    	
    	/*
    	*  delete_field
    	*
    	*  This function is triggered when deleting a field
    	*
    	*  @type	function
    	*  @date	8/04/2014
    	*  @since	5.0.0
    	*
    	*  @param	$el
    	*  @return	n/a
    	*/
    	
    	delete_field : function( $el ){
	    	
	    	$el.find('.acf-field-object').each(function(){
		    	
		    	acf.field_group.delete_field( $(this), false );
		    	
	    	});
	    	
    	},
    	
    	
    	/*
    	*  change_field_type
    	*
    	*  This function is triggered when changing a field type
    	*
    	*  @type	function
    	*  @date	7/06/2014
    	*  @since	5.0.0
    	*
    	*  @param	$post_id (int)
    	*  @return	$post_id (int)
    	*/
		
		change_field_type : function( $el ) {
			
			$el.find('.acf-field-object').each(function(){
		    	
		    	acf.field_group.delete_field( $(this), false );
		    	
	    	});
			
		}
		
	});
	
	
	
	/*
	*  repeater
	*
	*  description
	*
	*  @type	function
	*  @date	12/02/2015
	*  @since	5.1.5
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	var acf_settings_repeater = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render'
		},
		
		events: {
			'change .acf-field[data-name="layout"] input':		'render',
			'focus .acf-field[data-name="collapsed"] select':	'focus',
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('.acf-field-object');
			
		},
				
		render: function( $el ){
			
			// bail early if not correct field type
			if( $el.attr('data-type') != 'repeater' ) {
				
				return;
				
			}
			
			
			// vars
			// may seem slow using '>' but is actually ~ 3x faster!
			var $layout = $el.find('> .settings > table > tbody > [data-name="layout"] input:checked'),
				$fields = $el.find('.acf-field-list:first');
			
			
			
			// update data
			$fields.attr('data-layout', $layout.val());
			
			
			// trigger focus and populate collapsed select
			this.focus( $el );
			
		},
		
		focus: function( $el ){
			
			// vars
			var $collapsed = $el.find('> .settings > table > tbody > [data-name="collapsed"] select'),
				$fields = $el.find('.acf-field-list:first');
			
			
			// collapsed
			var choices = [];
			
			choices.push({
				'label': $collapsed.find('option[value=""]').text(),
				'value': ''
			});
			
			
			$fields.children('.acf-field-object').not('[data-id="acfcloneindex"]').each(function(){
				
				// vars
				var $field = $(this);
				
				
				// append
				choices.push({
					'label': $field.find('.field-label:first').val(),
					'value': $field.attr('data-key')
				});
				
			});
			
			
			// render
			acf.render_select( $collapsed, choices );
			
		}
		
	});
	
	
	/*
	*  flexible_content
	*
	*  description
	*
	*  @type	function
	*  @date	25/09/2015
	*  @since	5.2.3
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	var acf_settings_flexible_content = acf.model.extend({
		
		actions: {
			'open_field':			'render',
			'change_field_type':	'render',
			'update_field_parent':	'update_field_parent'
		},
		
		events: {
			'change .acf-fc-meta-display select':		'_layout',
			'blur .acf-fc-meta-label input':			'_label',
			'click a[data-name="acf-fc-add"]':			'_add',
			'click a[data-name="acf-fc-duplicate"]':	'_duplicate',
			'click a[data-name="acf-fc-delete"]':		'_delete'
		},
		
		event: function( e ){
			
			// override
			return e.$el.closest('tr[data-name="fc_layout"]');
			
		},
				
		render: function( $el ){
			
			// reference
			var self = this;
			
			
			// bail early if not flexible_content
			if( $el.attr('data-type') != 'flexible_content' ) {
				
				return;
				
			}
			
			
			// vars
			var $tbody = $el.find('> .settings > table > tbody');
			
			
			// validate
			if( ! $tbody.hasClass('ui-sortable') ) {
				
				// add sortable
				$tbody.sortable({
					items					: '> tr[data-name="fc_layout"]',
					handle					: '[data-name="acf-fc-reorder"]',
					forceHelperSize			: true,
					forcePlaceholderSize	: true,
					scroll					: true,
					start : function (event, ui) {
						
						acf.do_action('sortstart', ui.item, ui.placeholder);
						
		   			},
		   			
		   			stop : function (event, ui) {
					
						acf.do_action('sortstop', ui.item, ui.placeholder);
						
						// save flexible content (layout order has changed)
						acf.field_group.save_field( $el );
						
		   			}
				});
				
			}
			
			
			// render layouts
			$tbody.children('tr[data-name="fc_layout"]').each(function(){
				
				self.render_layout( $(this) );
					
			});
			
		},
		
		
		/*
		*  render_layout
		*
		*  This function will update the field list class
		*
		*  @type	function
		*  @date	8/04/2014
		*  @since	5.0.0
		*
		*  @param	$field_list
		*  @return	n/a
		*/
		
		render_layout: function( $el ){
			
			// reference
			var self = this;
			
			
			// vars
			var $key = $el.find('.acf-fc-meta-key:first input'),
				$display = $el.find('.acf-fc-meta-display:first select'),
				$fields = $el.find('.acf-field-list:first');
			
			
			// update key
			// - both duplicate and add function need this
			$key.val( $el.attr('data-id') );
			
			
			// update data
			$fields.attr('data-layout', $display.val());
			
			
			// update meta
			var layout_key = $el.attr('data-id');
			
			$fields.children('.acf-field-object').each(function(){
				
				self.render_meta( $(this), layout_key );
				
			});
			
		},
		
		
		render_meta: function( $field, layout_key ){
			
			acf.field_group.update_field_meta( $field, 'parent_layout', layout_key );
			
		},
		
		update_field_parent: function( $el, $parent ){			
			
			// remove parent_layout if not a sub field
			if( !$parent.exists() ) {
				
				acf.field_group.delete_field_meta( $el, 'parent_layout' );
				return;
				
			}
			
			
			// baill eraly if not flexible content
			if( $parent.attr('data-type') != 'flexible_content' ) {
				
				acf.field_group.delete_field_meta( $el, 'parent_layout' );
				return;
				
			}
			
			
			// vars
			var $tr = $el.closest('tr[data-name="fc_layout"]');
			
			
			// update meta
			this.render_meta( $el, $tr.attr('data-id') );
			
			
			// save field
			// - parent_layout meta needs to be saved within the post_content serialized array
			acf.field_group.save_field( $el );
						
		},
		
		
		/*
		*  events
		*
		*  description
		*
		*  @type	function
		*  @date	25/09/2015
		*  @since	5.2.3
		*
		*  @param	$post_id (int)
		*  @return	$post_id (int)
		*/
		
		_layout: function( $el ){
			
			this.render_layout( $el );
			
		},
		
		_add: function( $el ){
			
			// duplicate
			$el2 = acf.duplicate( $el );
			
			
			// remove sub fields
			$el2.find('.acf-field-object').not('[data-id="acfcloneindex"]').remove();
	
			
			// show add new message
			$el2.find('.no-fields-message').show();
			
			
			// reset layout meta values
			$el2.find('.acf-fc-meta input').val('');
			
			
			// add new tr
			$el.after( $el2 );
			
			
			// render layout
			this.render_layout( $el2 );
			
			
			// save field
			acf.field_group.save_field( $el.closest('.acf-field-object') );
			
		},
		
		_duplicate: function( $el ){
			
			// duplicate
			$el2 = acf.duplicate( $el );
			
			
			// add new tr
			$el.after( $el2 );
			
			
			// fire action 'duplicate_field' and allow acf.pro logic to clean sub fields
			acf.do_action('duplicate_field', $el2);
			
			
			// render layout
			this.render_layout( $el2 );
			
			
			// save field
			acf.field_group.save_field( $el.closest('.acf-field-object') );
			
		},
		
		_delete: function( $el ){
			
			// validate
			if( $el.siblings('tr[data-name="fc_layout"]').length == 0 ) {
			
				alert( acf._e('flexible_content','layout_warning') );
				
				return false;
				
			}
			
			
			// delete fields
			$el.find('.acf-field-object').not('[data-id="acfcloneindex"]').each(function(){
				
				// delete without animation
				acf.field_group.delete_field( $(this), false );
				
			});
			
			
			// remove tr
			acf.remove_tr( $el );
			
			
			// save field
			acf.field_group.save_field( $el.closest('.acf-field-object') );
				
		},
		
		_label: function( $el ){
			
			// vars
			var $label = $el.find('.acf-fc-meta-label:first input'),
				$name = $el.find('.acf-fc-meta-name:first input');
			
			
			// only if name is empty
			if( $name.val() == '' ) {
				
				// vars
				var s = $label.val();
				
				
				// sanitize
				s = acf.str_sanitize(s);
				
				
				// update name
				$name.val( s ).trigger('change');
				
			}
			
		}
		
	});

})(jQuery);

// @codekit-prepend "../js/field-group.js";

