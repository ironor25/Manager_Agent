<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <script>
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    </script>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Manager Agent - Premium Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    
    <!-- Premium Theme CSS -->
    <link href="{{ asset('css/premium-theme.css') }}" rel="stylesheet">
    
    <!-- html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    @stack('page-style')
</head>
<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @include('sidebar')

    <div class="app-wrapper">
        @include('header')

        <main class="main-content">
            @yield('page-content')
        </main>

        @include('footer')
    </div>

    <!-- jQuery & Bootstrap Bundle with Popper -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Marked.js for Markdown parsing -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <script>
        // Sidebar Toggle Logic
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        });

        document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('show');
            this.classList.remove('show');
        });
        
        // Dark Mode Toggle Logic
        document.getElementById('darkModeToggle')?.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            const icon = document.getElementById('darkModeIcon');
            if(icon) {
                if(newTheme === 'dark') {
                    icon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const theme = document.documentElement.getAttribute('data-bs-theme');
            const icon = document.getElementById('darkModeIcon');
            if(icon && theme === 'dark') {
                icon.classList.replace('fa-moon', 'fa-sun');
            }
        });

        // Setup CSRF Token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Prevent DataTables from showing pop-up alerts on aborted/failed Ajax calls
        if ($.fn.dataTable) {
            $.fn.dataTable.ext.errMode = 'throw';
        }

        // Global PDF Export Helper for Graph-Based Analysis pages
        window.exportPageToPDF = function(elementSelector, filename) {
            // Using browser native print-to-pdf which is more reliable 
            // and doesn't conflict with DOM elements like html2pdf
            if (filename) {
                document.title = filename.replace('.pdf', '');
            }
            window.print();
        };

        // Global DataTable Excel/CSV Export Helper (handles server-side and client-side tables)
        window.exportDataTableToCSV = function(tableSelector, filename) {
            const table = $(tableSelector).DataTable();
            const ajaxUrl = table.ajax.url();
            
            if (!ajaxUrl) {
                window.exportDOMTableToCSV(tableSelector, filename);
                return;
            }

            const loader = $('<div id="excel-export-loader" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.75);z-index:99999;display:flex;align-items:center;justify-content:center;flex-direction:column;backdrop-filter:blur(2px);"><div class="spinner-border text-success" role="status"></div><div class="mt-2 fw-semibold">Preparing Excel Export...</div></div>');
            $('body').append(loader);

            // Clone active search and sort params but fetch all rows
            const params = $.extend(true, {}, table.ajax.params());
            params.length = 100000;
            params.start = 0;

            $.ajax({
                url: ajaxUrl,
                data: params,
                dataType: 'json',
                success: function(response) {
                    loader.remove();
                    const data = response.data || [];
                    const columns = table.settings()[0].aoColumns;
                    
                    // Filter out action columns and columns with no valid titles
                    const validCols = columns.filter(col => {
                        const hasData = col.data !== undefined && col.data !== null;
                        const isAction = col.data === 'action' || col.data === 'actions' || (col.sTitle && (col.sTitle.toLowerCase().includes('action') || col.sTitle.toLowerCase().includes('edit')));
                        return hasData && !isAction;
                    });
                    
                    // Build CSV Header
                    const headers = validCols.map(col => col.sTitle || col.data);
                    let csvContent = "\uFEFF"; // UTF-8 BOM for Excel compatibility
                    csvContent += headers.map(h => `"${String(h).replace(/"/g, '""')}"`).join(",") + "\n";
                    
                    // Build CSV Rows
                    data.forEach(row => {
                        const rowData = validCols.map(col => {
                            let val = '';
                            if (typeof col.data === 'function') {
                                val = col.data(row, 'display');
                            } else if (col.data && row[col.data] !== undefined) {
                                val = row[col.data];
                            } else if (col.name && row[col.name] !== undefined) {
                                val = row[col.name];
                            }
                            
                            if (val === null || val === undefined) {
                                val = '';
                            } else {
                                // Strip HTML tags and clean up string for CSV format
                                val = String(val).replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                                val = val.replace(/"/g, '""');
                            }
                            return `"${val}"`;
                        });
                        csvContent += rowData.join(",") + "\n";
                    });
                    
                    // Trigger browser download
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement("a");
                    const url = URL.createObjectURL(blob);
                    link.setAttribute("href", url);
                    link.setAttribute("download", filename || 'export.csv');
                    link.style.visibility = 'hidden';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function(err) {
                    loader.remove();
                    console.error('Export failed:', err);
                    alert('Failed to export data to Excel.');
                }
            });
        };

        // Fallback DOM Table CSV Exporter (for static or fully rendered tables)
        window.exportDOMTableToCSV = function(tableSelector, filename) {
            const table = $(tableSelector);
            let csvContent = "\uFEFF"; // UTF-8 BOM
            
            table.find('tr').each(function() {
                const rowData = [];
                $(this).find('th, td').each(function() {
                    const text = $(this).text().trim();
                    const isAction = $(this).find('button, a').length > 0 || text.toLowerCase().includes('action');
                    if (!isAction) {
                        rowData.push(`"${text.replace(/"/g, '""')}"`);
                    }
                });
                if (rowData.length > 0) {
                    csvContent += rowData.join(",") + "\n";
                }
            });
            
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename || 'export.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };
    </script>
    
    <!-- Global Toast Notification -->
    @if(session('success'))
    <div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
            <div class="d-flex">
                <div class="toast-body fw-medium py-3 px-4" style="font-size: 0.95rem;">
                    <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.getElementById('successToast');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
    @endif

    @auth
        @include('ai-agent.widget')
    @endauth

    @stack('page-script')
</body>
</html>


