@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
<style>
    body { background: #f8fafc; }
    .navbar { background: white; border-bottom: 1px solid #e2e8f0; }
    .stat-card { background: white; border-left: 4px solid #2563eb; }
    .table { background: white; }
    .badge { font-size: 0.875rem; }
</style>
@endsection

@section('content')
<!-- Navbar -->
<nav class="navbar sticky-top">
    <div class="container-fluid px-4">
        <span class="navbar-brand mb-0 h4">Search Engine Dashboard</span>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted" id="userEmail"></span>
            <button class="btn btn-sm btn-outline-danger" onclick="Dashboard.logout()">Logout</button>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container-fluid p-4">
    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <small class="text-muted">Total Contents</small>
                    <h4 class="mb-0 mt-1" id="statTotal">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <small class="text-muted">Total Views</small>
                    <h4 class="mb-0 mt-1" id="statViews">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card">
                <div class="card-body">
                    <small class="text-muted">Total Likes</small>
                    <h4 class="mb-0 mt-1" id="statLikes">-</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchKeyword" placeholder="Search...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="filterType">
                        <option value="">All Types</option>
                        <option value="video">Video</option>
                        <option value="article">Article</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="sortBy">
                        <option value="score">Sort by Score</option>
                        <option value="views">Sort by Views</option>
                        <option value="likes">Sort by Likes</option>
                        <option value="published_at">Sort by Date</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>External ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Provider</th>
                            <th>Tags / Category</th>
                            <th>Views</th>
                            <th>Likes</th>
                            <th>Score</th>
                            <th>Published</th>
                        </tr>
                    </thead>
                    <tbody id="resultsTable">
                        <tr><td colspan="9" class="text-center">Loading...</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav class="mt-3">
                <ul class="pagination justify-content-center mb-0" id="pagination"></ul>
            </nav>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const Dashboard = {
    currentPage: 1,
    searchTimeout: null,

    init() {
        if (!this.checkAuth()) return;

        this.loadUserInfo();
        this.loadStatistics();
        this.performSearch();
        this.setupEventListeners();
    },

    checkAuth() {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            window.location.href = '{{ route("login") }}';
            return false;
        }
        return true;
    },

    setupEventListeners() {
        document.getElementById('searchKeyword').addEventListener('input', () => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.currentPage = 1;
                this.performSearch();
            }, 500);
        });

        document.getElementById('filterType').addEventListener('change', () => {
            this.currentPage = 1;
            this.performSearch();
        });

        document.getElementById('sortBy').addEventListener('change', () => {
            this.currentPage = 1;
            this.performSearch();
        });
    },

    async loadUserInfo() {
        try {
            const response = await API.get('/user');
            const email = response.data?.email || response.email || 'User';
            document.getElementById('userEmail').textContent = email;
        } catch(error) {
            document.getElementById('userEmail').textContent = 'User';
        }
    },

    async loadStatistics() {
        try {
            const response = await API.get('/search/statistics');
            const data = response.data || response;

            document.getElementById('statTotal').textContent = this.formatNumber(data.total_contents);
            document.getElementById('statViews').textContent = this.formatNumber(data.total_views);
            document.getElementById('statLikes').textContent = this.formatNumber(data.total_likes);
        } catch(error) {
            Toast.error('Failed to load statistics');
        }
    },

    async performSearch() {
        const keyword = document.getElementById('searchKeyword').value;
        const type = document.getElementById('filterType').value;
        const sortBy = document.getElementById('sortBy').value;

        const params = { page: this.currentPage, per_page: 20 };
        if (keyword) params.keyword = keyword;
        if (type) params.type = type;
        if (sortBy) params.sort_by = sortBy;

        try {
            Loading.show();
            const response = await API.get('/search', params);
            this.renderResults(response.data || []);
            this.renderPagination(response.meta);
        } catch(error) {
            this.renderEmpty('Failed to load results');
        } finally {
            Loading.hide();
        }
    },

    renderResults(data) {
        const tbody = document.getElementById('resultsTable');

        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No results found</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(item => `
            <tr>
                <td><code class="text-muted">${item.external_id || 'N/A'}</code></td>
                <td><strong>${item.title}</strong></td>
                <td><span class="badge bg-${item.type === 'video' ? 'danger' : 'primary'}">${item.type}</span></td>
                <td>${item.provider?.name || 'N/A'}</td>
                <td>${this.formatTagsAndCategories(item)}</td>
                <td>${this.formatNumber(item.metrics?.views || 0)}</td>
                <td>${this.formatNumber(item.metrics?.likes || 0)}</td>
                <td><strong>${item.scores?.total?.toFixed(2) || 'N/A'}</strong></td>
                <td>${this.formatDate(item.published_at)}</td>
            </tr>
        `).join('');
    },

    formatTagsAndCategories(item) {
        const badges = [];

        // TAGS İŞLEME
        // Case 1: tags direkt array → ["programming", "advanced", "concurrency"]
        // Case 2: tags object içinde category → {"category": ["programming", "architecture"]}

        if (item.tags) {
            if (Array.isArray(item.tags)) {
                // Direkt array
                item.tags.forEach(tag => {
                    badges.push(`<span class="badge bg-info text-dark me-1 mb-1">${tag}</span>`);
                });
            } else if (typeof item.tags === 'object' && item.tags.category && Array.isArray(item.tags.category)) {
                // Object içinde category array
                item.tags.category.forEach(tag => {
                    badges.push(`<span class="badge bg-info text-dark me-1 mb-1">${tag}</span>`);
                });
            }
        }

        return badges.length > 0 ? badges.join('') : '<span class="text-muted">-</span>';
    },

    renderPagination(meta) {
        const container = document.getElementById('pagination');
        if (!meta || meta.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        const pages = [];
        const current = meta.current_page;
        const last = meta.last_page;

        // Previous
        pages.push(`<li class="page-item ${current === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="Dashboard.goToPage(${current - 1})">Prev</a>
        </li>`);

        // Pages
        for(let i = 1; i <= last; i++) {
            if (i === 1 || i === last || (i >= current - 1 && i <= current + 1)) {
                pages.push(`<li class="page-item ${i === current ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="Dashboard.goToPage(${i})">${i}</a>
                </li>`);
            } else if (i === current - 2 || i === current + 2) {
                pages.push('<li class="page-item disabled"><span class="page-link">...</span></li>');
            }
        }

        // Next
        pages.push(`<li class="page-item ${current === last ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="Dashboard.goToPage(${current + 1})">Next</a>
        </li>`);

        container.innerHTML = pages.join('');
    },

    renderEmpty(message) {
        document.getElementById('resultsTable').innerHTML =
            `<tr><td colspan="9" class="text-center text-muted">${message}</td></tr>`;
    },

    async goToPage(page) {
        this.currentPage = page;
        await this.performSearch();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    formatNumber(num) {
        if (!num) return '0';
        return new Intl.NumberFormat().format(num);
    },

    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        const diff = Math.floor((new Date() - date) / 1000);

        if (diff < 60) return 'just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;

        return date.toLocaleDateString();
    },

    logout() {
        if (confirm('Logout?')) {
            localStorage.clear();
            window.location.href = '{{ route("login") }}';
        }
    }
};

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => Dashboard.init());
} else {
    Dashboard.init();
}
</script>
@endsection
