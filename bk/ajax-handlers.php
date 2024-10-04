<?php

// Handle AJAX request to filter images
add_action('wp_ajax_filter_images', 'filter_images');
add_action('wp_ajax_nopriv_filter_images', 'filter_images');

function filter_images() {
    check_ajax_referer('filter_images_nonce', 'security');

    // Get request data
    $search_image_name = sanitize_text_field($_POST['search_image_name']);
    $selected_tags = isset($_POST['tags']) ? array_map('sanitize_text_field', $_POST['tags']) : [];
    $dimensions = $_POST['dimensions'];
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;

    // Logo image ID to exclude
    $logo_image_id = 3728; // ID of the logo image to exclude

    // Set up the query
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => 20,
        'paged' => $paged,
        'post__not_in' => array($logo_image_id), // Exclude by attachment ID
        'tax_query' => array(),
        'meta_query' => array('relation' => 'AND'),
    );

    // Add image name filter if necessary
    if (!empty($search_image_name)) {
        $args['s'] = $search_image_name;
    }

    // Add tag filter if necessary
    if (!empty($selected_tags)) {
        $args['tax_query'][] = array(
            'taxonomy' => 'custom_tag',
            'field' => 'name',
            'terms' => $selected_tags,
            'operator' => 'AND',
        );
    }

    // Add dimension filters
    foreach ($dimensions as $key => $value) {
        if (!empty($value)) {
            $args['meta_query'][] = array(
                'key' => 'image_dimensions',
                'value' => sprintf(':"%s";s:%d:"%s";', $key, strlen($value), $value),
                'compare' => 'LIKE',
            );
        }
    }

    $images_query = new WP_Query($args);

    ob_start();

    if ($images_query->have_posts()) {
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

        // Render pagination
        echo '<div class="pagination" style="display: flex; justify-content: center; padding: 10px 0;">';
        $big = 999999999; // need an unlikely integer
        echo paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, $paged),
            'total' => $images_query->max_num_pages,
            'prev_text' => __('&laquo; Previous'),
            'next_text' => __('Next &raquo;'),
            'type' => 'plain',
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