<?php
/**
 * This is a PHP file containing the code for the acf_field_icon_picker class.
 *
 * @package Advanced_Custom_Fields_Pro
 */

if ( ! class_exists( 'acf_field_icon_picker' ) ) :

	/**
	 * Class acf_field_icon_picker.
	 */
	class acf_field_icon_picker extends acf_field {
		/**
		 * Initialize icon picker field
		 *
		 * @since 6.3
		 *
		 * @return void
		 */
		public function initialize() {
			$this->name          = 'icon_picker';
			$this->label         = __( 'Icon Picker', 'acf' );
			$this->public        = true;
			$this->category      = 'advanced';
			$this->description   = __( 'An interactive UI for selecting an icon. Select from Dashicons, the media library, or a standalone URL input.', 'acf' );
			$this->preview_image = acf_get_url() . '/assets/images/field-type-previews/field-preview-icon-picker.png';
			$this->doc_url       = acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/icon-picker/', 'docs', 'field-type-selection' );
			$this->defaults      = array(
				'library'       => 'all',
				'tabs'          => array_keys( $this->get_tabs() ),
				'return_format' => 'string',
				'default_value' => array(
					'type'  => null,
					'value' => null,
				),
			);
		}

		/**
		 * Gets the available tabs for the icon picker field.
		 *
		 * @since 6.3
		 *
		 * @return array
		 */
		public function get_tabs() {
			$tabs = array(
				'dashicons' => esc_html__( 'Dashicons', 'acf' ),
			);

			if ( current_user_can( 'upload_files' ) ) {
				$tabs['media_library'] = esc_html__( 'Media Library', 'acf' );
			}

			$tabs['url'] = esc_html__( 'URL', 'acf' );

			/**
			 * Allows filtering the tabs used by the icon picker.
			 *
			 * @since 6.3
			 *
			 * @param array $tabs An associative array of tabs in key => label format.
			 * @return array
			 */
			return apply_filters( 'acf/fields/icon_picker/tabs', $tabs );
		}

		/**
		 * Renders icon picker field
		 *
		 * @since 6.3
		 *
		 * @param object $field The ACF Field
		 * @return void
		 */
		public function render_field( $field ) {
			$uploader = acf_get_setting( 'uploader' );

			// Enqueue uploader scripts
			if ( $uploader === 'wp' ) {
				acf_enqueue_uploader();
			}

			$div = array(
				'id'    => $field['id'],
				'class' => $field['class'] . ' acf-icon-picker',
			);

			echo '<div ' . acf_esc_attrs( $div ) . '>';

			acf_hidden_input(
				array(
					'name'             => $field['name'] . '[type]',
					'value'            => $field['value']['type'],
					'data-hidden-type' => 'type',
				)
			);
			acf_hidden_input(
				array(
					'name'             => $field['name'] . '[value]',
					'value'            => $field['value']['value'],
					'data-hidden-type' => 'value',
				)
			);

			$tabs  = $this->get_tabs();
			$shown = array_filter(
				$tabs,
				function ( $tab ) use ( $field ) {
					return in_array( $tab, $field['tabs'], true );
				},
				ARRAY_FILTER_USE_KEY
			);

			foreach ( $shown as $name => $label ) {
				acf_render_field_wrap(
					array(
						'type'           => 'tab',
						'label'          => $label,
						'key'            => 'acf_icon_picker_tabs',
						'selected'       => $name === $field['value']['type'],
						'unique_tab_key' => $name,
					)
				);

				$wrapper_class = str_replace( '_', '-', $name );
				echo '<div class="acf-icon-picker-tabs acf-icon-picker-' . esc_attr( $wrapper_class ) . '-tabs">';

				switch ( $name ) {
					case 'dashicons':
						echo '<div class="acf-dashicons-search-wrap">';
							acf_text_input(
								array(
									'class'       => 'acf-dashicons-search-input',
									'placeholder' => esc_html__( 'Search icons...', 'acf' ),
									'type'        => 'search',
								)
							);
						echo '</div>';
						echo '<div class="acf-dashicons-list"></div>';
						?>
						<div class="acf-dashicons-list-empty">
							<img src="<?php echo esc_url( acf_get_url( 'assets/images/face-sad.svg' ) ); ?>" />
							<p class="acf-no-results-text">
								<?php
								printf(
									/* translators: %s: The invalid search term */
									esc_html__( "No search results for '%s'", 'acf' ),
									'<span class="acf-invalid-dashicon-search-term"></span>'
								);
								?>
							</p>
						</div>

						<?php
						break;
					case 'media_library':
						?>
						<div class="acf-icon-picker-tab" data-category="<?php echo esc_attr( $name ); ?>">
							<div class="acf-icon-picker-media-library">
								<button
									aria-label="<?php esc_attr_e( 'Click to change the icon in the Media Library', 'acf' ); ?>"
									class="acf-icon-picker-media-library-preview"
									style="<?php echo esc_attr( 'media_library' === $field['value']['type'] || 'dashicons' === $field['value']['type'] && ! empty( $field['value']['value'] ) ? '' : 'display: none;' ); ?>"
								>
									<div class="acf-icon-picker-media-library-preview-img" style="<?php echo esc_attr( 'media_library' !== $field['value']['type'] ? 'display: none;' : '' ); ?>">
										<?php
											$img_url = wp_get_attachment_image_url( $field['value']['value'], 'thumbnail' );
											// If the type is media_library, then we need to show the media library preview.
										?>
											<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php esc_attr_e( 'The currently selected image preview', 'acf' ); ?>" />
									</div>
									<div class="acf-icon-picker-media-library-preview-dashicon" style="<?php echo esc_attr( 'dashicons' !== $field['value']['type'] ? 'display: none;' : '' ); ?>">
										<div class="dashicons <?php echo esc_attr( $field['value']['value'] ); ?>">
										</div>
									</div>
								</button>
								<button class="acf-icon-picker-media-library-button">
									<div class="acf-icon-picker-media-library-button-icon dashicons dashicons-admin-media"></div>
									<span><?php esc_html_e( 'Browse Media Library', 'acf' ); ?></span>
								</button>
							</div>
						</div>
						<?php
						break;
					case 'url':
						echo '<div class="acf-icon-picker-url">';
						acf_text_input(
							array(
								'class' => 'acf-icon_url',
								'value' => $field['value']['type'] === 'url' ? $field['value']['value'] : '',
							)
						);

						// Helper Text
						?>
						<p class="description"><?php esc_html_e( 'The URL to the icon you\'d like to use, or svg as Data URI', 'acf' ); ?></p>
						<?php
						echo '</div>';
						break;
					default:
						do_action( 'acf/fields/icon_picker/tab/' . $name, $field );
				}

				echo '</div>';
			}

			echo '</div>';
		}


		/**
		 * Renders field settings for the icon picker field.
		 *
		 * @since 6.3
		 *
		 * @param array $field The icon picker field object.
		 * @return void
		 */
		public function render_field_settings( $field ) {
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Tabs', 'acf' ),
					'instructions' => __( 'Select where content editors can choose the icon from.', 'acf' ),
					'type'         => 'checkbox',
					'name'         => 'tabs',
					'choices'      => $this->get_tabs(),
				)
			);

			$return_format_doc = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				acf_add_url_utm_tags( 'https://www.advancedcustomfields.com/resources/icon-picker/', 'docs', 'icon-picker-return-format' ),
				__( 'Learn More', 'acf' )
			);

			$return_format_instructions = sprintf(
				/* translators: %s - link to documentation */
				__( 'Specify the return format for the icon. %s', 'acf' ),
				$return_format_doc
			);

			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Return Format', 'acf' ),
					'instructions' => $return_format_instructions,
					'type'         => 'radio',
					'name'         => 'return_format',
					'choices'      => array(
						'string' => __( 'String', 'acf' ),
						'array'  => __( 'Array', 'acf' ),
					),
					'layout'       => 'horizontal',
				)
			);
		}

		/**
		 * Localizes text for Icon Picker
		 *
		 * @since 6.3
		 *
		 * @return void
		 */
		public function input_admin_enqueue_scripts() {
			acf_localize_data(
				array(
					'iconPickerA11yStrings' => array(
						'noResultsForSearchTerm'       => esc_html__( 'No results found for that search term', 'acf' ),
						'newResultsFoundForSearchTerm' => esc_html__( 'The available icons matching your search query have been updated in the icon picker below.', 'acf' ),
					),
					'iconPickeri10n'        => $this->get_dashicons(),
				)
			);
		}

		/**
		 * Validates the field value before it is saved into the database.
		 *
		 * @since 6.3
		 *
		 * @param  integer $valid The current validation status.
		 * @param  mixed   $value The value of the field.
		 * @param  array   $field The field array holding all the field options.
		 * @param  string  $input The corresponding input name for $_POST value.
		 * @return boolean true If the value is valid, false if not.
		 */
		public function validate_value( $valid, $value, $field, $input ) {
			// If the value is empty, return true. You're allowed to save nothing.
			if ( empty( $value ) && empty( $field['required'] ) ) {
				return true;
			}

			// If the value is not an array, return $valid status.
			if ( ! is_array( $value ) ) {
				return $valid;
			}

			// If the value is an array, but the type is not set, fail validation.
			if ( ! isset( $value['type'] ) ) {
				return __( 'Icon picker requires an icon type.', 'acf' );
			}

			// If the value is an array, but the value is not set, fail validation.
			if ( ! isset( $value['value'] ) ) {
				return __( 'Icon picker requires a value.', 'acf' );
			}

			return true;
		}

		/**
		 * format_value()
		 *
		 * This filter is appied to the $value after it is loaded from the db and before it is returned to the template
		 *
		 * @since 6.3
		 *
		 * @param  mixed   $value   The value which was loaded from the database.
		 * @param  integer $post_id The $post_id from which the value was loaded.
		 * @param  array   $field   The field array holding all the field options.
		 * @return mixed   $value   The modified value.
		 */
		public function format_value( $value, $post_id, $field ) {
			// Handle empty values.
			if ( empty( $value ) ) {
				// Return the default value if there is one.
				if ( isset( $field['default_value'] ) ) {
					return $field['default_value'];
				} else {
					// Otherwise return false.
					return false;
				}
			}

			// If media_library, behave the same as an image field.
			if ( $value['type'] === 'media_library' ) {
				// convert to int
				$value['value'] = intval( $value['value'] );

				// format
				if ( $field['return_format'] === 'string' ) {
					return wp_get_attachment_url( $value['value'] );
				} elseif ( $field['return_format'] === 'array' ) {
					$value['value'] = acf_get_attachment( $value['value'] );
					return $value;
				}
			}

			// If the desired return format is a string
			if ( $field['return_format'] === 'string' ) {
				return $value['value'];
			}

			// If nothing specific matched the return format, just return the value.
			return $value;
		}

		/**
		 * get_dashicons()
		 *
		 * This function will return an array of dashicons.
		 *
		 * @since 6.3
		 *
		 * @return  array $dashicons an array of dashicons.
		 */
		public function get_dashicons() {
			$dashicons = array(
				'dashicons-admin-generic'           => esc_html__( 'Generic icon', 'acf' ),
				'dashicons-admin-appearance'        => esc_html__( 'Appearance icon', 'acf' ),
				'dashicons-admin-collapse'          => esc_html__( 'Collapse icon', 'acf' ),
				'dashicons-admin-comments'          => esc_html__( 'Comments icon', 'acf' ),
				'dashicons-admin-customizer'        => esc_html__( 'Customizer icon', 'acf' ),
				'dashicons-admin-home'              => esc_html__( 'Home icon', 'acf' ),
				'dashicons-admin-links'             => esc_html__( 'Links icon', 'acf' ),
				'dashicons-admin-media'             => esc_html__( 'Media icon', 'acf' ),
				'dashicons-admin-multisite'         => esc_html__( 'Multisite icon', 'acf' ),
				'dashicons-admin-network'           => esc_html__( 'Network icon', 'acf' ),
				'dashicons-admin-page'              => esc_html__( 'Page icon', 'acf' ),
				'dashicons-admin-plugins'           => esc_html__( 'Plugins icon', 'acf' ),
				'dashicons-admin-post'              => esc_html__( 'Post icon', 'acf' ),
				'dashicons-admin-settings'          => esc_html__( 'Settings icon', 'acf' ),
				'dashicons-admin-site'              => esc_html__( 'Site icon', 'acf' ),
				'dashicons-admin-tools'             => esc_html__( 'Tools icon', 'acf' ),
				'dashicons-admin-users'             => esc_html__( 'Users icon', 'acf' ),
				'dashicons-album'                   => esc_html__( 'Album icon', 'acf' ),
				'dashicons-align-center'            => esc_html__( 'Align-center icon', 'acf' ),
				'dashicons-align-left'              => esc_html__( 'Align-left icon', 'acf' ),
				'dashicons-align-none'              => esc_html__( 'Align-none icon', 'acf' ),
				'dashicons-align-right'             => esc_html__( 'Align-right icon', 'acf' ),
				'dashicons-analytics'               => esc_html__( 'Analytics icon', 'acf' ),
				'dashicons-archive'                 => esc_html__( 'Archive icon', 'acf' ),
				'dashicons-arrow-down'              => esc_html__( 'Arrow down icon', 'acf' ),
				'dashicons-arrow-down-alt'          => esc_html__( 'Arrow down-alt icon', 'acf' ),
				'dashicons-arrow-down-alt2'         => esc_html__( 'Arrow down-alt2 icon', 'acf' ),
				'dashicons-arrow-left'              => esc_html__( 'Arrow left icon', 'acf' ),
				'dashicons-arrow-left-alt'          => esc_html__( 'Arrow left-alt icon', 'acf' ),
				'dashicons-arrow-left-alt2'         => esc_html__( 'Arrow left-alt2 icon', 'acf' ),
				'dashicons-arrow-right'             => esc_html__( 'Arrow right icon', 'acf' ),
				'dashicons-arrow-right-alt'         => esc_html__( 'Arrow right-alt icon', 'acf' ),
				'dashicons-arrow-right-alt2'        => esc_html__( 'Arrow right-alt2 icon', 'acf' ),
				'dashicons-arrow-up'                => esc_html__( 'Arrow up icon', 'acf' ),
				'dashicons-arrow-up-alt'            => esc_html__( 'Arrow up-alt icon', 'acf' ),
				'dashicons-arrow-up-alt2'           => esc_html__( 'Arrow up-alt2 icon', 'acf' ),
				'dashicons-art'                     => esc_html__( 'Art icon', 'acf' ),
				'dashicons-awards'                  => esc_html__( 'Awards icon', 'acf' ),
				'dashicons-backup'                  => esc_html__( 'Backup icon', 'acf' ),
				'dashicons-book'                    => esc_html__( 'Book icon', 'acf' ),
				'dashicons-book-alt'                => esc_html__( 'Book alt icon', 'acf' ),
				'dashicons-building'                => esc_html__( 'Building icon', 'acf' ),
				'dashicons-businessman'             => esc_html__( 'Businessman icon', 'acf' ),
				'dashicons-calendar'                => esc_html__( 'Calendar icon', 'acf' ),
				'dashicons-calendar-alt'            => esc_html__( 'Calendar alt icon', 'acf' ),
				'dashicons-camera'                  => esc_html__( 'Camera icon', 'acf' ),
				'dashicons-carrot'                  => esc_html__( 'Carrot icon', 'acf' ),
				'dashicons-cart'                    => esc_html__( 'Cart icon', 'acf' ),
				'dashicons-category'                => esc_html__( 'Category icon', 'acf' ),
				'dashicons-chart-area'              => esc_html__( 'Chart area icon', 'acf' ),
				'dashicons-chart-bar'               => esc_html__( 'Chart bar icon', 'acf' ),
				'dashicons-chart-line'              => esc_html__( 'Chart line icon', 'acf' ),
				'dashicons-chart-pie'               => esc_html__( 'Chart pie icon', 'acf' ),
				'dashicons-clipboard'               => esc_html__( 'Clipboard icon', 'acf' ),
				'dashicons-clock'                   => esc_html__( 'Clock icon', 'acf' ),
				'dashicons-cloud'                   => esc_html__( 'Cloud icon', 'acf' ),
				'dashicons-controls-back'           => esc_html__( 'Controls back icon', 'acf' ),
				'dashicons-controls-forward'        => esc_html__( 'Controls forward icon', 'acf' ),
				'dashicons-controls-pause'          => esc_html__( 'Controls pause icon', 'acf' ),
				'dashicons-controls-play'           => esc_html__( 'Controls play icon', 'acf' ),
				'dashicons-controls-repeat'         => esc_html__( 'Controls repeat icon', 'acf' ),
				'dashicons-controls-skipback'       => esc_html__( 'Controls skipback icon', 'acf' ),
				'dashicons-controls-skipforward'    => esc_html__( 'Controls skipforward icon', 'acf' ),
				'dashicons-controls-volumeoff'      => esc_html__( 'Controls volumeoff icon', 'acf' ),
				'dashicons-controls-volumeon'       => esc_html__( 'Controls volumeon icon', 'acf' ),
				'dashicons-dashboard'               => esc_html__( 'Dashboard icon', 'acf' ),
				'dashicons-desktop'                 => esc_html__( 'Desktop icon', 'acf' ),
				'dashicons-dismiss'                 => esc_html__( 'Dismiss icon', 'acf' ),
				'dashicons-download'                => esc_html__( 'Download icon', 'acf' ),
				'dashicons-edit'                    => esc_html__( 'Edit icon', 'acf' ),
				'dashicons-editor-aligncenter'      => esc_html__( 'aligncenter icon', 'acf' ),
				'dashicons-editor-alignleft'        => esc_html__( 'alignleft icon', 'acf' ),
				'dashicons-editor-alignright'       => esc_html__( 'alignright icon', 'acf' ),
				'dashicons-editor-bold'             => esc_html__( 'Bold icon', 'acf' ),
				'dashicons-editor-break'            => esc_html__( 'Break icon', 'acf' ),
				'dashicons-editor-code'             => esc_html__( 'Code icon', 'acf' ),
				'dashicons-editor-contract'         => esc_html__( 'Contract icon', 'acf' ),
				'dashicons-editor-customchar'       => esc_html__( 'Customchar icon', 'acf' ),
				'dashicons-editor-expand'           => esc_html__( 'Expand icon', 'acf' ),
				'dashicons-editor-help'             => esc_html__( 'Help icon', 'acf' ),
				'dashicons-editor-indent'           => esc_html__( 'Indent icon', 'acf' ),
				'dashicons-editor-insertmore'       => esc_html__( 'Insertmore icon', 'acf' ),
				'dashicons-editor-italic'           => esc_html__( 'Italic icon', 'acf' ),
				'dashicons-editor-justify'          => esc_html__( 'Justify icon', 'acf' ),
				'dashicons-editor-kitchensink'      => esc_html__( 'Kitchensink icon', 'acf' ),
				'dashicons-editor-ol'               => esc_html__( 'Ol icon', 'acf' ),
				'dashicons-editor-outdent'          => esc_html__( 'Outdent icon', 'acf' ),
				'dashicons-editor-paragraph'        => esc_html__( 'Paragraph icon', 'acf' ),
				'dashicons-editor-paste-text'       => esc_html__( 'Paste text icon', 'acf' ),
				'dashicons-editor-paste-word'       => esc_html__( 'Paste word icon', 'acf' ),
				'dashicons-editor-quote'            => esc_html__( 'Quote icon', 'acf' ),
				'dashicons-editor-removeformatting' => esc_html__( 'Removeformatting icon', 'acf' ),
				'dashicons-editor-rtl'              => esc_html__( 'Rtl icon', 'acf' ),
				'dashicons-editor-spellcheck'       => esc_html__( 'Spellcheck icon', 'acf' ),
				'dashicons-editor-strikethrough'    => esc_html__( 'Strikethrough icon', 'acf' ),
				'dashicons-editor-table'            => esc_html__( 'Table icon', 'acf' ),
				'dashicons-editor-textcolor'        => esc_html__( 'Textcolor icon', 'acf' ),
				'dashicons-editor-ul'               => esc_html__( 'Ul icon', 'acf' ),
				'dashicons-editor-underline'        => esc_html__( 'Underline icon', 'acf' ),
				'dashicons-editor-unlink'           => esc_html__( 'Unlink icon', 'acf' ),
				'dashicons-editor-video'            => esc_html__( 'Video icon', 'acf' ),
				'dashicons-email'                   => esc_html__( 'Email icon', 'acf' ),
				'dashicons-email-alt'               => esc_html__( 'Email alt icon', 'acf' ),
				'dashicons-exerpt-view'             => esc_html__( 'Exerpt-view icon', 'acf' ),
				'dashicons-external'                => esc_html__( 'External icon', 'acf' ),
				'dashicons-facebook'                => esc_html__( 'Facebook icon', 'acf' ),
				'dashicons-facebook-alt'            => esc_html__( 'Facebook alt icon', 'acf' ),
				'dashicons-feedback'                => esc_html__( 'Feedback icon', 'acf' ),
				'dashicons-filter'                  => esc_html__( 'Filter icon', 'acf' ),
				'dashicons-flag'                    => esc_html__( 'Flag icon', 'acf' ),
				'dashicons-format-aside'            => esc_html__( 'Format aside icon', 'acf' ),
				'dashicons-format-audio'            => esc_html__( 'Format audio icon', 'acf' ),
				'dashicons-format-chat'             => esc_html__( 'Format chat icon', 'acf' ),
				'dashicons-format-gallery'          => esc_html__( 'Format gallery icon', 'acf' ),
				'dashicons-format-image'            => esc_html__( 'Format image icon', 'acf' ),
				'dashicons-format-quote'            => esc_html__( 'Format quote icon', 'acf' ),
				'dashicons-format-status'           => esc_html__( 'Format status icon', 'acf' ),
				'dashicons-format-video'            => esc_html__( 'Format video icon', 'acf' ),
				'dashicons-forms'                   => esc_html__( 'Forms icon', 'acf' ),
				'dashicons-googleplus'              => esc_html__( 'Googleplus icon', 'acf' ),
				'dashicons-grid-view'               => esc_html__( 'Grid-view icon', 'acf' ),
				'dashicons-groups'                  => esc_html__( 'Groups icon', 'acf' ),
				'dashicons-hammer'                  => esc_html__( 'Hammer icon', 'acf' ),
				'dashicons-heart'                   => esc_html__( 'Heart icon', 'acf' ),
				'dashicons-hidden'                  => esc_html__( 'Hidden icon', 'acf' ),
				'dashicons-id'                      => esc_html__( 'Id icon', 'acf' ),
				'dashicons-id-alt'                  => esc_html__( 'Id-alt icon', 'acf' ),
				'dashicons-image-crop'              => esc_html__( 'Image crop icon', 'acf' ),
				'dashicons-image-filter'            => esc_html__( 'Image filter icon', 'acf' ),
				'dashicons-image-flip-horizontal'   => esc_html__( 'Image flip-horizontal icon', 'acf' ),
				'dashicons-image-flip-vertical'     => esc_html__( 'Image flip-vertical icon', 'acf' ),
				'dashicons-image-rotate'            => esc_html__( 'Image rotate icon', 'acf' ),
				'dashicons-image-rotate-left'       => esc_html__( 'Image rotate-left icon', 'acf' ),
				'dashicons-image-rotate-right'      => esc_html__( 'Image rotate-right icon', 'acf' ),
				'dashicons-images-alt'              => esc_html__( 'Images-alt icon', 'acf' ),
				'dashicons-images-alt2'             => esc_html__( 'Images-alt2 icon', 'acf' ),
				'dashicons-index-card'              => esc_html__( 'Index-card icon', 'acf' ),
				'dashicons-info'                    => esc_html__( 'Info icon', 'acf' ),
				'dashicons-laptop'                  => esc_html__( 'Laptop icon', 'acf' ),
				'dashicons-layout'                  => esc_html__( 'Layout icon', 'acf' ),
				'dashicons-leftright'               => esc_html__( 'Leftright icon', 'acf' ),
				'dashicons-lightbulb'               => esc_html__( 'Lightbulb icon', 'acf' ),
				'dashicons-list-view'               => esc_html__( 'List-view icon', 'acf' ),
				'dashicons-location'                => esc_html__( 'Location icon', 'acf' ),
				'dashicons-location-alt'            => esc_html__( 'Location-alt icon', 'acf' ),
				'dashicons-lock'                    => esc_html__( 'Lock icon', 'acf' ),
				'dashicons-marker'                  => esc_html__( 'Marker icon', 'acf' ),
				'dashicons-media-archive'           => esc_html__( 'Media archive icon', 'acf' ),
				'dashicons-media-audio'             => esc_html__( 'Media audio icon', 'acf' ),
				'dashicons-media-code'              => esc_html__( 'Media code icon', 'acf' ),
				'dashicons-media-default'           => esc_html__( 'Media default icon', 'acf' ),
				'dashicons-media-document'          => esc_html__( 'Media document icon', 'acf' ),
				'dashicons-media-interactive'       => esc_html__( 'Media interactive icon', 'acf' ),
				'dashicons-media-spreadsheet'       => esc_html__( 'Media spreadsheet icon', 'acf' ),
				'dashicons-media-text'              => esc_html__( 'Media text icon', 'acf' ),
				'dashicons-media-video'             => esc_html__( 'Media video icon', 'acf' ),
				'dashicons-megaphone'               => esc_html__( 'Megaphone icon', 'acf' ),
				'dashicons-menu'                    => esc_html__( 'Menu icon', 'acf' ),
				'dashicons-microphone'              => esc_html__( 'Microphone icon', 'acf' ),
				'dashicons-migrate'                 => esc_html__( 'Migrate icon', 'acf' ),
				'dashicons-minus'                   => esc_html__( 'Minus icon', 'acf' ),
				'dashicons-money'                   => esc_html__( 'Money icon', 'acf' ),
				'dashicons-move'                    => esc_html__( 'Move icon', 'acf' ),
				'dashicons-nametag'                 => esc_html__( 'Nametag icon', 'acf' ),
				'dashicons-networking'              => esc_html__( 'Networking icon', 'acf' ),
				'dashicons-no'                      => esc_html__( 'No icon', 'acf' ),
				'dashicons-no-alt'                  => esc_html__( 'No alternative icon', 'acf' ),
				'dashicons-palmtree'                => esc_html__( 'Palmtree icon', 'acf' ),
				'dashicons-paperclip'               => esc_html__( 'Paperclip icon', 'acf' ),
				'dashicons-performance'             => esc_html__( 'Performance icon', 'acf' ),
				'dashicons-phone'                   => esc_html__( 'Phone icon', 'acf' ),
				'dashicons-playlist-audio'          => esc_html__( 'Playlist-audio icon', 'acf' ),
				'dashicons-playlist-video'          => esc_html__( 'Playlist-video icon', 'acf' ),
				'dashicons-plus'                    => esc_html__( 'Plus icon', 'acf' ),
				'dashicons-plus-alt'                => esc_html__( 'Plus-alt icon', 'acf' ),
				'dashicons-portfolio'               => esc_html__( 'Portfolio icon', 'acf' ),
				'dashicons-post-status'             => esc_html__( 'Post-status icon', 'acf' ),
				'dashicons-pressthis'               => esc_html__( 'Pressthis icon', 'acf' ),
				'dashicons-products'                => esc_html__( 'Products icon', 'acf' ),
				'dashicons-randomize'               => esc_html__( 'Randomize icon', 'acf' ),
				'dashicons-redo'                    => esc_html__( 'Redo icon', 'acf' ),
				'dashicons-rss'                     => esc_html__( 'Rss icon', 'acf' ),
				'dashicons-schedule'                => esc_html__( 'Schedule icon', 'acf' ),
				'dashicons-screenoptions'           => esc_html__( 'Screenoptions icon', 'acf' ),
				'dashicons-search'                  => esc_html__( 'Search icon', 'acf' ),
				'dashicons-share'                   => esc_html__( 'Share icon', 'acf' ),
				'dashicons-share-alt'               => esc_html__( 'Share-alt icon', 'acf' ),
				'dashicons-share-alt2'              => esc_html__( 'Share-alt2 icon', 'acf' ),
				'dashicons-shield'                  => esc_html__( 'Shield icon', 'acf' ),
				'dashicons-shield-alt'              => esc_html__( 'Shield-alt icon', 'acf' ),
				'dashicons-slides'                  => esc_html__( 'Slides icon', 'acf' ),
				'dashicons-smartphone'              => esc_html__( 'Smartphone icon', 'acf' ),
				'dashicons-smiley'                  => esc_html__( 'Smiley icon', 'acf' ),
				'dashicons-sort'                    => esc_html__( 'Sort icon', 'acf' ),
				'dashicons-sos'                     => esc_html__( 'Sos icon', 'acf' ),
				'dashicons-star-empty'              => esc_html__( 'Star-empty icon', 'acf' ),
				'dashicons-star-filled'             => esc_html__( 'Star-filled icon', 'acf' ),
				'dashicons-star-half'               => esc_html__( 'Star-half icon', 'acf' ),
				'dashicons-sticky'                  => esc_html__( 'Sticky icon', 'acf' ),
				'dashicons-store'                   => esc_html__( 'Store icon', 'acf' ),
				'dashicons-tablet'                  => esc_html__( 'Tablet icon', 'acf' ),
				'dashicons-tag'                     => esc_html__( 'Tag icon', 'acf' ),
				'dashicons-tagcloud'                => esc_html__( 'Tagcloud icon', 'acf' ),
				'dashicons-testimonial'             => esc_html__( 'Testimonial icon', 'acf' ),
				'dashicons-text'                    => esc_html__( 'Text icon', 'acf' ),
				'dashicons-thumbs-down'             => esc_html__( 'Thumbs-down icon', 'acf' ),
				'dashicons-thumbs-up'               => esc_html__( 'Thumbs-up icon', 'acf' ),
				'dashicons-tickets'                 => esc_html__( 'Tickets icon', 'acf' ),
				'dashicons-tickets-alt'             => esc_html__( 'Tickets alternative icon', 'acf' ),
				'dashicons-translation'             => esc_html__( 'Translation icon', 'acf' ),
				'dashicons-trash'                   => esc_html__( 'Trash icon', 'acf' ),
				'dashicons-twitter'                 => esc_html__( 'Twitter icon', 'acf' ),
				'dashicons-undo'                    => esc_html__( 'Undo icon', 'acf' ),
				'dashicons-universal-access'        => esc_html__( 'Universal access icon', 'acf' ),
				'dashicons-universal-access-alt'    => esc_html__( 'Universal access alternative icon', 'acf' ),
				'dashicons-unlock'                  => esc_html__( 'Unlock icon', 'acf' ),
				'dashicons-update'                  => esc_html__( 'Update icon', 'acf' ),
				'dashicons-upload'                  => esc_html__( 'Upload icon', 'acf' ),
				'dashicons-vault'                   => esc_html__( 'Vault icon', 'acf' ),
				'dashicons-video-alt'               => esc_html__( 'Video-alt icon', 'acf' ),
				'dashicons-video-alt2'              => esc_html__( 'Video-alt2 icon', 'acf' ),
				'dashicons-video-alt3'              => esc_html__( 'Video-alt3 icon', 'acf' ),
				'dashicons-visibility'              => esc_html__( 'Visibility icon', 'acf' ),
				'dashicons-warning'                 => esc_html__( 'Warning icon', 'acf' ),
				'dashicons-welcome-add-page'        => esc_html__( 'Welcome add-page icon', 'acf' ),
				'dashicons-welcome-comments'        => esc_html__( 'Welcome comments icon', 'acf' ),
				'dashicons-welcome-learn-more'      => esc_html__( 'Welcome learn-more icon', 'acf' ),
				'dashicons-welcome-view-site'       => esc_html__( 'Welcome view-site icon', 'acf' ),
				'dashicons-welcome-widgets-menus'   => esc_html__( 'Welcome widgets-menus icon', 'acf' ),
				'dashicons-welcome-write-blog'      => esc_html__( 'Welcome write-blog icon', 'acf' ),
				'dashicons-wordpress'               => esc_html__( 'Wordpress icon', 'acf' ),
				'dashicons-wordpress-alt'           => esc_html__( 'Wordpress-alt icon', 'acf' ),
				'dashicons-yes'                     => esc_html__( 'Yes icon', 'acf' ),
			);

			return apply_filters( 'acf/fields/icon_picker/dashicons', $dashicons );
		}

		/**
		 * Returns the schema used by the REST API.
		 *
		 * @since 6.3
		 *
		 * @param array $field The main field array.
		 * @return array
		 */
		public function get_rest_schema( array $field ): array {
			return array(
				'type'       => array( 'object', 'null' ),
				'required'   => ! empty( $field['required'] ),
				'properties' => array(
					'type'  => array(
						'description' => esc_html__( 'The type of icon to save.', 'acf' ),
						'type'        => array( 'string' ),
						'required'    => true,
						'enum'        => array_keys( $this->get_tabs() ),
					),
					'value' => array(
						'description' => esc_html__( 'The value of icon to save.', 'acf' ),
						'type'        => array( 'string', 'int' ),
						'required'    => true,
					),
				),
			);
		}

		/**
		 * Validates a value sent via the REST API.
		 *
		 * @since 6.3
		 *
		 * @param boolean    $valid The current validity boolean.
		 * @param array|null $value The value of the field.
		 * @param array      $field The main field array.
		 * @return boolean|WP_Error
		 */
		public function validate_rest_value( $valid, $value, $field ) {
			if ( is_null( $value ) ) {
				if ( ! empty( $field['required'] ) ) {
					return new WP_Error(
						'rest_property_required',
						/* translators: %s - field name */
						sprintf( __( '%s is a required property of acf.', 'acf' ), $field['name'] )
					);
				} else {
					return $valid;
				}
			}

			if ( ! empty( $value['type'] ) && 'media_library' === $value['type'] ) {
				$param = sprintf( '%s[%s][value]', $field['prefix'], $field['name'] );
				$data  = array(
					'param' => $param,
					'value' => (int) $value['value'],
				);

				if ( ! is_int( $value['value'] ) || 'attachment' !== get_post_type( $value['value'] ) ) {
					/* translators: %s - field/param name */
					$error = sprintf( __( '%s requires a valid attachment ID when type is set to media_library.', 'acf' ), $param );
					return new WP_Error( 'rest_invalid_param', $error, $data );
				}
			}

			return $valid;
		}
	}

	acf_register_field_type( 'acf_field_icon_picker' );
endif;
