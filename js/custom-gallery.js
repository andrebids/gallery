jQuery(document).ready(function($) {
    // Initialize Select2 for other tags selection
    $('#filter-tags-other').select2({
        placeholder: "Select tags",
        allowClear: true,
        width: '100%'
    });

    // Apply filters when the button is clicked
    $('#apply-filters').on('click', function() {
        applyFilters(1);
    });

    // Reset filters when the button is clicked
    $('#reset-filters').on('click', function() {
        $('#filter-tag-2d').prop('checked', false);
        $('#filter-tag-3d').prop('checked', false);
        $('#filter-tags-other').val([]).trigger('change');
        $('#filter-altura').val(''); // Reset height selection
        applyFilters(1);
    });

    // Function to apply filters using AJAX
    function applyFilters(page = 1) {
        var selectedTags2D3D = [];
        if ($('#filter-tag-2d').is(':checked')) selectedTags2D3D.push('2D');
        if ($('#filter-tag-3d').is(':checked')) selectedTags2D3D.push('3D');
        var selectedOtherTags = $('#filter-tags-other').val() || [];
        var selectedTags = selectedTags2D3D.concat(selectedOtherTags);
        var dimensions = {
            altura: $('#filter-altura').val().trim()
        };

        $.ajax({
            url: ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'filter_images',
                tags: selectedTags,
                dimensions: dimensions,
                page: page,
                security: ajax_object.ajax_nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#gallery-container').html(response.data.html);
                    updateFiltersSummary(selectedTags2D3D, selectedOtherTags, dimensions);
                    // Re-initialize Masonry
                    $('.custom-gallery-masonry').masonry('reloadItems').masonry('layout');
                    // Re-initialize lightbox after loading new images
                    if (typeof lightbox !== 'undefined') {
                        lightbox.init();
                    }
                    // Scroll to the top of the gallery container
                    $('html, body').animate({
                        scrollTop: $('#gallery-container').offset().top
                    }, 500);
                    // Reinitialize pagination click event
                    initPagination();
                } else {
                    console.error('Error in AJAX response:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Update filters summary
    function updateFiltersSummary(selectedTags2D3D, selectedOtherTags, dimensions) {
        var summaryHtml = '';
        if (selectedTags2D3D.length > 0) {
            selectedTags2D3D.forEach(function(tag) {
                summaryHtml += '<div class="filter-item">Tag: ' + tag + ' <span class="remove-filter" data-filter="tag" data-value="' + tag + '">x</span></div>';
            });
        }
        if (selectedOtherTags.length > 0) {
            selectedOtherTags.forEach(function(tag) {
                summaryHtml += '<div class="filter-item">Tag: ' + tag + ' <span class="remove-filter" data-filter="tag" data-value="' + tag + '">x</span></div>';
            });
        }
        if (dimensions.altura) {
            summaryHtml += '<div class="filter-item">Height: ' + dimensions.altura + ' <span class="remove-filter" data-filter="height">x</span></div>';
        }
        // Show the applied filters section only if there are active filters
        if (summaryHtml) {
            $('#applied-filters').show();
        } else {
            $('#applied-filters').hide();
        }
        $('#filters-summary').html(summaryHtml);
    }

    // Remove individual filters
    $(document).on('click', '.remove-filter', function() {
        var filterType = $(this).data('filter');
        var filterValue = $(this).data('value');
        switch (filterType) {
            case 'tag':
                if (filterValue === '2D' || filterValue === '3D') {
                    $('#filter-tag-' + filterValue.toLowerCase()).prop('checked', false);
                } else {
                    var selectedTags = $('#filter-tags-other').val();
                    selectedTags = selectedTags.filter(function(tag) {
                        return tag !== filterValue;
                    });
                    $('#filter-tags-other').val(selectedTags).trigger('change');
                }
                break;
            case 'height':
                $('#filter-altura').val(''); // Reset height selection
                break;
        }
        applyFilters(1);
    });

    // Initialize pagination click event
    function initPagination() {
        $('.pagination a').off('click').on('click', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            var page = href.match(/page\/(\d+)/);
            if (page) {
                applyFilters(parseInt(page[1]));
            } else {
                var pageQuery = href.match(/paged=(\d+)/);
                if (pageQuery) {
                    applyFilters(parseInt(pageQuery[1]));
                }
            }
        });
    }

    // Initial call to set up pagination
    initPagination();
});