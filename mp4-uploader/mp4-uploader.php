<?php
/*
Plugin Name: MP4 Uploader
Description: Custom plugin for uploading MP4 videos.
Version: 1.1
Author: Jeremy Perpignani
Author URI: https:diamondhands.org
*/

// MP4 Uploader Form Shortcode
function mp4_uploader_form_shortcode() {
    ob_start();
    ?>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="mp4_file" accept="video/mp4">
        <input type="submit" name="upload_mp4" value="Upload MP4">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('mp4_uploader_form', 'mp4_uploader_form_shortcode');

// MP4 Gallery Shortcode
function mp4_gallery_shortcode($atts) {
    ob_start();

    $query_args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'video/mp4',
        'posts_per_page' => -1,
        'post_status' => 'inherit',
        'meta_query' => array(
            array(
                'key' => 'mp4_uploaded',
                'value' => 'yes',
            ),
        ),
    );

    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
        echo '<div class="mp4-gallery">';
        while ($query->have_posts()) {
            $query->the_post();
            $mp4_url = wp_get_attachment_url(get_the_ID());
            echo '<video src="' . esc_url($mp4_url) . '" controls></video>';
        }
        echo '</div>';
    } else {
        echo 'No MP4 videos found.';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('mp4_gallery', 'mp4_gallery_shortcode');

// Handle MP4 Upload
function handle_mp4_upload() {
    if (isset($_POST['upload_mp4'])) {
        $file = $_FILES['mp4_file'];

        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];

        $upload_dir = wp_upload_dir();
        $target_dir = $upload_dir['path'];
        $target_file = $target_dir . '/' . $file_name;

        $upload_result = move_uploaded_file($file_tmp, $target_file);

        if ($upload_result) {
            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . $file_name,
                'post_mime_type' => 'video/mp4',
                'post_title' => $file_name,
                'post_content' => '',
                'post_status' => 'inherit',
            );

            $attachment_id = wp_insert_attachment($attachment, $target_file);

            if (!is_wp_error($attachment_id)) {
                update_post_meta($attachment_id, 'mp4_uploaded', 'yes');
            }
        }
    }
}
add_action('init', 'handle_mp4_upload');
