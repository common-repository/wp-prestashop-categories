<?php
/*
Plugin Name: WP PrestaShop Categories
Plugin URI: http://blog.joelgaujard.info/realisations/wp-prestashop-categories
Description: Include a <a href="http://www.prestashop.com">PrestaShop</a> ecommerce website to your blog. Include the header and footer of your ecommerce website on your blog. You need to install the <a href="http://prestashop.joelgaujard.info/product.php?id_product=17">WordPress module for PrestaShop</a> on your ecommerce website to use this plugin.
Version: 1.2.0
Author: Joel Gaujard
Author URI: http://www.joelgaujard.info/
*/

//error_reporting(E_ALL);

/**
 * Add function to widgets_init that'll load our widget.
 */
add_action( 'widgets_init', 'pscategories_load_widgets' );

/**
 * Register our widget.
 */
function pscategories_load_widgets() {
	register_widget( 'PS_Categories' );
}

/**
 * Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.
 */
class PS_Categories extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function PS_Categories() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'pscategories', 'description' => __('A widget that displays your ecommerce (prestashop) categories.') );
		/* Widget control settings. */
		$control_ops = array( 'id_base' => 'pscategories-widget' );
		/* Create the widget. */
		$this->WP_Widget( 'pscategories-widget', __('PrestaShop Categories'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$url = $instance['url'];
		$show_image = isset( $instance['show_image'] ) ? $instance['show_image'] : false;
		//$ajax = $instance['ajax'];

		/* Before widget (defined by themes). */
		$output = $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			$output .= $before_title . $title . $after_title;

		//if ( 'disabled' == $ajax ):
			/* Get PrestaShop categories with cURL method*/
			$url = rtrim($url,'/').'/modules/wordpresscategories/ps-categories.php';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			$categories = json_decode($result);
	
			$output .= '<ul>';
			if ( is_array($categories) AND !empty($categories) AND $categories ) {
				foreach ( $categories AS $key => $category ) {
					$output .= '<li><a href="' . $category->link . '">';
					if ( $show_image )
						$output .= '<img src="' . $category->image . '" alt="" style="vertical-align: middle" />';
					$output .= $category->name.'</a></li>';
				}
			} else {
				$output .= '<p>' . __('None categories.') . '</p>';
			}
			$output .= '</ul>';
		//endif;

		/* After widget (defined by themes). */
		$output .= $after_widget;

		echo $output;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['url'] = strip_tags( $new_instance['url'] );

		/* No need to strip tags for selects, checkboxes or radios. */
		//$instance['ajax'] = $new_instance['ajax'];
		$instance['show_image'] = $new_instance['show_image'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title' => __('Shop categories'),
			'url' => __('http://www.site.com/shop/'),
			//'ajax' => 'enabled',
			'show_image' => true
		);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>

		<!-- Url: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e('Shop Url:'); ?></label>
			<input id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" value="<?php echo $instance['url']; ?>" />
		</p>

		<!-- Ajax: Select Box >
		<p>
			<label for="<?php echo $this->get_field_id( 'ajax' ); ?>"><?php _e('Ajax mode:'); ?></label> 
			<select id="<?php echo $this->get_field_id( 'ajax' ); ?>" name="<?php echo $this->get_field_name( 'ajax' ); ?>">
				<option value="enabled" <?php if ( 'enabled' == $instance['ajax'] ) echo 'selected="selected"'; ?>><?php _e('Enabled'); ?></option>
				<option value="disabled" <?php if ( 'disabled' == $instance['ajax'] ) echo 'selected="selected"'; ?>><?php _e('Disabled'); ?></option>
			</select>
		</p-->

		<!-- Show Image? Checkbox -->
		<p>
			<input value="1" type="checkbox" <?php checked( $instance['show_image'], true ); ?> id="<?php echo $this->get_field_id( 'show_image' ); ?>" name="<?php echo $this->get_field_name( 'show_image' ); ?>" /> 
			<label for="<?php echo $this->get_field_id( 'show_image' ); ?>"><?php _e('Display image?'); ?></label>
		</p>

	<?php
	}
}

