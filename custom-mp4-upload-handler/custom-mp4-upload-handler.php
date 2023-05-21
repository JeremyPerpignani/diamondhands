<?php
/*
Plugin Name: Custom MP4 Upload Handler
Description: Custom plugin for handling MP4 uploads and displaying videos.
Version: 1.0
Author: Jeremy Perpignani
Author URI: https:diamondhands.org
*/

// Register shortcode for displaying the video
function custom_mp4_upload_handler_shortcode($atts) {
    ob_start();
    
    $atts = shortcode_atts(array(
        'id' => '',
        'autoplay' => '0',
        'loop' => '0'
    ), $atts);
    
    $video_id = $atts['id'];
    $autoplay = $atts['autoplay'] ? 'autoplay' : '';
    $loop = $atts['loop'] ? 'loop' : '';
    
    $video_url = get_post_meta($video_id, 'mp4_video', true);
    
    if (!empty($video_url)) {
        ?>
        <video controls <?php echo $autoplay; ?> <?php echo $loop; ?>>
            <source src="<?php echo esc_attr($video_url); ?>" type="video/mp4">
        </video>
        <?php
    }
    
    return ob_get_clean();
}
add_shortcode('custom_mp4_video', 'custom_mp4_upload_handler_shortcode');

// Register shortcode for displaying the upload form
function custom_mp4_upload_handler_form_shortcode($atts) {
    ob_start();
    ?>
    <form id="custom-mp4-upload-form" method="post" enctype="multipart/form-data">
        <input type="file" name="custom_mp4_file">
        <input type="hidden" name="action" value="custom_mp4_upload">
        <?php wp_nonce_field('custom_mp4_upload_nonce', 'custom_mp4_upload_nonce'); ?>
        <input type="submit" value="Upload">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_mp4_upload_form', 'custom_mp4_upload_handler_form_shortcode');

// Handle custom MP4 file upload
function custom_mp4_upload_handler() {
    if (!isset($_POST['custom_mp4_upload_nonce']) || !wp_verify_nonce($_POST['custom_mp4_upload_nonce'], 'custom_mp4_upload_nonce')) {
        return;
    }
    
    if (!current_user_can('upload_files')) {
        return;
    }
    
    if (!empty($_FILES['custom_mp4_file']['name'])) {
        $upload_overrides = array('test_form' => false);
        
        $uploaded_file = wp_handle_upload($_FILES['custom_mp4_file'], $upload_overrides);
        
        if (!isset($uploaded_file['error'])) {
            $attachment = array(
                'post_title' => $_FILES['custom_mp4_file']['name'],
                'post_mime_type' => $uploaded_file['type'],
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $uploaded_file['file']);
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $attach_data = wp_generate_attachment_metadata($attach_id, $uploaded_file['file']);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            echo 'File uploaded successfully!';
        } else {
            echo 'Error uploading file: ' . $uploaded_file['error'];
        }
    }
    
    die();
}
add_action('wp_ajax_custom_mp4_upload', 'custom_mp4_upload_handler');
add_action('wp_ajax_nopriv_custom_mp4_upload', 'custom_mp4_upload_handler');
