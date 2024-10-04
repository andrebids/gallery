<?php
// Shortcode for the filter form
add_shortcode('custom_gallery_filters', 'custom_gallery_filters_shortcode');
function custom_gallery_filters_shortcode() {
    ob_start();
    ?>
    <div id="image-filter-form">
        <h3>Image Filters</h3>
        <div>
            <label>2D/3D:</label>
            <div class="checkbox-group">
                <label for="filter-tag-2d">
                    <input type="checkbox" id="filter-tag-2d" name="filter-tag-2d" value="2D"> 2D
                </label>
                <label for="filter-tag-3d">
                    <input type="checkbox" id="filter-tag-3d" name="filter-tag-3d" value="3D"> 3D
                </label>
            </div>
        </div>
        <div>
            <label for="filter-tags-other">Other Tags:</label>
            <select id="filter-tags-other" multiple style="width: 100%;">
                <?php
                $tags = get_existing_custom_tags();
                $tags_2d3d = ['2D', '3D'];
                $other_tags = array_diff($tags, $tags_2d3d);
                foreach ($other_tags as $tag) {
                    $count = get_image_count_by_tag($tag);
                    if ($count > 0) {  // Only include tags with at least one image
                        echo '<option value="' . esc_attr($tag) . '">' . esc_html($tag) . ' (' . $count . ' images)</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div>
            <label for="filter-altura">Height:</label>
            <input type="text" id="filter-altura" placeholder="Height">
        </div>
        <div class="filter-buttons">
            <button id="apply-filters">Apply Filters</button>
            <button id="reset-filters">Reset Filters</button>
        </div>
        <div id="applied-filters" style="display: none;">
            <h4>Applied Filters:</h4>
            <div id="filters-summary"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Shortcode for the gallery
add_shortcode('custom_gallery', 'custom_gallery_shortcode');
function custom_gallery_shortcode() {
    ob_start();
    ?>
    <div id="gallery-container">
        <div class="custom-gallery-masonry">
        <?php
        // Display images with pagination
        $logo_image_id = 3728; // ID of the logo image to exclude
        $svg_image_id = 8864; // ID of the SVG image to exclude
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $posts_per_page = 20;
        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => array('image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/svg+xml'),
            'post_status' => 'inherit',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'post__not_in' => array($logo_image_id, $svg_image_id), // Exclude specific image IDs
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_wp_attachment_metadata',
                    'compare' => 'EXISTS',
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_hidden_from_gallery',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key' => '_hidden_from_gallery',
                        'value' => '1',
                        'compare' => '!='
                    )
                )
            ),
        );
        $images_query = new WP_Query($args);
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
        } else {
            echo '<p>No images found.</p>';
        }
        wp_reset_postdata();
        ?>
        </div>
        <div id="gallery-pagination" class="pagination" style="display: flex; justify-content: center; padding: 10px 0;">
            <?php
            echo paginate_links(array(
                'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'format' => '?paged=%#%',
                'current' => max(1, get_query_var('paged')),
                'total' => $images_query->max_num_pages,
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
                'type' => 'plain',
            ));
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Function to get all existing tags for autocomplete
if (!function_exists('get_existing_custom_tags')) {
    function get_existing_custom_tags() {
        $tags = get_terms(array(
            'taxonomy' => 'custom_tag',
            'hide_empty' => false,
        ));
        return is_array($tags) ? wp_list_pluck($tags, 'name') : [];
    }
}

// Function to count the number of images for a given tag
function get_image_count_by_tag($tag_name) {
    $args = array(
        'post_type' => 'attachment',
        'post_mime_type' => 'image',
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'custom_tag',
                'field' => 'name',
                'terms' => $tag_name,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query->found_posts;
}