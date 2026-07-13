<script>
    let activeFilterRequests = 0;
    let filterLoaderTimeout = null;

    function showFilterSpinner() {
        const skipLoader = typeof window.disableGlobalFilterLoader !== 'undefined' && window.disableGlobalFilterLoader;
        if (!skipLoader) {
            activeFilterRequests++;
            if (filterLoaderTimeout) {
                clearTimeout(filterLoaderTimeout);
                filterLoaderTimeout = null;
            }
            $('#filter-loader').removeClass('d-none');
        }
    }

    function hideFilterSpinner() {
        const skipLoader = typeof window.disableGlobalFilterLoader !== 'undefined' && window.disableGlobalFilterLoader;
        if (!skipLoader) {
            activeFilterRequests--;
            if (activeFilterRequests <= 0) {
                activeFilterRequests = 0;
                if (!filterLoaderTimeout) {
                    filterLoaderTimeout = setTimeout(function() {
                        $('#filter-loader').addClass('d-none');
                        filterLoaderTimeout = null;
                    }, 200);
                }
            }
        }
    }

    // Hook into native fetch to show/hide the filter loader
    const originalFetch = window.fetch;
    window.fetch = async function(...args) {
        showFilterSpinner();
        try {
            const response = await originalFetch(...args);
            return response;
        } finally {
            hideFilterSpinner();
        }
    };

    // Universal Date Filter Javascript Module
    $(document).ready(function() {
        // Global jQuery AJAX loaders using individual send/complete events for counting
        $(document).ajaxSend(function() {
            showFilterSpinner();
        });
        $(document).ajaxComplete(function() {
            hideFilterSpinner();
        });

        $('#dateFilter').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customDateContainer').removeClass('d-none');
            } else {
                $('#customDateContainer').addClass('d-none');
                
                const $form = $(this).closest('form');
                if ($form.length) {
                    $('#filter-loader').removeClass('d-none');
                    $form.submit();
                } else if (typeof window.onDateFilterChange === 'function') {
                    window.onDateFilterChange();
                }
            }
        });

        $('#applyCustomDate').on('click', function(e) {
            const $form = $(this).closest('form');
            if ($form.length) {
                e.preventDefault();
                $('#filter-loader').removeClass('d-none');
                $form.submit();
            } else if (typeof window.onDateFilterChange === 'function') {
                window.onDateFilterChange();
            }
        });
    });

    /**
     * Helper to append date parameters to AJAX requests.
     */
    function getDateFilterParams() {
        let filter = $('#dateFilter').val();
        let params = { date_filter: filter };
        
        if (filter === 'custom') {
            params.start_date = $('#startDate').val();
            params.end_date = $('#endDate').val();
        }
        return params;
    }

    /**
     * Helper to append date parameters to URL strings.
     */
    function getDateFilterQueryString() {
        let params = getDateFilterParams();
        let qs = `date_filter=${params.date_filter}`;
        if (params.date_filter === 'custom') {
            if (params.start_date) qs += `&start_date=${params.start_date}`;
            if (params.end_date) qs += `&end_date=${params.end_date}`;
        }
        return qs;
    }
</script>
