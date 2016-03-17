(function($){
	
	// comon
	acf.pro = acf.model.extend({
		
		actions: {
			'refresh': 	'refresh',
		},
		
		filters: {
			'get_fields' : 'get_fields',
		},
		
		get_fields: function( $fields ){
			
			// remove clone fields
			$fields = $fields.not('.acf-clone .acf-field');
			
			// return
			return $fields;
		
		},
		
		
		/*
		*  refresh
		*
		*  This function will run when acf detects a refresh is needed on the UI
		*  Most commonly after ready / conditional logic change
		*
		*  @type	function
		*  @date	10/11/2014
		*  @since	5.0.9
		*
		*  @param	n/a
		*  @return	n/a
		*/
		
		refresh: function( $el ){
			
			// reference
			var self = this;
			
			
			// defaults
			$el = $el || false;
			
			
			// if is row
			if( $el && $el.is('tr') ) {
				
				self.render_table( $el.closest('table') );
				
				return;
				
			}
			
			
			// find and rener all tables
			$('.acf-table', $el).each(function(){
				
				self.render_table( $(this) );
				
			});
			
		},
		
		render_table: function( $table ){
			
			// vars
			var $ths = $table.find('> thead th.acf-th'),
				colspan = 1,
				available_width = 100;
			
			
			// bail early if no $ths
			if( !$ths.exists() ) {
				
				return;
				
			}
			
			
			// render th/td visibility
			$ths.each(function(){
				
				// vars
				var $th = $(this),
					key = $th.attr('data-key'),
					$td = $table.find('td[data-key="' + key + '"]');
				
				
				// clear class
				$td.removeClass('appear-empty');
				$th.removeClass('hidden-by-conditional-logic');
				
				
				// no td
				if( !$td.exists() ) {
					
					// do nothing
				
				// if all td are hidden
				} else if( $td.not('.hidden-by-conditional-logic').length == 0 ) {
					
					$th.addClass('hidden-by-conditional-logic');
				
				// if 1 or more td are visible
				} else {
					
					$td.filter('.hidden-by-conditional-logic').addClass('appear-empty');
					
				}
				
			});
			
			
			
			// clear widths
			$ths.css('width', 'auto');
			
			
			// update $ths
			$ths = $ths.not('.hidden-by-conditional-logic');
			
			
			// set colspan
			colspan = $ths.length;
			
			
			// set custom widths first
			$ths.filter('[data-width]').each(function(){
				
				// vars
				var width = parseInt( $(this).attr('data-width') );
				
				
				// remove from available
				available_width -= width;
				
				
				// set width
				$(this).css('width', width + '%');
				
			});
			
			
			// update $ths
			$ths = $ths.not('[data-width]');
			
			
			// set custom widths first
			$ths.each(function(){
				
				// cal width
				var width = available_width / $ths.length;
				
				
				// set width
				$(this).css('width', width + '%');
				
			});
			
			
			// update colspan
			$table.find('.acf-row .acf-field.-collapsed-target').removeAttr('colspan');
			$table.find('.acf-row.-collapsed .acf-field.-collapsed-target').attr('colspan', colspan);
			
		},
		
		
	});

})(jQuery);

