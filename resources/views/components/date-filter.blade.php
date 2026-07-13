<div class="d-flex gap-2 align-items-center">
    <select id="dateFilter" name="date_filter" class="form-select form-select-sm shadow-sm" style="width: 150px;">
        <option value="all_time" {{ request('date_filter') === 'all_time' ? 'selected' : '' }}>All Time</option>
        <option value="weekly" {{ request('date_filter') === 'weekly' ? 'selected' : '' }}>Last 7 Days</option>
        <option value="monthly" {{ request('date_filter') === 'monthly' ? 'selected' : '' }}>Last 30 Days</option>
        <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Range</option>
    </select>
    <div id="customDateContainer" class="{{ request('date_filter') === 'custom' ? '' : 'd-none' }} d-flex gap-2">
        <input type="date" id="startDate" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        <input type="date" id="endDate" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        <button class="btn btn-sm btn-primary" id="applyCustomDate">Apply</button>
    </div>
</div>

{{-- Global loading overlay for date-filtered pages --}}
<div id="filter-loader" class="d-none">
    <div class="loader-card">
        <div class="loader-spinner"></div>
        <p class="mb-0 fw-semibold text-body">Loading data&hellip;</p>
    </div>
</div>

<style>
    #filter-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        background: rgba(0, 0, 0, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(2px);
        transition: opacity 0.2s;
    }
    #filter-loader.d-none {
        display: none !important;
    }
    .loader-card {
        background: var(--bs-card-bg, #fff);
        border-radius: 16px;
        padding: 2rem 3rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
        border: 1px solid var(--bs-border-color, rgba(0, 0, 0, 0.1));
    }
    .loader-spinner {
        width: 48px;
        height: 48px;
        border: 5px solid var(--bs-border-color, #e2e8f0);
        border-top-color: var(--bs-primary, #3b82f6);
        border-radius: 50%;
        animation: filter-spin 0.7s linear infinite;
    }
    @keyframes filter-spin {
        to { transform: rotate(360deg); }
    }
</style>
