@extends('layouts.master')

@extends('layouts.app')

@section('content')

<h1 class="text-center fw-bold mb-5">Dashboard</h1>

<div class="container mb-5">

  <!-- Time Range Filters -->
  <div class="row mb-4">
    <div class="col text-center">
      <div class="btn-group" role="group" aria-label="Time Range Filters">
        <input type="radio" class="btn-check" name="filterType" id="daily" value="daily" autocomplete="off" checked>
        <label class="btn btn-outline-primary" for="daily">Daily</label>

        <input type="radio" class="btn-check" name="filterType" id="monthly" value="monthly" autocomplete="off">
        <label class="btn btn-outline-primary" for="monthly">Monthly</label>

        <input type="radio" class="btn-check" name="filterType" id="yearly" value="yearly" autocomplete="off">
        <label class="btn btn-outline-primary" for="yearly">Yearly</label>
      </div>
    </div>
  </div>

  <!-- Month Navigation -->
  <div class="row justify-content-center align-items-center mb-3">
    <div class="col-auto">
      <button class="btn btn-outline-primary" onclick="changeMonth(-1)">
        &#8592; Prev
      </button>
    </div>
    <div class="col-auto">
      <h3 id="monthYear" class="mb-0 fw-semibold">August 2025</h3>
    </div>
    <div class="col-auto">
      <button class="btn btn-outline-primary" onclick="changeMonth(1)">
        Next &#8594;
      </button>
    </div>
  </div>

</div>

<!-- Dashboard Widgets -->
<div class="container-fluid">
  <div class="row g-4">

    <!-- In Wallet -->
    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header text-center fw-semibold bg-light">In Wallet</div>
        <div class="card-body d-flex flex-column justify-content-center align-items-center">
          <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" fill="currentColor" class="bi bi-wallet2 mb-2" viewBox="0 0 16 16">
            <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499zM5.562 3H13V1.78a.5.5 0 0 0-.621-.484zM1.5 4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5z"/>
          </svg>
          <h4 id="inWallet" class="fw-bold mb-0">--</h4>
        </div>
      </div>
    </div>

    <!-- Income vs Expense -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header text-center fw-semibold bg-light">Income vs Expense</div>
            <div class="card-body p-3">
                <div class="ratio ratio-1x1">
                    <canvas id="incomeVsExpenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>


    <!-- Category-wise Expense -->
    <div class="col-md-6 col-xl-3">
      <div class="card shadow-sm h-100 border-0">
        <div class="card-header text-center fw-semibold bg-light">Category-wise Expense</div>
        <div class="card-body d-flex justify-content-center align-items-center">
          <canvas id="categoryPieChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Categories -->
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header text-center fw-semibold bg-light">Top Categories</div>
            <div class="card-body p-3">
                <div class="ratio ratio-1x1">
                    <canvas id="topCategoryChart" style="max-width: 100%; max-height: 100%;"></canvas>

                    {{-- <canvas id="topCategoryChart"></canvas> --}}
                </div>
            </div>
        </div>
    </div>

  </div>
</div>

@endsection

@push('custom-scripts')
    <script src="{{ asset('asset/js/chart.js') }}"></script>

    <script>
        let currentDate = new Date();

        function formatDateToYYYYMMDD(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function updateHeaderDate() {
            const selectedFrequency = document.querySelector('input[name="filterType"]:checked')?.value;
            const headerEl = document.getElementById('monthYear');

            if (!headerEl) return;

            const options = {
                daily: { day: '2-digit', month: 'short', year: 'numeric' },
                monthly: { month: 'long', year: 'numeric' },
                yearly: { year: 'numeric' }
            };

            const format = options[selectedFrequency] || options['daily'];
            const formatted = currentDate.toLocaleDateString('en-US', format);
            headerEl.textContent = formatted;
        }

        function renderChart(ctxId, type, data, options = {}) {
            const ctx = document.getElementById(ctxId)?.getContext('2d');
            if (!ctx) return;

            if (window[ctxId] instanceof Chart) {
                window[ctxId].destroy();
            }

            const isPie = ['pie', 'doughnut'].includes(type);

            const isEmptyData = !data.datasets?.length || data.datasets[0].data.every(v => !v || v === 0);

            if (isEmptyData) {
                data = {
                    labels: ['No Data'],
                    datasets: [{
                        label: 'No Data',
                        data: [1],
                        backgroundColor: ['#e0e0e0']
                    }]
                };
            }

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

        function changeMonth(offset) {
            const selectedFrequency = document.querySelector('input[name="filterType"]:checked')?.value;

            if (selectedFrequency === 'yearly') {
                currentDate.setFullYear(currentDate.getFullYear() + offset);
            } else if (selectedFrequency === 'monthly') {
                currentDate.setMonth(currentDate.getMonth() + offset);
            } else {
                currentDate.setDate(currentDate.getDate() + offset);
            }

            updateHeaderDate();
            const formattedDate = formatDateToYYYYMMDD(currentDate);
            fetchChartData(selectedFrequency, formattedDate);
        }

        function fetchChartData(type = 'daily', date = null) {
            axios.get(`/dashboard/chart-data`, {
                params: {
                    type,
                    date
                }
            })
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

                document.getElementById('inWallet').innerHTML =
                    response.data.in_wallet.toLocaleString(undefined, {
                        style: 'currency',
                        currency: 'INR'
                    });

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
                    }, {
                    scales: {
                        x: {
                        ticks: {
                            maxRotation: 30,
                            minRotation: 30,
                            autoSkip: false,
                            font: {
                            size: 10
                            }
                        },
                        grid: {
                            display: false
                        }
                        },
                        y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₹' + value.toLocaleString()
                        },
                        grid: {
                            drawBorder: false
                        }
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                        display: false
                        }
                    }
                });

            })
            .catch(error => {
                console.error('Error fetching chart data:', error);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const selectedFrequency = document.querySelector('input[name="filterType"]:checked')?.value || 'daily';
            const formattedDate = formatDateToYYYYMMDD(currentDate);

            updateHeaderDate();
            fetchChartData(selectedFrequency, formattedDate);

            document.querySelectorAll('input[name="filterType"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    const newDate = formatDateToYYYYMMDD(currentDate);
                    updateHeaderDate();
                    fetchChartData(radio.value, newDate);
                });
            });
        });
    </script>

@endpush
