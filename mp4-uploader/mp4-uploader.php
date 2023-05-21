<?php
/*
Plugin Name: MP4 Uploader
Description: Custom plugin for uploading MP4 videos.
Version: 1.0
Author: Jeremy Perpignani
Author URI: https:diamondhands.org
*/

// Enqueue scripts and styles
function mp4_uploader_enqueue_scripts() {
    wp_enqueue_script('mp4-uploader-script', plugin_dir_url(__FILE__) . 'js/mp4-uploader.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'mp4_uploader_enqueue_scripts');

// Add custom meta box to the post editor
function mp4_uploader_add_meta_box() {
    add_meta_box('mp4-uploader-meta-box', 'MP4 Video', 'mp4_uploader_render_meta_box', 'post', 'normal', 'high');
}
add_action('add_meta_boxes', 'mp4_uploader_add_meta_box');

// Render the custom meta box
function mp4_uploader_render_meta_box($post) {
    wp_nonce_field('mp4_uploader_meta_box', 'mp4_uploader_meta_box_nonce');
    $value = get_post_meta($post->ID, 'mp4_video', true);
    ?>
    <input type="text" id="mp4_video" name="mp4_video" value="<?php echo esc_attr($value); ?>" style="width:100%;" placeholder="Enter MP4 video URL">
    <?php
}

// Save the custom meta box value
function mp4_uploader_save_meta_box($post_id) {
    if (!isset($_POST['mp4_uploader_meta_box_nonce']) || !wp_verify_nonce($_POST['mp4_uploader_meta_box_nonce'], 'mp4_uploader_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['mp4_video'])) {
        update_post_meta($post_id, 'mp4_video', sanitize_text_field($_POST['mp4_video']));
    }
}
add_action('save_post', 'mp4_uploader_save_meta_box');
