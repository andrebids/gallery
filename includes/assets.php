<?php
// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue CSS and JavaScript for Gallery and Lightbox
function custom_gallery_assets() {
    // Enqueue jQuery Masonry
    wp_enqueue_script('jquery-masonry');

    // Lightbox2
    wp_enqueue_style('lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
    wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), '2.11.3', true);

    // Select2 CSS and JS
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);

    // Custom gallery CSS (loaded after Select2 to override styles)
    wp_enqueue_style('custom-gallery-css', plugins_url('../css/custom-gallery.css', __FILE__), array('select2-css'));

    // Custom gallery JavaScript
    wp_enqueue_script('custom-gallery-js', plugins_url('../js/custom-gallery.js', __FILE__), array('jquery', 'select2-js'), '1.0', true);

    // Localize the script with new data
    wp_localize_script('custom-gallery-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('filter_images_nonce'),
    ));
}

// Hook the function to WordPress
add_action('wp_enqueue_scripts', 'custom_gallery_assets');
?>