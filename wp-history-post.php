<?php
/*

**************************************************************************

Plugin Name:  WP History Post
Plugin URI:   http://www.arefly.com/wp-history-post/
Description:  Let visitors see your post in Today's History. 讓訪客看到歷史上的今天所寫的文章
Version:      1.0.6
Author:       Arefly
Author URI:   http://www.arefly.com/
Text Domain:  wp-history-post
Domain Path:  /lang/

**************************************************************************

	Copyright 2014  Arefly  (email : eflyjason@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

**************************************************************************/

define("WP_HISTORY_POST_PLUGIN_URL", plugin_dir_url( __FILE__ ));
define("WP_HISTORY_POST_FULL_DIR", plugin_dir_path( __FILE__ ));
define("WP_HISTORY_POST_TEXT_DOMAIN", "wp-history-post");

/* Plugin Localize */
function wp_history_post_load_plugin_textdomain() {
	load_plugin_textdomain(WP_HISTORY_POST_TEXT_DOMAIN, false, dirname(plugin_basename( __FILE__ )).'/lang/');
}
add_action('plugins_loaded', 'wp_history_post_load_plugin_textdomain');

include_once WP_HISTORY_POST_FULL_DIR."options.php";

/* Add Links to Plugins Management Page */
function wp_history_post_action_links($links){
	$links[] = '<a href="'.get_admin_url(null, 'options-general.php?page='.WP_HISTORY_POST_TEXT_DOMAIN.'-options').'">'.__("Settings", WP_HISTORY_POST_TEXT_DOMAIN).'</a>';
	return $links;
}
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'wp_history_post_action_links');

function wp_history_post_base($post_year, $post_month, $post_day){
	global $wpdb;
	$limit = 30;
	$order = "latest";
	if($order == "latest"){ $order = "DESC";} else { $order = '';}
	$sql = "select ID, year(post_date_gmt) as h_year, post_title, comment_count FROM 
	$wpdb->posts WHERE post_password = '' AND post_type = 'post' AND post_status = 'publish'
	AND year(post_date_gmt)!='$post_year' AND month(post_date_gmt)='$post_month' AND day(post_date_gmt)='$post_day'
	order by post_date_gmt $order limit $limit";
	$histtory_post = $wpdb->get_results($sql);
	return $histtory_post;
}

/*********************** WP HISTORY POST SINGLE POST PART START ***********************/

function wp_history_post_single(){
	$histtory_post = wp_history_post_base(get_the_time('Y'), get_the_time('m'), get_the_time('j'));
	if($histtory_post){
		foreach( $histtory_post as $post ){
			$h_year = $post->h_year;
			$h_post_title = $post->post_title;
			$h_permalink = get_permalink( $post->ID );
			$h_comments = $post->comment_count;
			$h_post .= get_option("wp_history_post_content_list");
			$h_post = str_replace("%YEAR%", $h_year, $h_post);
			$h_post = str_replace("%LINK%", $h_permalink, $h_post);
			$h_post = str_replace("%TITLE%", $h_post_title, $h_post);
			$h_post = str_replace("%COMMENTS_NUM%", $h_comments, $h_post);
		}
	}
	if($h_post){
		$result = get_option("wp_history_post_content_title").$h_post;
	}
	return $result;
	wp_reset_query();
}

function get_wp_history_post(){
	echo wp_history_post_single();
}

function wp_history_post_content($content){
	global $wpdb;
	if(is_single()){
		$content .= wp_history_post_single();
	}
	return $content;
}
add_action('the_content', 'wp_history_post_content');

/*********************** WP HISTORY POST WIDGET PART START ***********************/

function wp_history_post_widget(){
	$histtory_post = wp_history_post_base(date('Y'), date('m'), date('j'));
	if($histtory_post){
		foreach($histtory_post as $post){
			$h_year = $post->h_year;
			$h_post_title = $post->post_title;
			$h_permalink = get_permalink( $post->ID );
			$h_comments = $post->comment_count;
			$h_post .= '<li>'.$h_year.': <a href="'.$h_permalink.'"title="'.$h_post_title.'" rel="external nofollow">'.$h_post_title.'</a></li>';
		}
	}
	return $h_post;
	wp_reset_query();
}

class wp_history_post_widget extends WP_Widget {
	// Controller
	function __construct(){
		$widget_ops = array('classname' => 'wp_history_post_class', 'description' => __("See your Today's History Post.", WP_HISTORY_POST_TEXT_DOMAIN));
		$control_ops = array('width' => 400, 'height' => 300);
		parent::WP_Widget(false, $name = __('WP History Post', WP_HISTORY_POST_TEXT_DOMAIN), $widget_ops, $control_ops);
	}

	// Constructor
	function wp_my_plugin(){
		parent::WP_Widget(false, $name = __('WP History Post', WP_HISTORY_POST_TEXT_DOMAIN) );
	}

	// Display widget
	function widget($args, $instance){
		extract($args);
		// These are the widget options
		$title = apply_filters('widget_title', $instance['title']);
		$no_post_notice = $instance['no_post_notice'];
		echo $before_widget;
		// Display the widget
		echo '<div class="'.WP_HISTORY_POST_TEXT_DOMAIN.'">';

		// Check if title is set
		if(empty($title)){
			$title = "Today's History Post";
		}
		echo $before_title . $title . $after_title;

		$history_post = wp_history_post_widget();
		if($history_post){
			echo $history_post;
		}else{
			if($no_post_notice){
				echo $no_post_notice;
			}else{
				_e("There are no History Post Today!", WP_HISTORY_POST_TEXT_DOMAIN);
			}
		}
		echo '</div>';
		echo $after_widget;
	}

	// Update widget
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		// Fields
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['no_post_notice'] = strip_tags($new_instance['no_post_notice']);
		return $instance;
	}

	// Widget form creation
	function form($instance) {
		// Check values
		if($instance) {
			$title = esc_attr($instance['title']);
			$no_post_notice = esc_attr($instance['no_post_notice']);
		} else {
			$title = '';
			$no_post_notice = '';
		}
?>
<p>
<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', WP_HISTORY_POST_TEXT_DOMAIN); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id('no_post_notice'); ?>"><?php _e("No History Post's Notice", WP_HISTORY_POST_TEXT_DOMAIN); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('no_post_notice'); ?>" name="<?php echo $this->get_field_name('no_post_notice'); ?>" type="text" value="<?php echo $no_post_notice; ?>" />
</p>
<?php
	}
}

function wp_history_post_register_widgets(){
	register_widget('wp_history_post_widget');
}
add_action('widgets_init', 'wp_history_post_register_widgets');