<?php
/*
Plugin Name: MP4 Uploader
Description: Custom plugin for uploading MP4 videos.
Version: 1.2
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

    $default_width = get_option('mp4_default_width', '');
    $default_height = get_option('mp4_default_height', '');

    $gallery_atts = shortcode_atts(
        array(
            'width' => $default_width,
            'height' => $default_height,
        ),
        $atts
    );

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
            echo '<video src="' . esc_url($mp4_url) . '" width="' . esc_attr($gallery_atts['width']) . '" height="' . esc_attr($gallery_atts['height']) . '" controls></video>';
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

// Add the settings page
function mp4_settings_page() {
    add_options_page('MP4 Uploader Settings', 'MP4 Uploader Settings', 'manage_options', 'mp4-uploader-settings', 'mp4_render_settings_page');
}
add_action('admin_menu', 'mp4_settings_page');

// Render the settings page
function mp4_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>MP4 Uploader Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mp4_settings');
            do_settings_sections('mp4_settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row">Default Video Width</th>
                    <td><input type="number" name="mp4_default_width" value="<?php echo esc_attr(get_option('mp4_default_width')); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row">Default Video Height</th>
                    <td><input type="number" name="mp4_default_height" value="<?php echo esc_attr(get_option('mp4_default_height')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register the settings
function mp4_register_settings() {
    register_setting('mp4_settings', 'mp4_default_width');
    register_setting('mp4_settings', 'mp4_default_height');
}
add_action('admin_init', 'mp4_register_settings');
