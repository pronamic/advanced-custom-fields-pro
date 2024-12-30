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

			if ( ! is_array( $field['tabs'] ) ) {
				$field['tabs'] = array();
			}

			$tabs  = $this->get_tabs();
			$shown = array_filter(
				$tabs,
				function ( $tab ) use ( $field ) {
					return in_array( $tab, $field['tabs'], true );
				},
				ARRAY_FILTER_USE_KEY
			);

			foreach ( $shown as $name => $label ) {
				if ( count( $shown ) > 1 ) {
					acf_render_field_wrap(
						array(
							'type'           => 'tab',
							'label'          => $label,
							'key'            => 'acf_icon_picker_tabs',
							'selected'       => $name === $field['value']['type'],
							'unique_tab_key' => $name,
						)
					);
				}

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
								<?php
								$button_style = 'display: none;';

								if ( in_array( $field['value']['type'], array( 'media_library', 'dashicons' ), true ) && ! empty( $field['value']['value'] ) ) {
									$button_style = '';
								}
								?>
								<button
									aria-label="<?php esc_attr_e( 'Click to change the icon in the Media Library', 'acf' ); ?>"
									class="acf-icon-picker-media-library-preview"
									style="<?php echo esc_attr( $button_style ); ?>"
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
				'dashicons-admin-appearance'          => esc_html__( 'Appearance Icon', 'acf' ),
				'dashicons-admin-collapse'            => esc_html__( 'Collapse Icon', 'acf' ),
				'dashicons-admin-comments'            => esc_html__( 'Comments Icon', 'acf' ),
				'dashicons-admin-customizer'          => esc_html__( 'Customizer Icon', 'acf' ),
				'dashicons-admin-generic'             => esc_html__( 'Generic Icon', 'acf' ),
				'dashicons-admin-home'                => esc_html__( 'Home Icon', 'acf' ),
				'dashicons-admin-links'               => esc_html__( 'Links Icon', 'acf' ),
				'dashicons-admin-media'               => esc_html__( 'Media Icon', 'acf' ),
				'dashicons-admin-multisite'           => esc_html__( 'Multisite Icon', 'acf' ),
				'dashicons-admin-network'             => esc_html__( 'Network Icon', 'acf' ),
				'dashicons-admin-page'                => esc_html__( 'Page Icon', 'acf' ),
				'dashicons-admin-plugins'             => esc_html__( 'Plugins Icon', 'acf' ),
				'dashicons-admin-post'                => esc_html__( 'Post Icon', 'acf' ),
				'dashicons-admin-settings'            => esc_html__( 'Settings Icon', 'acf' ),
				'dashicons-admin-site'                => esc_html__( 'Site Icon', 'acf' ),
				'dashicons-admin-site-alt'            => esc_html__( 'Site (alt) Icon', 'acf' ),
				'dashicons-admin-site-alt2'           => esc_html__( 'Site (alt2) Icon', 'acf' ),
				'dashicons-admin-site-alt3'           => esc_html__( 'Site (alt3) Icon', 'acf' ),
				'dashicons-admin-tools'               => esc_html__( 'Tools Icon', 'acf' ),
				'dashicons-admin-users'               => esc_html__( 'Users Icon', 'acf' ),
				'dashicons-airplane'                  => esc_html__( 'Airplane Icon', 'acf' ),
				'dashicons-album'                     => esc_html__( 'Album Icon', 'acf' ),
				'dashicons-align-center'              => esc_html__( 'Align Center Icon', 'acf' ),
				'dashicons-align-full-width'          => esc_html__( 'Align Full Width Icon', 'acf' ),
				'dashicons-align-left'                => esc_html__( 'Align Left Icon', 'acf' ),
				'dashicons-align-none'                => esc_html__( 'Align None Icon', 'acf' ),
				'dashicons-align-pull-left'           => esc_html__( 'Align Pull Left Icon', 'acf' ),
				'dashicons-align-pull-right'          => esc_html__( 'Align Pull Right Icon', 'acf' ),
				'dashicons-align-right'               => esc_html__( 'Align Right Icon', 'acf' ),
				'dashicons-align-wide'                => esc_html__( 'Align Wide Icon', 'acf' ),
				'dashicons-amazon'                    => esc_html__( 'Amazon Icon', 'acf' ),
				'dashicons-analytics'                 => esc_html__( 'Analytics Icon', 'acf' ),
				'dashicons-archive'                   => esc_html__( 'Archive Icon', 'acf' ),
				'dashicons-arrow-down'                => esc_html__( 'Arrow Down Icon', 'acf' ),
				'dashicons-arrow-down-alt'            => esc_html__( 'Arrow Down (alt) Icon', 'acf' ),
				'dashicons-arrow-down-alt2'           => esc_html__( 'Arrow Down (alt2) Icon', 'acf' ),
				'dashicons-arrow-left'                => esc_html__( 'Arrow Left Icon', 'acf' ),
				'dashicons-arrow-left-alt'            => esc_html__( 'Arrow Left (alt) Icon', 'acf' ),
				'dashicons-arrow-left-alt2'           => esc_html__( 'Arrow Left (alt2) Icon', 'acf' ),
				'dashicons-arrow-right'               => esc_html__( 'Arrow Right Icon', 'acf' ),
				'dashicons-arrow-right-alt'           => esc_html__( 'Arrow Right (alt) Icon', 'acf' ),
				'dashicons-arrow-right-alt2'          => esc_html__( 'Arrow Right (alt2) Icon', 'acf' ),
				'dashicons-arrow-up'                  => esc_html__( 'Arrow Up Icon', 'acf' ),
				'dashicons-arrow-up-alt'              => esc_html__( 'Arrow Up (alt) Icon', 'acf' ),
				'dashicons-arrow-up-alt2'             => esc_html__( 'Arrow Up (alt2) Icon', 'acf' ),
				'dashicons-art'                       => esc_html__( 'Art Icon', 'acf' ),
				'dashicons-awards'                    => esc_html__( 'Awards Icon', 'acf' ),
				'dashicons-backup'                    => esc_html__( 'Backup Icon', 'acf' ),
				'dashicons-bank'                      => esc_html__( 'Bank Icon', 'acf' ),
				'dashicons-beer'                      => esc_html__( 'Beer Icon', 'acf' ),
				'dashicons-bell'                      => esc_html__( 'Bell Icon', 'acf' ),
				'dashicons-block-default'             => esc_html__( 'Block Default Icon', 'acf' ),
				'dashicons-book'                      => esc_html__( 'Book Icon', 'acf' ),
				'dashicons-book-alt'                  => esc_html__( 'Book (alt) Icon', 'acf' ),
				'dashicons-buddicons-activity'        => esc_html__( 'Activity Icon', 'acf' ),
				'dashicons-buddicons-bbpress-logo'    => esc_html__( 'bbPress Icon', 'acf' ),
				'dashicons-buddicons-buddypress-logo' => esc_html__( 'BuddyPress Icon', 'acf' ),
				'dashicons-buddicons-community'       => esc_html__( 'Community Icon', 'acf' ),
				'dashicons-buddicons-forums'          => esc_html__( 'Forums Icon', 'acf' ),
				'dashicons-buddicons-friends'         => esc_html__( 'Friends Icon', 'acf' ),
				'dashicons-buddicons-groups'          => esc_html__( 'Groups Icon', 'acf' ),
				'dashicons-buddicons-pm'              => esc_html__( 'PM Icon', 'acf' ),
				'dashicons-buddicons-replies'         => esc_html__( 'Replies Icon', 'acf' ),
				'dashicons-buddicons-topics'          => esc_html__( 'Topics Icon', 'acf' ),
				'dashicons-buddicons-tracking'        => esc_html__( 'Tracking Icon', 'acf' ),
				'dashicons-building'                  => esc_html__( 'Building Icon', 'acf' ),
				'dashicons-businessman'               => esc_html__( 'Businessman Icon', 'acf' ),
				'dashicons-businessperson'            => esc_html__( 'Businessperson Icon', 'acf' ),
				'dashicons-businesswoman'             => esc_html__( 'Businesswoman Icon', 'acf' ),
				'dashicons-button'                    => esc_html__( 'Button Icon', 'acf' ),
				'dashicons-calculator'                => esc_html__( 'Calculator Icon', 'acf' ),
				'dashicons-calendar'                  => esc_html__( 'Calendar Icon', 'acf' ),
				'dashicons-calendar-alt'              => esc_html__( 'Calendar (alt) Icon', 'acf' ),
				'dashicons-camera'                    => esc_html__( 'Camera Icon', 'acf' ),
				'dashicons-camera-alt'                => esc_html__( 'Camera (alt) Icon', 'acf' ),
				'dashicons-car'                       => esc_html__( 'Car Icon', 'acf' ),
				'dashicons-carrot'                    => esc_html__( 'Carrot Icon', 'acf' ),
				'dashicons-cart'                      => esc_html__( 'Cart Icon', 'acf' ),
				'dashicons-category'                  => esc_html__( 'Category Icon', 'acf' ),
				'dashicons-chart-area'                => esc_html__( 'Chart Area Icon', 'acf' ),
				'dashicons-chart-bar'                 => esc_html__( 'Chart Bar Icon', 'acf' ),
				'dashicons-chart-line'                => esc_html__( 'Chart Line Icon', 'acf' ),
				'dashicons-chart-pie'                 => esc_html__( 'Chart Pie Icon', 'acf' ),
				'dashicons-clipboard'                 => esc_html__( 'Clipboard Icon', 'acf' ),
				'dashicons-clock'                     => esc_html__( 'Clock Icon', 'acf' ),
				'dashicons-cloud'                     => esc_html__( 'Cloud Icon', 'acf' ),
				'dashicons-cloud-saved'               => esc_html__( 'Cloud Saved Icon', 'acf' ),
				'dashicons-cloud-upload'              => esc_html__( 'Cloud Upload Icon', 'acf' ),
				'dashicons-code-standards'            => esc_html__( 'Code Standards Icon', 'acf' ),
				'dashicons-coffee'                    => esc_html__( 'Coffee Icon', 'acf' ),
				'dashicons-color-picker'              => esc_html__( 'Color Picker Icon', 'acf' ),
				'dashicons-columns'                   => esc_html__( 'Columns Icon', 'acf' ),
				'dashicons-controls-back'             => esc_html__( 'Back Icon', 'acf' ),
				'dashicons-controls-forward'          => esc_html__( 'Forward Icon', 'acf' ),
				'dashicons-controls-pause'            => esc_html__( 'Pause Icon', 'acf' ),
				'dashicons-controls-play'             => esc_html__( 'Play Icon', 'acf' ),
				'dashicons-controls-repeat'           => esc_html__( 'Repeat Icon', 'acf' ),
				'dashicons-controls-skipback'         => esc_html__( 'Skip Back Icon', 'acf' ),
				'dashicons-controls-skipforward'      => esc_html__( 'Skip Forward Icon', 'acf' ),
				'dashicons-controls-volumeoff'        => esc_html__( 'Volume Off Icon', 'acf' ),
				'dashicons-controls-volumeon'         => esc_html__( 'Volume On Icon', 'acf' ),
				'dashicons-cover-image'               => esc_html__( 'Cover Image Icon', 'acf' ),
				'dashicons-dashboard'                 => esc_html__( 'Dashboard Icon', 'acf' ),
				'dashicons-database'                  => esc_html__( 'Database Icon', 'acf' ),
				'dashicons-database-add'              => esc_html__( 'Database Add Icon', 'acf' ),
				'dashicons-database-export'           => esc_html__( 'Database Export Icon', 'acf' ),
				'dashicons-database-import'           => esc_html__( 'Database Import Icon', 'acf' ),
				'dashicons-database-remove'           => esc_html__( 'Database Remove Icon', 'acf' ),
				'dashicons-database-view'             => esc_html__( 'Database View Icon', 'acf' ),
				'dashicons-desktop'                   => esc_html__( 'Desktop Icon', 'acf' ),
				'dashicons-dismiss'                   => esc_html__( 'Dismiss Icon', 'acf' ),
				'dashicons-download'                  => esc_html__( 'Download Icon', 'acf' ),
				'dashicons-drumstick'                 => esc_html__( 'Drumstick Icon', 'acf' ),
				'dashicons-edit'                      => esc_html__( 'Edit Icon', 'acf' ),
				'dashicons-edit-large'                => esc_html__( 'Edit Large Icon', 'acf' ),
				'dashicons-edit-page'                 => esc_html__( 'Edit Page Icon', 'acf' ),
				'dashicons-editor-aligncenter'        => esc_html__( 'Align Center Icon', 'acf' ),
				'dashicons-editor-alignleft'          => esc_html__( 'Align Left Icon', 'acf' ),
				'dashicons-editor-alignright'         => esc_html__( 'Align Right Icon', 'acf' ),
				'dashicons-editor-bold'               => esc_html__( 'Bold Icon', 'acf' ),
				'dashicons-editor-break'              => esc_html__( 'Break Icon', 'acf' ),
				'dashicons-editor-code'               => esc_html__( 'Code Icon', 'acf' ),
				'dashicons-editor-contract'           => esc_html__( 'Contract Icon', 'acf' ),
				'dashicons-editor-customchar'         => esc_html__( 'Custom Character Icon', 'acf' ),
				'dashicons-editor-expand'             => esc_html__( 'Expand Icon', 'acf' ),
				'dashicons-editor-help'               => esc_html__( 'Help Icon', 'acf' ),
				'dashicons-editor-indent'             => esc_html__( 'Indent Icon', 'acf' ),
				'dashicons-editor-insertmore'         => esc_html__( 'Insert More Icon', 'acf' ),
				'dashicons-editor-italic'             => esc_html__( 'Italic Icon', 'acf' ),
				'dashicons-editor-justify'            => esc_html__( 'Justify Icon', 'acf' ),
				'dashicons-editor-kitchensink'        => esc_html__( 'Kitchen Sink Icon', 'acf' ),
				'dashicons-editor-ltr'                => esc_html__( 'LTR Icon', 'acf' ),
				'dashicons-editor-ol'                 => esc_html__( 'Ordered List Icon', 'acf' ),
				'dashicons-editor-ol-rtl'             => esc_html__( 'Ordered List RTL Icon', 'acf' ),
				'dashicons-editor-outdent'            => esc_html__( 'Outdent Icon', 'acf' ),
				'dashicons-editor-paragraph'          => esc_html__( 'Paragraph Icon', 'acf' ),
				'dashicons-editor-paste-text'         => esc_html__( 'Paste Text Icon', 'acf' ),
				'dashicons-editor-paste-word'         => esc_html__( 'Paste Word Icon', 'acf' ),
				'dashicons-editor-quote'              => esc_html__( 'Quote Icon', 'acf' ),
				'dashicons-editor-removeformatting'   => esc_html__( 'Remove Formatting Icon', 'acf' ),
				'dashicons-editor-rtl'                => esc_html__( 'RTL Icon', 'acf' ),
				'dashicons-editor-spellcheck'         => esc_html__( 'Spellcheck Icon', 'acf' ),
				'dashicons-editor-strikethrough'      => esc_html__( 'Strikethrough Icon', 'acf' ),
				'dashicons-editor-table'              => esc_html__( 'Table Icon', 'acf' ),
				'dashicons-editor-textcolor'          => esc_html__( 'Text Color Icon', 'acf' ),
				'dashicons-editor-ul'                 => esc_html__( 'Unordered List Icon', 'acf' ),
				'dashicons-editor-underline'          => esc_html__( 'Underline Icon', 'acf' ),
				'dashicons-editor-unlink'             => esc_html__( 'Unlink Icon', 'acf' ),
				'dashicons-editor-video'              => esc_html__( 'Video Icon', 'acf' ),
				'dashicons-ellipsis'                  => esc_html__( 'Ellipsis Icon', 'acf' ),
				'dashicons-email'                     => esc_html__( 'Email Icon', 'acf' ),
				'dashicons-email-alt'                 => esc_html__( 'Email (alt) Icon', 'acf' ),
				'dashicons-email-alt2'                => esc_html__( 'Email (alt2) Icon', 'acf' ),
				'dashicons-embed-audio'               => esc_html__( 'Embed Audio Icon', 'acf' ),
				'dashicons-embed-generic'             => esc_html__( 'Embed Generic Icon', 'acf' ),
				'dashicons-embed-photo'               => esc_html__( 'Embed Photo Icon', 'acf' ),
				'dashicons-embed-post'                => esc_html__( 'Embed Post Icon', 'acf' ),
				'dashicons-embed-video'               => esc_html__( 'Embed Video Icon', 'acf' ),
				'dashicons-excerpt-view'              => esc_html__( 'Excerpt View Icon', 'acf' ),
				'dashicons-exit'                      => esc_html__( 'Exit Icon', 'acf' ),
				'dashicons-external'                  => esc_html__( 'External Icon', 'acf' ),
				'dashicons-facebook'                  => esc_html__( 'Facebook Icon', 'acf' ),
				'dashicons-facebook-alt'              => esc_html__( 'Facebook (alt) Icon', 'acf' ),
				'dashicons-feedback'                  => esc_html__( 'Feedback Icon', 'acf' ),
				'dashicons-filter'                    => esc_html__( 'Filter Icon', 'acf' ),
				'dashicons-flag'                      => esc_html__( 'Flag Icon', 'acf' ),
				'dashicons-food'                      => esc_html__( 'Food Icon', 'acf' ),
				'dashicons-format-aside'              => esc_html__( 'Aside Icon', 'acf' ),
				'dashicons-format-audio'              => esc_html__( 'Audio Icon', 'acf' ),
				'dashicons-format-chat'               => esc_html__( 'Chat Icon', 'acf' ),
				'dashicons-format-gallery'            => esc_html__( 'Gallery Icon', 'acf' ),
				'dashicons-format-image'              => esc_html__( 'Image Icon', 'acf' ),
				'dashicons-format-quote'              => esc_html__( 'Quote Icon', 'acf' ),
				'dashicons-format-status'             => esc_html__( 'Status Icon', 'acf' ),
				'dashicons-format-video'              => esc_html__( 'Video Icon', 'acf' ),
				'dashicons-forms'                     => esc_html__( 'Forms Icon', 'acf' ),
				'dashicons-fullscreen-alt'            => esc_html__( 'Fullscreen (alt) Icon', 'acf' ),
				'dashicons-fullscreen-exit-alt'       => esc_html__( 'Fullscreen Exit (alt) Icon', 'acf' ),
				'dashicons-games'                     => esc_html__( 'Games Icon', 'acf' ),
				'dashicons-google'                    => esc_html__( 'Google Icon', 'acf' ),
				'dashicons-grid-view'                 => esc_html__( 'Grid View Icon', 'acf' ),
				'dashicons-groups'                    => esc_html__( 'Groups Icon', 'acf' ),
				'dashicons-hammer'                    => esc_html__( 'Hammer Icon', 'acf' ),
				'dashicons-heading'                   => esc_html__( 'Heading Icon', 'acf' ),
				'dashicons-heart'                     => esc_html__( 'Heart Icon', 'acf' ),
				'dashicons-hidden'                    => esc_html__( 'Hidden Icon', 'acf' ),
				'dashicons-hourglass'                 => esc_html__( 'Hourglass Icon', 'acf' ),
				'dashicons-html'                      => esc_html__( 'HTML Icon', 'acf' ),
				'dashicons-id'                        => esc_html__( 'ID Icon', 'acf' ),
				'dashicons-id-alt'                    => esc_html__( 'ID (alt) Icon', 'acf' ),
				'dashicons-image-crop'                => esc_html__( 'Crop Icon', 'acf' ),
				'dashicons-image-filter'              => esc_html__( 'Filter Icon', 'acf' ),
				'dashicons-image-flip-horizontal'     => esc_html__( 'Flip Horizontal Icon', 'acf' ),
				'dashicons-image-flip-vertical'       => esc_html__( 'Flip Vertical Icon', 'acf' ),
				'dashicons-image-rotate'              => esc_html__( 'Rotate Icon', 'acf' ),
				'dashicons-image-rotate-left'         => esc_html__( 'Rotate Left Icon', 'acf' ),
				'dashicons-image-rotate-right'        => esc_html__( 'Rotate Right Icon', 'acf' ),
				'dashicons-images-alt'                => esc_html__( 'Images (alt) Icon', 'acf' ),
				'dashicons-images-alt2'               => esc_html__( 'Images (alt2) Icon', 'acf' ),
				'dashicons-index-card'                => esc_html__( 'Index Card Icon', 'acf' ),
				'dashicons-info'                      => esc_html__( 'Info Icon', 'acf' ),
				'dashicons-info-outline'              => esc_html__( 'Info Outline Icon', 'acf' ),
				'dashicons-insert'                    => esc_html__( 'Insert Icon', 'acf' ),
				'dashicons-insert-after'              => esc_html__( 'Insert After Icon', 'acf' ),
				'dashicons-insert-before'             => esc_html__( 'Insert Before Icon', 'acf' ),
				'dashicons-instagram'                 => esc_html__( 'Instagram Icon', 'acf' ),
				'dashicons-laptop'                    => esc_html__( 'Laptop Icon', 'acf' ),
				'dashicons-layout'                    => esc_html__( 'Layout Icon', 'acf' ),
				'dashicons-leftright'                 => esc_html__( 'Left Right Icon', 'acf' ),
				'dashicons-lightbulb'                 => esc_html__( 'Lightbulb Icon', 'acf' ),
				'dashicons-linkedin'                  => esc_html__( 'LinkedIn Icon', 'acf' ),
				'dashicons-list-view'                 => esc_html__( 'List View Icon', 'acf' ),
				'dashicons-location'                  => esc_html__( 'Location Icon', 'acf' ),
				'dashicons-location-alt'              => esc_html__( 'Location (alt) Icon', 'acf' ),
				'dashicons-lock'                      => esc_html__( 'Lock Icon', 'acf' ),
				'dashicons-marker'                    => esc_html__( 'Marker Icon', 'acf' ),
				'dashicons-media-archive'             => esc_html__( 'Archive Icon', 'acf' ),
				'dashicons-media-audio'               => esc_html__( 'Audio Icon', 'acf' ),
				'dashicons-media-code'                => esc_html__( 'Code Icon', 'acf' ),
				'dashicons-media-default'             => esc_html__( 'Default Icon', 'acf' ),
				'dashicons-media-document'            => esc_html__( 'Document Icon', 'acf' ),
				'dashicons-media-interactive'         => esc_html__( 'Interactive Icon', 'acf' ),
				'dashicons-media-spreadsheet'         => esc_html__( 'Spreadsheet Icon', 'acf' ),
				'dashicons-media-text'                => esc_html__( 'Text Icon', 'acf' ),
				'dashicons-media-video'               => esc_html__( 'Video Icon', 'acf' ),
				'dashicons-megaphone'                 => esc_html__( 'Megaphone Icon', 'acf' ),
				'dashicons-menu'                      => esc_html__( 'Menu Icon', 'acf' ),
				'dashicons-menu-alt'                  => esc_html__( 'Menu (alt) Icon', 'acf' ),
				'dashicons-menu-alt2'                 => esc_html__( 'Menu (alt2) Icon', 'acf' ),
				'dashicons-menu-alt3'                 => esc_html__( 'Menu (alt3) Icon', 'acf' ),
				'dashicons-microphone'                => esc_html__( 'Microphone Icon', 'acf' ),
				'dashicons-migrate'                   => esc_html__( 'Migrate Icon', 'acf' ),
				'dashicons-minus'                     => esc_html__( 'Minus Icon', 'acf' ),
				'dashicons-money'                     => esc_html__( 'Money Icon', 'acf' ),
				'dashicons-money-alt'                 => esc_html__( 'Money (alt) Icon', 'acf' ),
				'dashicons-move'                      => esc_html__( 'Move Icon', 'acf' ),
				'dashicons-nametag'                   => esc_html__( 'Nametag Icon', 'acf' ),
				'dashicons-networking'                => esc_html__( 'Networking Icon', 'acf' ),
				'dashicons-no'                        => esc_html__( 'No Icon', 'acf' ),
				'dashicons-no-alt'                    => esc_html__( 'No (alt) Icon', 'acf' ),
				'dashicons-open-folder'               => esc_html__( 'Open Folder Icon', 'acf' ),
				'dashicons-palmtree'                  => esc_html__( 'Palm Tree Icon', 'acf' ),
				'dashicons-paperclip'                 => esc_html__( 'Paperclip Icon', 'acf' ),
				'dashicons-pdf'                       => esc_html__( 'PDF Icon', 'acf' ),
				'dashicons-performance'               => esc_html__( 'Performance Icon', 'acf' ),
				'dashicons-pets'                      => esc_html__( 'Pets Icon', 'acf' ),
				'dashicons-phone'                     => esc_html__( 'Phone Icon', 'acf' ),
				'dashicons-pinterest'                 => esc_html__( 'Pinterest Icon', 'acf' ),
				'dashicons-playlist-audio'            => esc_html__( 'Playlist Audio Icon', 'acf' ),
				'dashicons-playlist-video'            => esc_html__( 'Playlist Video Icon', 'acf' ),
				'dashicons-plugins-checked'           => esc_html__( 'Plugins Checked Icon', 'acf' ),
				'dashicons-plus'                      => esc_html__( 'Plus Icon', 'acf' ),
				'dashicons-plus-alt'                  => esc_html__( 'Plus (alt) Icon', 'acf' ),
				'dashicons-plus-alt2'                 => esc_html__( 'Plus (alt2) Icon', 'acf' ),
				'dashicons-podio'                     => esc_html__( 'Podio Icon', 'acf' ),
				'dashicons-portfolio'                 => esc_html__( 'Portfolio Icon', 'acf' ),
				'dashicons-post-status'               => esc_html__( 'Post Status Icon', 'acf' ),
				'dashicons-pressthis'                 => esc_html__( 'Pressthis Icon', 'acf' ),
				'dashicons-printer'                   => esc_html__( 'Printer Icon', 'acf' ),
				'dashicons-privacy'                   => esc_html__( 'Privacy Icon', 'acf' ),
				'dashicons-products'                  => esc_html__( 'Products Icon', 'acf' ),
				'dashicons-randomize'                 => esc_html__( 'Randomize Icon', 'acf' ),
				'dashicons-reddit'                    => esc_html__( 'Reddit Icon', 'acf' ),
				'dashicons-redo'                      => esc_html__( 'Redo Icon', 'acf' ),
				'dashicons-remove'                    => esc_html__( 'Remove Icon', 'acf' ),
				'dashicons-rest-api'                  => esc_html__( 'REST API Icon', 'acf' ),
				'dashicons-rss'                       => esc_html__( 'RSS Icon', 'acf' ),
				'dashicons-saved'                     => esc_html__( 'Saved Icon', 'acf' ),
				'dashicons-schedule'                  => esc_html__( 'Schedule Icon', 'acf' ),
				'dashicons-screenoptions'             => esc_html__( 'Screen Options Icon', 'acf' ),
				'dashicons-search'                    => esc_html__( 'Search Icon', 'acf' ),
				'dashicons-share'                     => esc_html__( 'Share Icon', 'acf' ),
				'dashicons-share-alt'                 => esc_html__( 'Share (alt) Icon', 'acf' ),
				'dashicons-share-alt2'                => esc_html__( 'Share (alt2) Icon', 'acf' ),
				'dashicons-shield'                    => esc_html__( 'Shield Icon', 'acf' ),
				'dashicons-shield-alt'                => esc_html__( 'Shield (alt) Icon', 'acf' ),
				'dashicons-shortcode'                 => esc_html__( 'Shortcode Icon', 'acf' ),
				'dashicons-slides'                    => esc_html__( 'Slides Icon', 'acf' ),
				'dashicons-smartphone'                => esc_html__( 'Smartphone Icon', 'acf' ),
				'dashicons-smiley'                    => esc_html__( 'Smiley Icon', 'acf' ),
				'dashicons-sort'                      => esc_html__( 'Sort Icon', 'acf' ),
				'dashicons-sos'                       => esc_html__( 'Sos Icon', 'acf' ),
				'dashicons-spotify'                   => esc_html__( 'Spotify Icon', 'acf' ),
				'dashicons-star-empty'                => esc_html__( 'Star Empty Icon', 'acf' ),
				'dashicons-star-filled'               => esc_html__( 'Star Filled Icon', 'acf' ),
				'dashicons-star-half'                 => esc_html__( 'Star Half Icon', 'acf' ),
				'dashicons-sticky'                    => esc_html__( 'Sticky Icon', 'acf' ),
				'dashicons-store'                     => esc_html__( 'Store Icon', 'acf' ),
				'dashicons-superhero'                 => esc_html__( 'Superhero Icon', 'acf' ),
				'dashicons-superhero-alt'             => esc_html__( 'Superhero (alt) Icon', 'acf' ),
				'dashicons-table-col-after'           => esc_html__( 'Table Col After Icon', 'acf' ),
				'dashicons-table-col-before'          => esc_html__( 'Table Col Before Icon', 'acf' ),
				'dashicons-table-col-delete'          => esc_html__( 'Table Col Delete Icon', 'acf' ),
				'dashicons-table-row-after'           => esc_html__( 'Table Row After Icon', 'acf' ),
				'dashicons-table-row-before'          => esc_html__( 'Table Row Before Icon', 'acf' ),
				'dashicons-table-row-delete'          => esc_html__( 'Table Row Delete Icon', 'acf' ),
				'dashicons-tablet'                    => esc_html__( 'Tablet Icon', 'acf' ),
				'dashicons-tag'                       => esc_html__( 'Tag Icon', 'acf' ),
				'dashicons-tagcloud'                  => esc_html__( 'Tagcloud Icon', 'acf' ),
				'dashicons-testimonial'               => esc_html__( 'Testimonial Icon', 'acf' ),
				'dashicons-text'                      => esc_html__( 'Text Icon', 'acf' ),
				'dashicons-text-page'                 => esc_html__( 'Text Page Icon', 'acf' ),
				'dashicons-thumbs-down'               => esc_html__( 'Thumbs Down Icon', 'acf' ),
				'dashicons-thumbs-up'                 => esc_html__( 'Thumbs Up Icon', 'acf' ),
				'dashicons-tickets'                   => esc_html__( 'Tickets Icon', 'acf' ),
				'dashicons-tickets-alt'               => esc_html__( 'Tickets (alt) Icon', 'acf' ),
				'dashicons-tide'                      => esc_html__( 'Tide Icon', 'acf' ),
				'dashicons-translation'               => esc_html__( 'Translation Icon', 'acf' ),
				'dashicons-trash'                     => esc_html__( 'Trash Icon', 'acf' ),
				'dashicons-twitch'                    => esc_html__( 'Twitch Icon', 'acf' ),
				'dashicons-twitter'                   => esc_html__( 'Twitter Icon', 'acf' ),
				'dashicons-twitter-alt'               => esc_html__( 'Twitter (alt) Icon', 'acf' ),
				'dashicons-undo'                      => esc_html__( 'Undo Icon', 'acf' ),
				'dashicons-universal-access'          => esc_html__( 'Universal Access Icon', 'acf' ),
				'dashicons-universal-access-alt'      => esc_html__( 'Universal Access (alt) Icon', 'acf' ),
				'dashicons-unlock'                    => esc_html__( 'Unlock Icon', 'acf' ),
				'dashicons-update'                    => esc_html__( 'Update Icon', 'acf' ),
				'dashicons-update-alt'                => esc_html__( 'Update (alt) Icon', 'acf' ),
				'dashicons-upload'                    => esc_html__( 'Upload Icon', 'acf' ),
				'dashicons-vault'                     => esc_html__( 'Vault Icon', 'acf' ),
				'dashicons-video-alt'                 => esc_html__( 'Video (alt) Icon', 'acf' ),
				'dashicons-video-alt2'                => esc_html__( 'Video (alt2) Icon', 'acf' ),
				'dashicons-video-alt3'                => esc_html__( 'Video (alt3) Icon', 'acf' ),
				'dashicons-visibility'                => esc_html__( 'Visibility Icon', 'acf' ),
				'dashicons-warning'                   => esc_html__( 'Warning Icon', 'acf' ),
				'dashicons-welcome-add-page'          => esc_html__( 'Add Page Icon', 'acf' ),
				'dashicons-welcome-comments'          => esc_html__( 'Comments Icon', 'acf' ),
				'dashicons-welcome-learn-more'        => esc_html__( 'Learn More Icon', 'acf' ),
				'dashicons-welcome-view-site'         => esc_html__( 'View Site Icon', 'acf' ),
				'dashicons-welcome-widgets-menus'     => esc_html__( 'Widgets Menus Icon', 'acf' ),
				'dashicons-welcome-write-blog'        => esc_html__( 'Write Blog Icon', 'acf' ),
				'dashicons-whatsapp'                  => esc_html__( 'WhatsApp Icon', 'acf' ),
				'dashicons-wordpress'                 => esc_html__( 'WordPress Icon', 'acf' ),
				'dashicons-wordpress-alt'             => esc_html__( 'WordPress (alt) Icon', 'acf' ),
				'dashicons-xing'                      => esc_html__( 'Xing Icon', 'acf' ),
				'dashicons-yes'                       => esc_html__( 'Yes Icon', 'acf' ),
				'dashicons-yes-alt'                   => esc_html__( 'Yes (alt) Icon', 'acf' ),
				'dashicons-youtube'                   => esc_html__( 'YouTube Icon', 'acf' ),
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
