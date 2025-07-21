@extends('layouts.master')

@section('content')
<h1 class="mb-4">Dashboard</h1>

<div class="btn-group mb-4" role="group" aria-label="Time Range Filters">
    <input type="radio" class="btn-check" name="filterType" id="daily" value="daily" autocomplete="off" checked>
    <label class="btn btn-outline-primary" for="daily">Daily</label>

    <input type="radio" class="btn-check" name="filterType" id="monthly" value="monthly" autocomplete="off">
    <label class="btn btn-outline-primary" for="monthly">Monthly</label>

    <input type="radio" class="btn-check" name="filterType" id="yearly" value="yearly" autocomplete="off">
    <label class="btn btn-outline-primary" for="yearly">Yearly</label>
</div>

<div class="container-fluid">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Income vs Expense</div>
                <div class="card-body">
                    <canvas id="incomeVsExpenseChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Category-wise Expense</div>
                <div class="card-body">
                    <canvas id="categoryPieChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Top Categories</div>
                <div class="card-body">
                    <canvas id="topCategoryChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@push('custom-scripts')
    <script src="{{ asset('asset/js/chart.js') }}"></script>

<script>
    function renderChart(ctxId, type, data, options = {}) {

        const ctx = document.getElementById(ctxId)?.getContext('2d');
        if (!ctx) return;

        if (window[ctxId] instanceof Chart) {
            window[ctxId].destroy();
        }

        const isPie = ['pie', 'doughnut'].includes(type);



        const baseOptions = {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: context => {

                            const label = context.label || context.dataset.label || '';
                            const value = context.parsed !== undefined ? context.parsed : '';
                            if (typeof value === 'object' && value !== null && 'y' in value) {
                                return `${label}: ₹${Number(value['y']).toLocaleString()}`;
                            }
                            return `${label}: ₹${Number(value).toLocaleString()}`;

                        }
                    }
                },
                legend: {
                    position: isPie ? 'top' : 'bottom'
                }
            },
            ...(isPie ? {} : {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₹' + value.toLocaleString()
                        }
                    }
                }
            }),
        };

        window[ctxId] = new Chart(ctx, {
            type,
            data,
            options: Object.assign({}, baseOptions, options)
        });
    }

    function normalizeDataArray(arr) {
        return Array.isArray(arr) ? arr.map(v => {
            const num = Number(v);
            return isNaN(num) ? 0 : num;
        }) : [];
    }

    function normalizeDatasets(datasets) {
        return (datasets || []).map(ds => ({
            ...ds,
            data: normalizeDataArray(ds.data || [])
        }));
    }

    function fetchChartData(type = 'daily') {
        axios.get(`/dashboard/chart-data`, { params: { type } })
            .then(response => {
                const {
                    credits = [],
                    debits = [],
                    categories = { labels: [], datasets: [] },
                    topCategories = { labels: [], datasets: [] },
                    incomeVsExpense = {
                        labels: ['Income', 'Expense'],
                        datasets: [{
                            data: [0, 0],
                            backgroundColor: ['rgba(75, 192, 192, 0.6)', 'rgba(255, 99, 132, 0.6)']
                        }]
                    }
                } = response.data;

                const fallbackLabels = ['No Data'];
                const fallbackData = [0];

                renderChart('incomeVsExpenseChart', 'pie', {
                    labels: incomeVsExpense.labels || fallbackLabels,
                    datasets: normalizeDatasets(incomeVsExpense.datasets).length
                        ? normalizeDatasets(incomeVsExpense.datasets)
                        : [{
                            data: fallbackData,
                            backgroundColor: ['#ccc']
                        }]
                });

                renderChart('categoryPieChart', 'pie', {
                    labels: categories.labels?.length ? categories.labels : fallbackLabels,
                    datasets: normalizeDatasets(categories.datasets).length
                        ? normalizeDatasets(categories.datasets)
                        : [{
                            data: fallbackData,
                            backgroundColor: ['#ddd']
                        }]
                });
                
                const normalizedTop = normalizeDatasets(topCategories.datasets);

                const hasValidData = normalizedTop.length &&
                    Array.isArray(topCategories.labels) &&
                    topCategories.labels.length === normalizedTop[0].data.length &&
                    normalizedTop[0].data.every(v => typeof v === 'number' && !isNaN(v));

                renderChart('topCategoryChart', 'bar', {
                    labels: hasValidData ? topCategories.labels : fallbackLabels,
                    datasets: hasValidData ? normalizedTop : [{
                        label: 'Top Categories',
                        data: fallbackData,
                        backgroundColor: ['#ccc']
                    }]
                });

            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        fetchChartData();
        document.querySelectorAll('input[name="filterType"]').forEach(radio => {
            radio.addEventListener('change', () => fetchChartData(radio.value));
        });
    });
</script>

@endpush
