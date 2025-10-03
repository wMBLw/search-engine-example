<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Search Engine Dashboard')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }


        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading-spinner.active {
            display: flex;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- API Configuration -->
    <script>
        const API_BASE_URL = '{{ url("/api") }}';
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        // Global API Helper
        const API = {
            async call(endpoint, options = {}) {
                const token = localStorage.getItem('auth_token');

                const headers = {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    ...options.headers
                };

                if (token) {
                    headers['Authorization'] = `Bearer ${token}`;
                }

                try {
                    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
                        ...options,
                        headers
                    });

                    console.log(`API Response: ${endpoint}`, { status: response.status });

                    // Parse response
                    let data;
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        console.error('Non-JSON response:', text);
                        throw new Error('Server returned non-JSON response');
                    }

                    if (response.status === 401) {
                        console.warn('Unauthorized, clearing token');
                        localStorage.removeItem('auth_token');
                        if (!window.location.pathname.includes('login')) {
                            window.location.href = '{{ route("login") }}';
                        }
                        throw new Error('Unauthorized');
                    }

                    if (!response.ok) {
                        console.error('API Error:', data);
                        throw { message: data.message || 'API Error', errors: data.errors };
                    }

                    return data;
                } catch (error) {
                    console.error('API Exception:', error);
                    throw error;
                }
            },

            get(endpoint, params = {}) {
                const queryString = new URLSearchParams(params).toString();
                const url = queryString ? `${endpoint}?${queryString}` : endpoint;
                return this.call(url, { method: 'GET' });
            },

            post(endpoint, body = {}) {
                return this.call(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(body)
                });
            }
        };

        // Loading Helper
        const Loading = {
            show() {
                document.getElementById('loadingSpinner').classList.add('active');
            },
            hide() {
                document.getElementById('loadingSpinner').classList.remove('active');
            }
        };

        // Toast Notification Helper
        const Toast = {
            show(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
                toast.style.zIndex = '10000';
                toast.style.minWidth = '300px';
                toast.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-${this.getIcon(type)} me-2"></i>
                        <span>${message}</span>
                        <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 5000);
            },

            getIcon(type) {
                const icons = {
                    success: 'check-circle',
                    danger: 'x-circle',
                    warning: 'exclamation-triangle',
                    info: 'info-circle'
                };
                return icons[type] || 'info-circle';
            },

            success(message) { this.show(message, 'success'); },
            error(message) { this.show(message, 'danger'); },
            warning(message) { this.show(message, 'warning'); },
            info(message) { this.show(message, 'info'); }
        };
    </script>

    @yield('scripts')
</body>
</html>