(function($){
		
	acf.fields.repeater = acf.field.extend({
		
		type: 'repeater',
		$el: null,
		$input: null,
		$table: null,
		$tbody: null,
		$clone: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click a[data-event="add-row"]': 		'_add',
			'click a[data-event="remove-row"]': 	'_remove',
			'click a[data-event="collapse-row"]': 	'_collapse',
			'mouseenter td.order': 					'_mouseenter'
		},
		
		focus: function(){
			
			// vars
			this.$el = this.$field.find('.acf-repeater:first');
			this.$input = this.$field.find('input:first');
			this.$table = this.$field.find('table:first');
			this.$tbody = this.$table.children('tbody');
			this.$clone = this.$tbody.children('tr.acf-clone');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		initialize: function(){
			
			// disable clone inputs
			this.$clone.find('input, textarea, select').attr('disabled', 'disabled');
						
			
			// render
			this.render();
			
		},
		
		show: function(){
			
			this.$tbody.find('.acf-field:visible').each(function(){
				
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		count: function(){
			
			return this.$tbody.children().length - 1;
			
		},
		
		render: function(){
			
			// update order numbers
			this.$tbody.children().each(function(i){
				
				$(this).find('> td.order > span').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 ) {
			
				this.$el.addClass('-empty');
				
			} else {
			
				this.$el.removeClass('-empty');
				
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				this.$el.find('> .acf-actions .button').addClass('disabled');
				
			} else {
				
				this.$el.find('> .acf-actions .button').removeClass('disabled');
				
			}
			
		},
		
		add: function( $tr ){
			
			// defaults
			$tr = $tr || this.$clone;
			
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				alert( acf._e('repeater','max').replace('{max}', this.o.max) );
				return false;
				
			}
			
			
			// reference
			var $field = this.$field;
				
				
			// duplicate
			$el = acf.duplicate( this.$clone );
			
						
			// remove clone class
			$el.removeClass('acf-clone');
			
			
			// enable inputs (ignore inputs disabled for life)
			$el.find('input, textarea, select').not('.acf-disabled').removeAttr('disabled');
			
			
			// move row
			$tr.before( $el );
			
			
			// focus (may have added sub repeater)
			this.doFocus($field);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
			
			// sync collapsed order
			this.sync();
			
			
			// return
			return $el;
			
		},
		
		remove: function( $tr ){
			
			// reference
			var self = this;
				
			
			// validate
			if( this.count() <= this.o.min ) {
			
				alert( acf._e('repeater','min').replace('{min}', this.o.min) );
				return false;
			}
			
			
			// action for 3rd party customization
			acf.do_action('remove', $tr);
			
			
			// animate out tr
			acf.remove_tr( $tr, function(){
				
				// trigger change to allow attachment save
				self.$input.trigger('change');
			
			
				// render
				self.render();
				
				
				// sync collapsed order
				self.sync();
				
				
				// refersh field (hide/show columns)
				acf.do_action('refresh', self.$field);
				
			});
			
		},
		
		sync: function(){
			
			// vars
			var name = 'collapsed_' + this.$field.data('key'),
				collapsed = [];
			
			
			// populate collapsed value
			this.$tbody.children().each(function( i ){
				
				if( $(this).hasClass('-collapsed') ) {
				
					collapsed.push( i );
					
				}
				
			});
			
			
			// update
			acf.update_user_setting( name, collapsed.join(',') );	
			
		},
		
		
		/*
		*  events
		*
		*  these functions are fired for this fields events
		*
		*  @type	function
		*  @date	17/09/2015
		*  @since	5.2.3
		*
		*  @param	e
		*  @return	n/a
		*/
		
		_mouseenter: function( e ){ //console.log('_mouseenter');
			
			// bail early if already sortable
			if( this.$tbody.hasClass('ui-sortable') ) return;
			
			
			// bail early if max 1 row
			if( this.o.max == 1 ) return;
			
			
			// reference
			var self = this;
			
			
			// add sortable
			this.$tbody.sortable({
				items: '> tr',
				handle: '> td.order',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function(event, ui) {
					
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			stop: function(event, ui) {
					
					// render
					self.render();
					
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			},
	   			update: function(event, ui) {
		   			
		   			// trigger change
					self.$input.trigger('change');
					
		   		}
	   			
			});
			
		},
		
		_add: function( e ){ //console.log('_add');
			
			// vars
			$row = false;
			
			
			// row add
			if( e.$el.hasClass('acf-icon') ) {
			
				$row = e.$el.closest('.acf-row');
				
			}
			
			
			// add
			this.add( $row );
				
		},
		
		_remove: function( e ){ //console.log('_remove');
			
			this.remove( e.$el.closest('.acf-row') );
			
		},
		
		_collapse: function( e ){ //console.log('_collapse');
			
			// vars
			var $tr = e.$el.closest('.acf-row');
			
			
			// open row
			if( $tr.hasClass('-collapsed') ) {
				
				$tr.removeClass('-collapsed');
				
				acf.do_action('show', $tr, 'collapse');
				
			} else {
				
				$tr.addClass('-collapsed');
				
				acf.do_action('hide', $tr, 'collapse');
				
			}
			
			
			// sync
			this.sync();
			
			
			// refersh field (hide/show columns)
			acf.do_action('refresh', this.$field);
						
		}
		
	});	
	
})(jQuery);

(function($){
		
	acf.fields.flexible_content = acf.field.extend({
		
		type: 'flexible_content',
		$el: null,
		$input: null,
		$values: null,
		$clones: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'show':		'show'
		},
		
		events: {
			'click [data-event="add-layout"]': 			'_open',
			'click [data-event="remove-layout"]': 		'_remove',
			'click [data-event="collapse-layout"]':		'_collapse',
			'click .acf-fc-layout-handle':				'_collapse',
			'click .acf-fc-popup a':					'_add',
			'blur .acf-fc-popup .focus':				'_close',
			'mouseenter .acf-fc-layout-handle': 		'_mouseenter'
		},
		
		focus: function(){
			
			// vars
			this.$el = this.$field.find('.acf-flexible-content:first');
			this.$input = this.$el.siblings('input');
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		count: function(){
			
			return this.$values.children('.layout').length;
			
		},
		
		initialize: function(){
			
			// disable clone inputs
			this.$clones.find('input, textarea, select').attr('disabled', 'disabled');
						
			
			// render
			this.render();
			
		},
		
		show: function(){
			
			this.$values.find('.acf-field:visible').each(function(){
				
				acf.do_action('show_field', $(this));
				
			});
			
		},
		
		render: function(){
			
			// vars
			var self = this;
			
			
			// update order numbers
			this.$values.children('.layout').each(function( i ){
			
				$(this).find('> .acf-fc-layout-handle .acf-fc-layout-order').html( i+1 );
				
			});
			
			
			// empty?
			if( this.count() == 0 ) {
			
				this.$el.addClass('empty');
				
			} else {
			
				this.$el.removeClass('empty');
				
			}
			
			
			// row limit reached
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				this.$el.find('> .acf-actions .button').addClass('disabled');
				
			} else {
				
				this.$el.find('> .acf-actions .button').removeClass('disabled');
				
			}
			
		},
		
		render_layout: function( $layout ){
			
			// update order number
			
			
			
			// update text
/*
			var data = acf.serialize_form($layout);
			
			console.log( data );
*/
			
		},
			
		validate_add: function( layout ){
			
			// vadiate max
			if( this.o.max > 0 && this.count() >= this.o.max ) {
				
				// vars
				var identifier	= ( this.o.max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max');
				
				
				// translate
				s = s.replace('{max}', this.o.max);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				
				// alert
				alert( s );
				
				
				// return
				return false;
			}
			
			
			// vadiate max layout
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_max		= parseInt( $a.attr('data-max') ),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			if( layout_max > 0 && layout_count >= layout_max ) {
				
				// vars
				var identifier	= ( layout_max == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'max_layout');
				
				
				// translate
				s = s.replace('{max}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				
				
				// alert
				alert( s );
				
				
				// return
				return false;
			}
			
			
			// return
			return true;
			
		},
		
		validate_remove: function( layout ){
			
			// vadiate min
			if( this.o.min > 0 && this.count() <= this.o.min ) {
				
				// vars
				var identifier	= ( this.o.min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min') + ', ' + acf._e('flexible_content', 'remove');
				
				
				// translate
				s = s.replace('{min}', this.o.min);
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				
				// return
				return confirm( s );

			}
			
			
			// vadiate max layout
			var $popup			= $( this.$el.children('.tmpl-popup').html() ),
				$a				= $popup.find('[data-layout="' + layout + '"]'),
				layout_min		= parseInt( $a.attr('data-min') ),
				layout_count	= this.$values.children('.layout[data-layout="' + layout + '"]').length;
			
			
			if( layout_min > 0 && layout_count <= layout_min ) {
				
				// vars
				var identifier	= ( layout_min == 1 ) ? 'layout' : 'layouts',
					s 			= acf._e('flexible_content', 'min_layout') + ', ' + acf._e('flexible_content', 'remove');
				
				
				// translate
				s = s.replace('{min}', layout_count);
				s = s.replace('{label}', '"' + $a.text() + '"');
				s = s.replace('{identifier}', acf._e('flexible_content', identifier));
				s = s.replace('{layout}', acf._e('flexible_content', 'layout'));
				
				
				// return
				return confirm( s );
			}
			
			
			// return
			return true;
			
		},
		
		sync: function(){
			
			// vars
			var name = 'collapsed_' + this.$field.data('key'),
				collapsed = [];
			
			
			// populate collapsed value
			this.$values.children('.layout').each(function( i ){
				
				if( $(this).hasClass('-collapsed') ) {
				
					collapsed.push( i );
					
				}
				
			});
			
			
			// update
			acf.update_user_setting( name, collapsed.join(',') );
			
		},
		
		add: function( layout, $before ){
			
			// defaults
			$before = $before || false;
			
					
			// bail early if validation fails
			if( !this.validate_add(layout) ) {
			
				return false;
				
			}
			
			
			// reference
			var $field = this.$field;
			
			
			// vars
			var $clone = this.$clones.children('.layout[data-layout="' + layout + '"]');
			
			
			// duplicate
			$el = acf.duplicate( $clone );
			
			
			// enable inputs (ignore inputs disabled for life)
			$el.find('input, textarea, select').not('.acf-disabled').removeAttr('disabled');
			
				
			// hide no values message
			this.$el.children('.no-value-message').hide();
			
			
			// add row
			if( $before ) {
				
				 $before.before( $el );
				 
			} else {
				
				this.$values.append( $el );
				
			}
			
			
			// focus (may have added sub flexible content)
			this.doFocus($field);
			
			
			// update order
			this.render();
			
			
			// validation
			acf.validation.remove_error( this.$field );
			
			
			// sync collapsed order
			this.sync();
			
		},
		
		
		/*
		*  events
		*
		*  these functions are fired for this fields events
		*
		*  @type	function
		*  @date	17/09/2015
		*  @since	5.2.3
		*
		*  @param	e
		*  @return	n/a
		*/
		
		_mouseenter: function( e ){ //console.log('_mouseenter');
			
			// bail early if already sortable
			if( this.$values.hasClass('ui-sortable') ) return;
			
			
			// bail early if max 1 row
			if( this.o.max == 1 ) return;
			
			
			// reference
			var self = this;
			
			
			// sortable
			this.$values.sortable({
				items: '> .layout',
				handle: '> .acf-fc-layout-handle',
				forceHelperSize: true,
				forcePlaceholderSize: true,
				scroll: true,
				start: function(event, ui) {
					
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			stop: function(event, ui) {
					
					// render
					self.render();
					
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			},
	   			update: function(event, ui) {
		   			
		   			// trigger change
					self.$input.trigger('change');
					
		   		}
			});
			
		},
		
		_open: function( e ){ //console.log('_open');
			
			// reference
			var $values = this.$values;
			
			
			// vars
			var $popup = $( this.$el.children('.tmpl-popup').html() );
			
			
			// modify popup
			$popup.find('a').each(function(){
				
				// vars
				var min		= parseInt( $(this).attr('data-min') ),
					max		= parseInt( $(this).attr('data-max') ),
					name	= $(this).attr('data-layout'),
					label	= $(this).text(),
					count	= $values.children('.layout[data-layout="' + name + '"]').length,
					$status = $(this).children('.status');
				
				
				if( max > 0 ) {
					
					// find diff
					var available	= max - count,
						s			= acf._e('flexible_content', 'available'),
						identifier	= ( available == 1 ) ? 'layout' : 'layouts',
				
					
					// translate
					s = s.replace('{available}', available);
					s = s.replace('{max}', max);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					
					
					// show status
					$status.show().text( available ).attr('title', s);
					
					
					// limit reached?
					if( available == 0 ) {
					
						$status.addClass('warning');
						
					}
					
				}
				
				
				if( min > 0 ) {
					
					// find diff
					var required	= min - count,
						s			= acf._e('flexible_content', 'required'),
						identifier	= ( required == 1 ) ? 'layout' : 'layouts',
				
						
					// translate
					s = s.replace('{required}', required);
					s = s.replace('{min}', min);
					s = s.replace('{label}', '"' + label + '"');
					s = s.replace('{identifier}', acf._e('flexible_content', identifier));
					
					
					// limit reached?
					if( required > 0 ) {
					
						$status.addClass('warning').show().text( required ).attr('title', s);
						
					}
					
				}
				
			});
			
			
			// add popup
			e.$el.after( $popup );
			
			
			// within layout?
			if( e.$el.closest('.acf-fc-layout-controlls').exists() ) {
				
				$popup.closest('.layout').addClass('-open');
				
			}
			
			
			// vars
			$popup.css({
				'margin-top' : 0 - $popup.height() - e.$el.outerHeight() - 14,
				'margin-left' : ( e.$el.outerWidth() - $popup.width() ) / 2,
			});
			
			
			// check distance to top
			var offset = $popup.offset().top;
			
			if( offset < 30 ) {
				
				$popup.css({
					'margin-top' : 15
				});
				
				$popup.find('.bit').addClass('top');
			}
			
			
			// focus
			$popup.children('.focus').trigger('focus');
			
		},
		
		_close: function( e ){ //console.log('_close');
			
			var $popup = e.$el.parent(),
				$layout = $popup.closest('.layout');
			
			
			// hide controlls?
			$layout.removeClass('-open');
			
			
			// remove popup
			setTimeout(function(){
				
				$popup.remove();
				
			}, 200);
			
		},
		
		_add: function( e ){ //console.log('_add');
						
			// vars
			var $popup = e.$el.closest('.acf-fc-popup'),
				layout = e.$el.attr('data-layout'),
				$before = false;
			
			
			// move row
			if( $popup.closest('.acf-fc-layout-controlls').exists() ) {
			
				$before = $popup.closest('.layout');
			
			}
			
			
			// add row
			this.add( layout, $before );
			
		},
		
		_remove: function( e ){ //console.log('_remove');
			
			// reference
			var self = this;
			
			
			// vars
			var $layout	= e.$el.closest('.layout');
			
			
			// bail early if validation fails
			if( !this.validate_remove( $layout.attr('data-layout') ) ) {
			
				return;
				
			}
			
			
			// close field
			var end_height = 0,
				$message = this.$el.children('.no-value-message');
			
			if( $layout.siblings('.layout').length == 0 ) {
			
				end_height = $message.outerHeight();
				
			}
			
			
			// action for 3rd party customization
			acf.do_action('remove', $layout);
			
			
			// remove
			acf.remove_el( $layout, function(){
				
				// update order
				self.render();
			
			
				// trigger change to allow attachment save
				self.$input.trigger('change');
				
			
				if( end_height > 0 ) {
				
					$message.show();
					
				}
				
				
				// sync collapsed order
				self.sync();
				
			}, end_height);
			
		},

		_collapse: function( e ){ //console.log('_collapse');
			
			// vars
			var $layout	= e.$el.closest('.layout');
			
			
			// open
			if( $layout.hasClass('-collapsed') ) {
			
				$layout.removeClass('-collapsed');
				
				acf.do_action('refresh', $layout);
			
			// close
			} else {
				
				$layout.addClass('-collapsed');
				
			}
			
			
			// sync collapsed order
			this.sync();
			
			
			// vars
			var data = acf.serialize( $layout );
			
			
			// append
			$.extend(data, {
				action: 	'acf/fields/flexible_content/layout_title',
				field_key: 	this.$field.data('key'),
				post_id: 	acf.get('post_id'),
				i: 			$layout.index(),
				layout:		$layout.data('layout'),
			});
			
			
			// ajax get title HTML
			$.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				data		: data,
				success: function( html ){
					
					// bail early if no html
					if( !html ) return;
					
					
					// update html
					$layout.find('> .acf-fc-layout-handle').html( html );
					
				}
			});

			
		}
		
	});	
	

})(jQuery);

(function($){
	
	acf.fields.gallery = acf.field.extend({
		
		type: 'gallery',
		$el: null,
		
		actions: {
			'ready':	'initialize',
			'append':	'initialize',
			'submit':	'close_sidebar',
			'show': 	'resize'
		},
		
		events: {
			'click .acf-gallery-attachment': 		'select_attachment',
			'click .remove-attachment':				'remove_attachment',
			'click .edit-attachment':				'edit_attachment',
			'click .update-attachment': 			'update_attachment',
			'click .add-attachment':				'add_attachment',
			'click .close-sidebar':					'close_sidebar',
			'change .acf-gallery-side input':		'update_attachment',
			'change .acf-gallery-side textarea':	'update_attachment',
			'change .acf-gallery-side select':		'update_attachment',
			'change .bulk-actions':					'sort'
		},
		
		focus: function(){
			
			this.$el = this.$field.find('.acf-gallery').first();
			this.$values = this.$el.children('.values');
			this.$clones = this.$el.children('.clones');
			
			
			// get options
			this.o = acf.get_data( this.$el );
			
			
			// min / max
			this.o.min = this.o.min || 0;
			this.o.max = this.o.max || 0;
			
		},
		
		get_attachment: function( id ){
			
			// defaults
			id = id || '';
			
			
			// vars
			var selector = '.acf-gallery-attachment';
			
			
			// update selector
			if( id === 'active' ) {
				
				selector += '.active';
				
			} else if( id ) {
				
				selector += '[data-id="' + id  + '"]';
				
			}
			
			
			// return
			return this.$el.find( selector );
			
		},
		
		count: function(){
			
			return this.get_attachment().length;
			
		},

		initialize: function(){
			
			// reference
			var self = this,
				$field = this.$field;
				
					
			// sortable
			this.$el.find('.acf-gallery-attachments').unbind('sortable').sortable({
				
				items					: '.acf-gallery-attachment',
				forceHelperSize			: true,
				forcePlaceholderSize	: true,
				scroll					: true,
				
				start: function (event, ui) {
					
					ui.placeholder.html( ui.item.html() );
					ui.placeholder.removeAttr('style');
								
					acf.do_action('sortstart', ui.item, ui.placeholder);
					
	   			},
	   			
	   			stop: function (event, ui) {
				
					acf.do_action('sortstop', ui.item, ui.placeholder);
					
	   			}
			});
			
			
			// resizable
			this.$el.unbind('resizable').resizable({
				handles: 's',
				minHeight: 200,
				stop: function(event, ui){
					
					acf.update_user_setting('gallery_height', ui.size.height);
				
				}
			});
			
			
			// resize
			$(window).on('resize', function(){
				
				self.doFocus( $field ).resize();
				
			});
			
			
			// render
			this.render();
			
			
			// resize
			this.resize();
					
		},

		render: function() {
			
			// vars
			var $select = this.$el.find('.bulk-actions'),
				$a = this.$el.find('.add-attachment');
			
			
			// disable select
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				$a.addClass('disabled');
				
			} else {
			
				$a.removeClass('disabled');
				
			}
			
		},
		
		sort: function( e ){
			
			// vars
			var sort = e.$el.val();
			
			
			// validate
			if( !sort ) {
			
				return;
				
			}
			
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_sort_order',
				field_key	: this.$field.data('key'),
				post_id		: acf.get('post_id'),
				ids			: [],
				sort		: sort
			});
			
			
			// find and add attachment ids
			this.get_attachment().each(function(){
				
				data.ids.push( $(this).attr('data-id') );
				
			});
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'json',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.sort_success
			});
			
		},
		
		sort_success: function( json ) {
		
			// validate
			if( !acf.is_ajax_success(json) ) {
			
				return;
				
			}
			
			
			// reverse order
			json.data.reverse();
			
			
			// loop over json
			for( i in json.data ) {
				
				var id = json.data[ i ],
					$attachment = this.get_attachment(id);
				
				
				// prepend attachment
				this.$el.find('.acf-gallery-attachments').prepend( $attachment );
				
			};
			
		},
		
		clear_selection: function(){
			
			this.get_attachment().removeClass('active');
			
		},
		
		select_attachment: function( e ){
			
			// vars
			var $attachment = e.$el;
			
			
			// bail early if already active
			if( $attachment.hasClass('active') ) {
				
				return;
				
			}
			
			
			// vars
			var id = $attachment.attr('data-id');
			
			
			// clear selection
			this.clear_selection();
			
			
			// add selection
			$attachment.addClass('active');
			
			
			// fetch
			this.fetch( id );
			
			
			// open sidebar
			this.open_sidebar();
			
		},
		
		open_sidebar: function(){
			
			// add class
			this.$el.addClass('sidebar-open');
			
			
			// hide bulk actions
			this.$el.find('.bulk-actions').hide();
			
			
			// vars
			var width = this.$el.width() / 3;
			
			
			// set minimum width
			width = parseInt( width );
			width = Math.max( width, 350 );
			
			
			// animate
			this.$el.find('.acf-gallery-side-inner').css({ 'width' : width-1 });
			this.$el.find('.acf-gallery-side').animate({ 'width' : width-1 }, 250);
			this.$el.find('.acf-gallery-main').animate({ 'right' : width }, 250);
						
		},
		
		close_sidebar: function(){
			
			// remove class
			this.$el.removeClass('sidebar-open');
			
			
			// vars
			var $select = this.$el.find('.bulk-actions');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// disable sidebar
			this.$el.find('.acf-gallery-side').find('input, textarea, select').attr('disabled', 'disabled');
			
			
			// animate
			this.$el.find('.acf-gallery-main').animate({ right: 0 }, 250);
			this.$el.find('.acf-gallery-side').animate({ width: 0 }, 250, function(){
				
				$select.show();
				
				$(this).find('.acf-gallery-side-data').html( '' );
				
			});
			
		},
		
		fetch: function( id ){
			
			// vars
			var data = acf.prepare_for_ajax({
				action		: 'acf/fields/gallery/get_attachment',
				field_key	: this.$field.data('key'),
				nonce		: acf.get('nonce'),
				post_id		: acf.get('post_id'),
				id			: id
			});
			
			
			// abort XHR if this field is already loading AJAX data
			if( this.$el.data('xhr') ) {
			
				this.$el.data('xhr').abort();
				
			}
			
			
			// get results
		    var xhr = $.ajax({
		    	url			: acf.get('ajaxurl'),
				dataType	: 'html',
				type		: 'post',
				cache		: false,
				data		: data,
				context		: this,
				success		: this.render_fetch
			});
			
			
			// update el data
			this.$el.data('xhr', xhr);
			
		},
		
		render_fetch: function( html ){
			
			// bail early if no html
			if( !html ) {
				
				return;	
				
			}
			
			
			// vars
			var $side = this.$el.find('.acf-gallery-side-data');
			
			
			// render
			$side.html( html );
			
			
			// remove acf form data
			$side.find('.compat-field-acf-form-data').remove();
			
			
			// detach meta tr
			var $tr = $side.find('> .compat-attachment-fields > tbody > tr').detach();
			
			
			// add tr
			$side.find('> table.form-table > tbody').append( $tr );			
			
			
			// remove origional meta table
			$side.find('> .compat-attachment-fields').remove();
			
			
			// setup fields
			acf.do_action('append', $side);
			
		},
		
		update_attachment: function(){
			
			// vars
			var $a = this.$el.find('.update-attachment')
				$form = this.$el.find('.acf-gallery-side-data'),
				data = acf.serialize_form( $form );
				
				
			// validate
			if( $a.attr('disabled') ) {
			
				return false;
				
			}
			
			
			// add attr
			$a.attr('disabled', 'disabled');
			$a.before('<i class="acf-loading"></i>');
			
			
			// append AJAX action		
			data.action = 'acf/fields/gallery/update_attachment';
			
			
			// prepare for ajax
			acf.prepare_for_ajax(data);
			
			
			// ajax
			$.ajax({
				url			: acf.get('ajaxurl'),
				data		: data,
				type		: 'post',
				dataType	: 'json',
				complete	: function( json ){
					
					$a.removeAttr('disabled');
					$a.prev('.acf-loading').remove();
					
				}
			});
			
		},
		
		add: function( a ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var thumb_url = a.url,
				thumb_class = 'acf-gallery-attachment acf-soh',
				filename = '',
				name = this.$el.find('[data-name="ids"]').attr('name');

			
			// title
			if( a.type !== 'image' && a.filename ) {
				
				filename = '<div class="filename">' + a.filename + '</div>';
				
			}
			
			
			// icon
			if( !thumb_url ) {
				
				thumb_url = a.icon;
				thumb_class += ' is-mime-icon';
				
			}
			
			
			// html
			var html = [
			'<div class="' + thumb_class + '" data-id="' + a.id + '">',
				'<input type="hidden" value="' + a.id + '" name="' + name + '[]">',
				'<div class="margin" title="' + a.filename + '">',
					'<div class="thumbnail">',
						'<img src="' + thumb_url + '">',
					'</div>',
					filename,
				'</div>',
				'<div class="actions acf-soh-target">',
					'<a href="#" class="acf-icon -cancel dark remove-attachment" data-id="' + a.id + '"></a>',
				'</div>',
			'</div>'].join('');
			
			
			// append
			this.$el.find('.acf-gallery-attachments').append( html );
			
			
			// render
			this.render();
			
		},
		
		edit_attachment:function( e ){
			
			// reference
			var self = this;
			
			
			// vars
			var id = acf.get_data(e.$el, 'id');
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('image', 'edit'),
				button:		acf._e('image', 'update'),
				mode:		'edit',
				id:			id,
				select:		function( attachment ){
					
					// override url
					if( acf.isset(attachment, 'attributes', 'sizes', self.o.preview_size, 'url') ) {
			    	
				    	attachment.url = attachment.attributes.sizes[ self.o.preview_size ].url;
				    	
			    	}
			    	
			    	
			    	// update image
			    	self.get_attachment(id).find('img').attr( 'src', attachment.url );
				 	
				 	
				 	// render sidebar
					self.fetch( id );
					
				}
			});
						
		},
		
		remove_attachment: function( e ){
			
			// prevent event from triggering click on attachment
			e.stopPropagation();
			
			
			// vars
			var id = acf.get_data(e.$el, 'id');
			
			
			// deselect attachmnet
			this.clear_selection();
			
			
			// update sidebar
			this.close_sidebar();
			
			
			// remove image
			this.get_attachment(id).remove();
			
			
			// render
			this.render();
			
			
		},
		
		render_collection: function( frame ){
			
			var self = this;
			
			
			// Note: Need to find a differen 'on' event. Now that attachments load custom fields, this function can't rely on a timeout. Instead, hook into a render function foreach item
			
			// set timeout for 0, then it will always run last after the add event
			setTimeout(function(){
			
			
				// vars
				var $content	= frame.content.get().$el
					collection	= frame.content.get().collection || null;
					

				
				if( collection ) {
					
					var i = -1;
					
					collection.each(function( item ){
					
						i++;
						
						var $li = $content.find('.attachments > .attachment:eq(' + i + ')');
						
						
						// if image is already inside the gallery, disable it!
						if( self.get_attachment(item.id).exists() ) {
						
							item.off('selection:single');
							$li.addClass('acf-selected');
							
						}
						
					});
					
				}
			
			
			}, 10);

				
		},
		
		add_attachment: function( e ){
			
			// validate
			if( this.o.max > 0 && this.count() >= this.o.max ) {
			
				acf.validation.add_warning( this.$field, acf._e('gallery', 'max'));
				
				return;
				
			}
			
			
			// vars
			var preview_size = this.o.preview_size;
			
			
			// reference
			var self = this,
				$field = this.$field;
			
			
			// popup
			var frame = acf.media.popup({
				
				title:		acf._e('gallery', 'select'),
				mode:		'select',
				type:		'',
				field:		this.$field.data('key'),
				multiple:	'add',
				library:	this.o.library,
				mime_types: this.o.mime_types,
				
				select: function( attachment, i ) {
					
					// vars
					var atts = attachment.attributes;
					
					
					// focus
					self.doFocus($field);
							
							
					// is image already in gallery?
					if( self.get_attachment(atts.id).exists() ) {
					
						return;
						
					}
					
					//console.log( attachment );
			    	
			    	// vars
			    	var a = {
				    	id:			atts.id,
				    	type:		atts.type,
				    	icon:		atts.icon,
				    	filename:	atts.filename,
				    	url:		''
			    	};
			    	
			    	
			    	// type
			    	if( a.type === 'image' ) {
				    	
				    	a.url = acf.maybe_get(atts, 'sizes.'+preview_size+'.url', atts.url);
				    	
			    	} else {
				    	
				    	a.url = acf.maybe_get(atts, 'thumb.src', '');
				    	
				    }
				    
				    
			    	// add file to field
			        self.add( a );
					
				}
			});
			
			
			// modify DOM
			frame.on('content:activate:browse', function(){
				
				self.render_collection( frame );
				
				frame.content.get().collection.on( 'reset add', function(){
				    
					self.render_collection( frame );
				    
			    });
				
			});
			
		},
		
		resize: function(){
			
			// vars
			var min = 100,
				max = 175,
				columns = 4,
				width = this.$el.width();
			
			
			// get width
			for( var i = 4; i < 20; i++ ) {
			
				var w = width/i;
				
				if( min < w && w < max ) {
				
					columns = i;
					break;
					
				}
				
			}
			
			
			// max columns css is 8
			columns = Math.min(columns, 8);
			
			
			// update data
			this.$el.attr('data-columns', columns);
		}
		
	});
	
})(jQuery);

// @codekit-prepend "../js/acf-pro.js";
// @codekit-prepend "../js/acf-repeater.js";
// @codekit-prepend "../js/acf-flexible-content.js";
// @codekit-prepend "../js/acf-gallery.js";

