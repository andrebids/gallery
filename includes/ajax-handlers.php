<?php
// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Handle AJAX request to filter images
function filter_images() {
    check_ajax_referer('filter_images_nonce', 'security');

    // Get request data
    $selected_tags = isset($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : [];
    $dimensions = isset($_POST['dimensions']) ? $_POST['dimensions'] : [];
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;

    // IDs of images to exclude
    $excluded_image_ids = array(3728, 8864); // Logo ID and SVG ID

    // Set up the query
    $args = array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => 20,
        'paged'          => $paged,
        'post__not_in'   => $excluded_image_ids,
        'tax_query'      => array(),
        'meta_query'     => array('relation' => 'AND'),
    );

    // Add tag filter if necessary
    if (!empty($selected_tags)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'custom_tag',
            'field'    => 'name',
            'terms'    => $selected_tags,
            'operator' => 'AND',
        );
    }

    // Add dimension filters
    foreach ($dimensions as $key => $value) {
        if (!empty($value)) {
            $args['meta_query'][] = array(
                'key'     => 'image_dimensions',
                'value'   => sprintf(':"%s";s:%d:"%s";', $key, strlen($value), $value),
                'compare' => 'LIKE',
            );
        }
    }

    // Ensure we're getting all image mime types, including SVG
    $args['post_mime_type'] = array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml');

    // Debug: Log the query arguments
    error_log('Image Query Args: ' . print_r($args, true));

    $images_query = new WP_Query($args);

    // Debug: Log the number of found posts
    error_log('Number of images found: ' . $images_query->found_posts);

    ob_start();
    if ($images_query->have_posts()) {
        echo '<div class="custom-gallery-masonry">';
        while ($images_query->have_posts()) {
            $images_query->the_post();
            $image_id = get_the_ID();
            $img_url = wp_get_attachment_url($image_id);
            $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            echo '<div class="masonry-item">';
            echo '<a href="' . esc_url($img_url) . '" data-lightbox="gallery" data-title="' . esc_attr($alt_text) . '">';
            echo '<img src="' . esc_url($img_url) . '" alt="' . esc_attr($alt_text) . '" />';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
        
        // Render pagination
        echo '<div id="gallery-pagination" class="pagination" style="display: flex; justify-content: center; padding: 10px 0;">';
        echo paginate_links(array(
            'total' => $images_query->max_num_pages,
            'current' => $paged,
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
        ));
        echo '</div>';
    } else {
        echo '<p>No images found.</p>';
    }
    $output = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success(array(
        'html' => $output,
        'max_pages' => $images_query->max_num_pages,
        'current_page' => $paged
    ));
}

// Hook the filter_images function to WordPress AJAX actions
add_action('wp_ajax_filter_images', 'filter_images');
add_action('wp_ajax_nopriv_filter_images', 'filter_images');

// You can add more AJAX handlers here if needed
?>