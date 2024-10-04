<?php

// Enqueue CSS and JavaScript for Gallery and Lightbox
add_action('wp_enqueue_scripts', 'custom_gallery_assets');

function custom_gallery_assets() {
    wp_enqueue_style('custom-gallery-css', plugins_url('custom-gallery.css', __FILE__));
    wp_enqueue_script('jquery-masonry');
    
    // Lightbox2
    wp_enqueue_style('lightbox-css', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css');
    wp_enqueue_script('lightbox-js', 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js', array('jquery'), '2.11.3', true);

    // Include Select2 CSS and JS
    wp_enqueue_style('select2-css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array('jquery'), '4.0.13', true);

    // Enqueue custom gallery script
    wp_enqueue_script('custom-gallery-js', plugins_url('custom-gallery.js', __FILE__), array('jquery'), '1.0', true);

    // Localize the script with new data
    $script_data_array = array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'ajax_nonce' => wp_create_nonce('filter_images_nonce'),
    );
    wp_localize_script('custom-gallery-js', 'ajax_object', $script_data_array);
}