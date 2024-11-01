<?php
function wp_history_post_register_settings() {
	add_option('wp_history_post_content_title', __('<h3>History Post: </h3>', WP_HISTORY_POST_TEXT_DOMAIN));
	add_option('wp_history_post_content_list', '<p>%YEAR%: <a href="%LINK%"title="%TITLE%" rel="external nofollow">%TITLE% (%COMMENTS_NUM% Comments)</a></p>', WP_HISTORY_POST_TEXT_DOMAIN);
	register_setting('wp_history_post_options', 'wp_history_post_content_title');
	register_setting('wp_history_post_options', 'wp_history_post_content_list');
}
add_action('admin_init', 'wp_history_post_register_settings');

function wp_history_post_register_options_page() {
	add_options_page(__('WP History Post Options Page', WP_HISTORY_POST_TEXT_DOMAIN), __('WP History Post', WP_HISTORY_POST_TEXT_DOMAIN), 'manage_options', WP_HISTORY_POST_TEXT_DOMAIN.'-options', 'wp_history_post_options_page');
}
add_action('admin_menu', 'wp_history_post_register_options_page');

function wp_history_post_options_page() {
?>
<div class="wrap">
	<h2><?php _e("WP History Post Options Page", WP_HISTORY_POST_TEXT_DOMAIN); ?></h2>
	<form method="post" action="options.php">
		<?php settings_fields('wp_history_post_options'); ?>
		<h3><?php _e("General Options", WP_HISTORY_POST_TEXT_DOMAIN); ?></h3>
			<p><?php printf(__('You can go to get History Post in Post by function %s in post or using widget to see Today History Post', WP_HISTORY_POST_TEXT_DOMAIN), '<code>get_wp_history_post()</code>'); ?></p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="wp_history_post_content_title"><?php _e("History Post in Post's title: ", WP_HISTORY_POST_TEXT_DOMAIN); ?></label></th>
					<td>
						<input type="text" name="wp_history_post_content_title" id="wp_history_post_content_title" value="<?php echo htmlspecialchars(get_option('wp_history_post_content_title')); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="wp_history_post_content_list"><?php _e("Way you want to get for the latest post: ", WP_HISTORY_POST_TEXT_DOMAIN); ?></label></th>
					<td>
						<input type="text" name="wp_history_post_content_list" id="wp_history_post_content_list" size="60" value="<?php echo htmlspecialchars(get_option('wp_history_post_content_list')); ?>" />
						<p><?php printf(__('You may use %s in it.', WP_HISTORY_POST_TEXT_DOMAIN), '<code>%YEAR%</code>, <code>%LINK%</code>, <code>%TITLE%</code>, <code>%COMMENTS_NUM%</code>'); ?></p>
					</td>
				</tr>
			</table>
		<?php submit_button(); ?>
	</form>
</div>
<?php
}
?>