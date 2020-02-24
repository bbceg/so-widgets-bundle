<?php

/**
 * Class SiteOrigin_Widget_Field_Icon
 */
class SiteOrigin_Widget_Field_Icon extends SiteOrigin_Widget_Field_Base {
	/**
	 * The number of visible rows in the icons selector.
	 *
	 * @access protected
	 * @var int
	 *
	 */
	protected $rows = 3;

	protected $icons_callback;

	protected function render_field( $value, $instance ) {
		$widget_icon_families = $this->get_widget_icon_families();

		// Get an array of available icon families styles to pass to self::get_value_parts()
		$icon_families_styles = self::get_icon_families_styles( $widget_icon_families );

		$value_parts = self::get_value_parts( $value, $icon_families_styles );

		if ( ! empty( $value ) ) {
			$value_family = $value_parts['family'];
			$value_style = empty( $value_parts['style'] ) ? '' : ( '-' . $value_parts['style'] );
			$value = $value_parts['family'] . $value_style . '-' . $value_parts['icon'];
			
		} else {
			$value_family = 'fontawesome';
		}
		?>

		<div class="siteorigin-widget-icon-selector-current">
			<div class="siteorigin-widget-icon"><span></span></div>
			<label><?php _e('Choose Icon', 'so-widgets-bundle') ?></label>
		</div>

		<a class="so-icon-remove" style="display: <?php echo !empty( $value ) ? 'inline-block' : 'none' ?>;"><?php esc_html_e( 'Remove', 'so-widgets-bundle' ) ?></a>

		<div class="clear"></div>

		<div class="siteorigin-widget-icon-selector siteorigin-widget-field-subcontainer">
			<select class="siteorigin-widget-icon-family" >
				<?php foreach( $widget_icon_families as $family_id => $family_info ) : ?>
					<option value="<?php echo esc_attr( $family_id ) ?>"
						<?php selected( $value_family, $family_id ) ?>
						<?php if( !empty( $this->icons_callback ) ) echo 'data-icons="' . esc_attr( json_encode( $family_info ) ) . '"' ?>
						>
						<?php echo esc_html( $family_info['name'] ) ?> (<?php echo count( $family_info['icons'] ) ?>)
					</option>
				<?php endforeach; ?>
			</select>
			
			<?php if ( ! empty( $widget_icon_families[$value_family]['styles'] ) ) :
				$family_styles = $widget_icon_families[ $value_family ]['styles'];
				?>
			<select class="siteorigin-widget-icon-family-styles">
				<?php foreach ( $family_styles as $family_style => $family_style_name ) : ?>
					<option value="<?php echo esc_attr( $family_style ) ?>"
							<?php selected( $value_parts['style'], $family_style ) ?>>
						<?php esc_html_e( $family_style_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php endif;?>

			<input type="search" class="siteorigin-widget-icon-search" placeholder="<?php esc_attr_e( 'Search Icons' ) ?>" />

			<input type="hidden" name="<?php echo esc_attr( $this->element_name ) ?>" value="<?php echo esc_attr( $value ) ?>"
			       class="siteorigin-widget-icon-icon siteorigin-widget-input" />

			<div class="siteorigin-widget-icon-icons" style="height: <?php echo ( $this->rows * 54 ) - 3 ?>px;"></div>
		</div>
		<?php
	}

	protected function sanitize_field_input( $value, $instance ) {
		$sanitized_value = $value;
		// Alphanumeric characters and hyphens.
		if( preg_match( '/[\w\d]+[\w\d-]*/', $sanitized_value, $sanitized_matches ) ) {
			$sanitized_value = $sanitized_matches[0];
		}
		else {
			$sanitized_value = '';
		}
		
		$widget_icon_families = $this->get_widget_icon_families();

		$icon_families_styles = self::get_icon_families_styles( $widget_icon_families );

		$value_parts = self::get_value_parts( $sanitized_value, $icon_families_styles );

		if( ! ( isset( $widget_icon_families[$value_parts['family']] ) && isset( $widget_icon_families[$value_parts['family']]['icons'][$value_parts['icon']] ) ) ) {
			$sanitized_value = isset( $this->default ) ? $this->default : '';
		}
		
		return $sanitized_value;
	}

	private function get_widget_icon_families(){
		if( !empty( $this->icons_callback ) ) {
			// We'll get the icons from the callback function
			$widget_icon_families = call_user_func( $this->icons_callback );
		}
		else {
			// We'll get icons from the main filter
			$widget_icon_families = self::get_icon_families();
		}

		return $widget_icon_families;
	}
	
	static function get_icon_families() {
		static $widget_icon_families;
		if( empty( $widget_icon_families ) ) {
			$widget_icon_families = apply_filters( 'siteorigin_widgets_icon_families', array() );
		}
		
		return $widget_icon_families;
	}
	
	static function get_value_parts( $value, $icon_families_styles = null ) {
		
		list( $value_family, $value_icon ) = ( ! empty( $value ) && strpos( $value, '-' ) !== false ) ? explode( '-', $value, 2 ) : array('', '');
		
		// Check if icon families have styles
		// See $this->sanitize_field_input()
		if ( $icon_families_styles !== null ) {
			// Loop over all the available styles
			foreach ( $icon_families_styles as $icon_family => $icon_family_styles ) {
				foreach ( $icon_family_styles as $icon_family_style => $icon_family_style_name ) {
					// Check the icon value for matching styles
					if ( substr( $value_icon, 0, strlen( $icon_family_style ) ) === $icon_family_style ) {
						// Store the icon name and icon style name
						$value_icon = substr( $value_icon, strlen( $icon_family_style . '-' ) );
						$value_style = $icon_family_style;

						// Exit both foreach loops
						break 2;
					}
				}
			}
		}
		
		// Trigger loading of the icon families and their filters. This isn't ideal, but necessary to ensure possible
		// migrations are available.
		self::get_icon_families();
		
		return apply_filters( 'siteorigin_widgets_icon_migrate_' . $value_family, array(
			'family' => $value_family,
			'style' => ! empty( $value_style ) ? $value_style : null,
			'icon' => $value_icon,
		) );
	}

	public static function get_icon_families_styles( $widget_icon_families ) {
		// Store an array of icon family styles to pass to self::get_value_parts()
		$icon_families_styles = [];

		// Loop through the icon families
		foreach ( $widget_icon_families as $key => $val ) {
			// Check if the family has available styles
			if ( array_key_exists( 'styles', $val ) ) {
				// Add the family and styles to the $styles array
				$icon_families_styles[ $key ] = $val[ 'styles' ];
			}
		}

		return $icon_families_styles;
	}

	public function enqueue_scripts(){
		wp_enqueue_script( 'so-icon-field', plugin_dir_url( __FILE__ ) . 'js/icon-field' . SOW_BUNDLE_JS_SUFFIX .  '.js', array( 'jquery' ), SOW_BUNDLE_VERSION );
		wp_enqueue_style( 'so-icon-field', plugin_dir_url( __FILE__ ) . 'css/icon-field.css', array( ), SOW_BUNDLE_VERSION );
	}

}
