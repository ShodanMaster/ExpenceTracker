@extends('layouts.master')
@section('content')

    <!-- Floating + Button for Daily -->
    <div id="daily-button">
        <button type="button"
            class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-3 d-flex align-items-center justify-content-center shadow"
            data-bs-toggle="modal"
            data-bs-target="#addModal"
            style="width: 56px; height: 56px; z-index: 1055;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                class="bi bi-plus-lg" viewBox="0 0 16 16">
                <path fill-rule="evenodd"
                    d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2" />
            </svg>
        </button>
    </div>

    <!-- Export PDF Button for Monthly/Yearly -->
    <div id="pdf-button" class="d-none">
        <button type="button"
            class="btn btn-danger rounded-circle position-fixed bottom-0 end-0 m-3 d-flex align-items-center justify-content-center shadow"
            data-bs-toggle="modal" data-bs-target="#reportModal"
            style="width: 56px; height: 56px; z-index: 1055;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-earmark-arrow-down" viewBox="0 0 16 16">
                <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.793L6.354 9.146a.5.5 0 1 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0-.708-.708L8.5 10.293z"/>
                <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
            </svg>
        </button>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h1 class="modal-title fs-5" id="addModalLabel"></h1>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm">
                    <input type="hidden" id="expense-date" name="expenseDate">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold me-3">Type:</label>
                            <div class="d-inline-flex gap-3 align-items-center">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="type" id="credit" value="credit" required>
                                    <label class="form-check-label" for="credit">Credit</label>
                                </div>
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="type" id="debit" value="debit" checked>
                                    <label class="form-check-label" for="debit">Debit</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="reason" name="reason" placeholder="Reason" list="reason-list" required>
                                    <datalist id="reason-list">
                                        @foreach ($reasons as $reason)
                                            <option value="{{ $reason->name }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Amount" required>
                                </div>
                            </div>
                        </div>
                        <textarea class="form-control" name="description" id="description" placeholder="Description"></textarea>
                    </div>
                    <div class="modal-footer bg-dark">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h1 class="modal-title fs-5" id="editModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    <input type="hidden" id="edit-expense-date" name="expenseDate">
                    <input type="hidden" id="expense-id" name="expenseId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold me-3">Type:</label>
                            <div class="d-inline-flex gap-3 align-items-center">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="edit-type" id="edit-credit" value="credit" checked required>
                                    <label class="form-check-label" for="edit-credit">Credit</label>
                                </div>
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="edit-type" id="edit-debit" value="debit">
                                    <label class="form-check-label" for="edit-debit">Debit</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="edit-reason" name="reason" placeholder="Reason" list="reason-list" required>
                                    <datalist id="reason-list">
                                        @foreach ($reasons as $reason)
                                            <option value="{{ $reason->name }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <input type="number" step="0.01" class="form-control" id="edit-amount" name="amount" placeholder="Amount" required>
                                </div>
                            </div>
                        </div>
                        <textarea class="form-control" name="description" id="edit-description" placeholder="Description"></textarea>
                    </div>
                    <div class="modal-footer bg-dark">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PDF Modalt -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h1 class="modal-title fs-5" id="reportModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reportForm">
                    <input type="hidden" name="expensePeriod" id="report-expense-period">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Transaction Type</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transaction_type" id="both" value="both" checked>
                                    <label class="form-check-label" for="both">Both</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transaction_type" id="credit" value="credit">
                                    <label class="form-check-label" for="credit">Credit</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="transaction_type" id="debit" value="debit">
                                    <label class="form-check-label" for="debit">Debit</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Export Format</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="format" id="pdf" value="pdf" checked>
                                    <label class="form-check-label" for="pdf">PDF</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="format" id="excel" value="excel">
                                    <label class="form-check-label" for="excel">Excel</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-dark">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Export</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <h1>Expense</h1>

        <div class="btn-group" role="group" aria-label="Select Frequency">
            <input type="radio" class="btn-check" name="frequency_filter" id="daily" value="daily" autocomplete="off" checked>
            <label class="btn btn-outline-primary" for="daily">Daily</label>

            <input type="radio" class="btn-check" name="frequency_filter" id="monthly" value="monthly" autocomplete="off">
            <label class="btn btn-outline-primary" for="monthly">Monthly</label>

            <input type="radio" class="btn-check" name="frequency_filter" id="yearly" value="yearly" autocomplete="off">
            <label class="btn btn-outline-primary" for="yearly">Yearly</label>
        </div>
    </div>


    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-primary" onclick="changeMonth(-1)">&#8592; Prev</button>
        <h3 id="monthYear" class="mb-0"></h3>
        <button class="btn btn-outline-primary" onclick="changeMonth(1)">Next &#8594;</button>
    </div>

    <div id="daily-content">
        <div class="row text-center fw-bold border-bottom pb-2">
            <div class="col">Sun</div>
            <div class="col">Mon</div>
            <div class="col">Tue</div>
            <div class="col">Wed</div>
            <div class="col">Thu</div>
            <div class="col">Fri</div>
            <div class="col">Sat</div>
        </div>

        <div id="calendar" class="mt-2"></div>
        <hr>

        <div class="d-flex justify-content-between">
            <div>
                <h3>Balance:
                    <strong id="balance"></strong>
                </h3>
            </div>
            <div>
                <h3>C/F:
                    <strong id="carry-forward"></strong>
                </h3>
            </div>
        </div>

        <div class="card mt-4" id="expense">
            <div class="card-header text-white text-center bg-dark fs-4 d-flex justify-content-between">
                <span id="title-expense-date"></span>
                <span id="total-transcations"></span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white d-flex justify-content-between">
                                Credits
                                <span id="total-credits"></span>
                            </div>
                            <div class="card-body">
                                <ul id="creditList" class="list-group list-group-flush"></ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-danger">
                            <div class="card-header bg-danger text-white d-flex justify-content-between">
                                Debits
                                <span id="total-debits"></span>
                            </div>
                            <div class="card-body">
                                <ul id="debitList" class="list-group list-group-flush"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="monthly-content" class="d-none">
        <div class="card bg-light p-3 mb-3">
            <div class="d-flex justify-content-between">
                <div>
                <div><strong>Total Income (Credit)</strong><br><span id="monthly-credit">₹0.00</span></div>
                <div class="mt-2">C/F<br><strong id="monthly-cf">₹0.00</strong></div>
                </div>
                <div class="text-end">
                <div><strong>Total Expense (Debit)</strong><br><span id="monthly-debit">₹0.00</span></div>
                <div class="mt-2">Balance<br><strong id="monthly-balance">₹0.00</strong></div>
                </div>
            </div>
        </div>

        <div id="monthly-summary-container"></div>

    </div>

    <div id="yearly-content" class="d-none table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Total Income (Credit)</th>
                    <th>Total Expense (Debit)</th>
                    <th>Balance</th>
                    <th>Credit Details</th>
                    <th>Debit Details</th>
                </tr>
            </thead>
            <tbody id="yearly-summary-body">
                <!-- Yearly summary rows will be injected here -->
            </tbody>
        </table>
    </div>

@endsection

@push('custom-scripts')
<script>
    let currentDate = new Date();
    let selectedElement = null;
    let formattedDate = formatDateToYYYYMMDD(currentDate);
    fetchExpense(formattedDate);

    const modals = ['#addModal', '#editModal'];

    modals.forEach(modalId => {
        const modalEl = document.querySelector(modalId);
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                const form = modalEl.querySelector('form');
                if (form) form.reset();

                const debitRadio = modalEl.querySelector('input[name="type"][value="debit"]');
                if (debitRadio) debitRadio.checked = true;
            });
        }
    });

    document.querySelectorAll('input[name="frequency_filter"]').forEach((radio) => {
        radio.addEventListener('change', function () {
            const frequency = this.value;

            ['daily-content', 'monthly-content', 'yearly-content'].forEach(id =>
                document.getElementById(id).classList.add('d-none')
            );
            document.getElementById('daily-button').classList.add('d-none');
            document.getElementById('pdf-button').classList.add('d-none');

            switch (frequency) {
                case 'daily':
                    document.getElementById('daily-content').classList.remove('d-none');
                    document.getElementById('daily-button').classList.remove('d-none');
                    renderCalendar(currentDate);
                    fetchExpense(formatDateToYYYYMMDD(currentDate));
                    break;

                case 'monthly':
                    document.getElementById('monthly-content').classList.remove('d-none');
                    document.getElementById('pdf-button').classList.remove('d-none');
                    renderCalendar(currentDate);
                    fetchMonthlySummary(currentDate);
                    break;

                case 'yearly':
                    document.getElementById('yearly-content').classList.remove('d-none');
                    document.getElementById('pdf-button').classList.remove('d-none');
                    document.getElementById("monthYear").textContent = currentDate.getFullYear();
                    fetchYearlySummary(currentDate.getFullYear());
                    break;
            }
        });
    });

    function formatDateToDDMMYYYY(date) {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }

    function formatDateToYYYYMMDD(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    }

    function formatDateToYYYYMM(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        return `${yyyy}-${mm}`;
    }

    function renderCalendar(date, frequency) {
        const calendar = document.getElementById("calendar");
        calendar.innerHTML = "";

        if (frequency === 'yearly') {
            // Just show the year, no calendar grid
            document.getElementById("monthYear").textContent = date.getFullYear();
            calendar.innerHTML = `<div class="text-center py-4">Year view - no calendar grid</div>`;
            return;
        }

        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        const todayStr = formatDateToDDMMYYYY(today);

        document.getElementById("monthYear").textContent =
            date.toLocaleString('default', { month: 'long', year: 'numeric' });

        let dayCounter = 1;
        let started = false;

        for (let week = 0; week < 6; week++) {
            const row = document.createElement("div");
            row.className = "row text-center";

            for (let day = 0; day < 7; day++) {
                const col = document.createElement("div");
                col.className = "col border py-4";

                if (!started && day === firstDay) {
                    started = true;
                }

                if (started && dayCounter <= daysInMonth) {
                    col.textContent = dayCounter;
                    const fullDate = new Date(year, month, dayCounter);
                    const fullDateStr = formatDateToDDMMYYYY(fullDate);

                    col.dataset.date = fullDateStr;
                    col.style.cursor = "pointer";

                    if (fullDateStr === todayStr && year === today.getFullYear() && month === today.getMonth()) {
                        col.classList.add("bg-primary", "text-white");
                        selectedElement = col;
                        document.getElementById('addModalLabel').textContent = `Date: ${fullDateStr}`;
                        document.getElementById('editModalLabel').textContent = `Date: ${fullDateStr}`;
                    } else {
                        col.classList.add("bg-light");
                    }

                    const currentDay = dayCounter;

                    col.onclick = function () {
                        if (selectedElement) {
                            selectedElement.classList.remove("bg-primary", "text-white");
                            selectedElement.classList.add("bg-light");
                        }

                        col.classList.remove("bg-light");
                        col.classList.add("bg-primary", "text-white");
                        selectedElement = col;

                        const selectedDate = new Date(year, month, currentDay);
                        const formattedDate = formatDateToYYYYMMDD(selectedDate);
                        const displayDate = formatDateToDDMMYYYY(selectedDate);

                        document.getElementById('addModalLabel').textContent = `Date: ${displayDate}`;
                        document.getElementById('editModalLabel').textContent = `Date: ${displayDate}`;
                        document.getElementById('expense-date').value = formattedDate;

                        fetchExpense(formattedDate);
                    };

                    dayCounter++;
                } else {
                    col.innerHTML = "&nbsp;";
                }

                row.appendChild(col);
            }

            calendar.appendChild(row);
            if (dayCounter > daysInMonth) break;
        }
    }

    function changeMonth(offset) {
        const selectedFrequency = document.querySelector('input[name="frequency_filter"]:checked')?.value;

        if (selectedFrequency === 'yearly') {
            currentDate.setFullYear(currentDate.getFullYear() + offset);
        } else {
            currentDate.setMonth(currentDate.getMonth() + offset);
        }

        selectedElement = null;
        renderCalendar(currentDate, selectedFrequency);

        if (selectedFrequency === 'monthly') {
            fetchMonthlySummary(currentDate);
            updateReportModalContext(currentDate, 'monthly');
        } else if (selectedFrequency === 'yearly') {
            fetchYearlySummary(currentDate.getFullYear());
            updateReportModalContext(currentDate, 'yearly');
        } else if (selectedFrequency === 'daily') {
            fetchExpense(formatDateToYYYYMMDD(currentDate));
        }
    }

    function updateReportModalContext(currentDate, frequency) {
        if (!currentDate || !frequency) return;

        const labelEl = document.getElementById('reportModalLabel');
        const inputEl = document.getElementById('report-expense-period');

        if (!labelEl || !inputEl) return;

        if (frequency === 'monthly') {
            const monthStr = currentDate.toLocaleString('default', { month: 'long' }); // "July"
            const year = currentDate.getFullYear(); // 2025
            labelEl.textContent = `${monthStr} ${year} Report`; // "Month: July 2025 Report"
            inputEl.value = `${year}-${String(currentDate.getMonth() + 1).padStart(2, '0')}`; // "2025-07"
        } else if (frequency === 'yearly') {
            const yearStr = currentDate.getFullYear(); // "2025"
            labelEl.textContent = `Year: ${yearStr} Report`;
            inputEl.value = yearStr;
        }
    }

    document.querySelector('[data-bs-target="#reportModal"]').addEventListener('click', function () {
        const selectedFrequency = document.querySelector('input[name="frequency_filter"]:checked')?.value;
        updateReportModalContext(currentDate, selectedFrequency);
    });

    renderCalendar(currentDate);

    function fetchExpense(date) {
        axios.get('/get-expenses/' + date)
            .then(response => {
                const res = response.data;
                if (res.status === 200) {
                    const data = res.data;

                    document.getElementById('balance').textContent = data.balance;
                    document.getElementById('carry-forward').textContent = data.carry_forward;
                    document.getElementById('title-expense-date').textContent = "Date: " + res.date;
                    document.getElementById('total-transcations').textContent = "Total Transactions: " + (res.credit_count + res.debit_count);
                    document.getElementById('total-credits').textContent = "Total Credits: " + res.credit_count;
                    document.getElementById('total-debits').textContent = "Total Debits: " + res.debit_count;

                    renderTransactionList(data.credit, 'creditList', 'success');
                    renderTransactionList(data.debit, 'debitList', 'danger');
                } else {
                    showErrorList();
                }
            })
            .catch(error => {
                console.error("Error fetching expenses:", error);
                showErrorList();
            });
    }

    function fetchYearlySummary(year) {
        axios.get('/get-yearly-summary/' + year)
            .then(response => {
                if (response.data.status === 200) {
                    renderYearlySummary(response.data.data);
                } else {
                    console.warn("No yearly data available");
                }
            })
            .catch(error => {
                console.error("Error fetching yearly summary:", error);
            });
    }

    function renderYearlySummary(data) {
        const container = document.getElementById('yearly-content');
        container.classList.remove('d-none');

        const tbody = document.getElementById('yearly-summary-body');
        tbody.innerHTML = ''; // clear previous rows

        // Check if there are any details to show
        if (!data.grouped_by_month || data.grouped_by_month.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center">No details available.</td></tr>`;
            return;
        }

        data.grouped_by_month.forEach(entry => {
            // Summarized reason-based credit/debit breakdown
            const creditDetails = entry.credit.map(c =>
                `${c.reason || 'N/A'}: $${c.amount.toFixed(2)}`
            ).join('<br>');

            const debitDetails = entry.debit.map(d =>
                `${d.reason || 'N/A'}: $${d.amount.toFixed(2)}`
            ).join('<br>');

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${entry.month}</td>
                <td>${entry.total_credit.toFixed(2)}</td>
                <td>${entry.total_debit.toFixed(2)}</td>
                <td>${entry.balance.toFixed(2)}</td>
                <td>${creditDetails || '—'}</td>
                <td>${debitDetails || '—'}</td>
            `;
            tbody.appendChild(row);
        });
    }

    function renderTransactionList(list, elementId, color) {
        const container = document.getElementById(elementId);
        if (!list || list.length === 0) {
            container.innerHTML = `<li class="list-group-item text-center text-muted">No ${color === 'success' ? 'Credit' : 'Debit'} Data</li>`;
            return;
        }

        container.innerHTML = list.map(item => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div><span>${item.reason}</span></div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-${color} fw-semibold">₹${item.amount}</span>
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                        onclick="editExpense(${item.id}, ${item.amount}, '${item.reason}', ${JSON.stringify(item.description)}, '${item.type}', '${item.date}')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                        </svg>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteExpense(${item.id})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                        </svg>
                    </button>
                </div>
            </li>
        `).join('');
    }

    function showErrorList() {
        document.getElementById('creditList').innerHTML =
            `<li class="list-group-item text-center text-muted">Error loading Credit Data</li>`;
        document.getElementById('debitList').innerHTML =
            `<li class="list-group-item text-center text-muted">Error loading Debit Data</li>`;
    }

    function fetchMonthlySummary(date) {
        const month = formatDateToYYYYMM(date);
        axios.get('/get-monthly-summary/' + month)
            .then(response => {

                if (response.data.status === 200) {
                    renderMonthlySummary(response.data.data);
                } else {
                    console.warn("No monthly data available");
                }
            })
            .catch(error => {
                console.error("Error fetching monthly summary:", error);
            });
    }

    function renderMonthlySummary(data) {
        document.getElementById('monthly-credit').textContent = `₹${data.total_credit.toFixed(2)}`;
        document.getElementById('monthly-debit').textContent = `₹${data.total_debit.toFixed(2)}`;
        document.getElementById('monthly-balance').textContent = `₹${data.balance.toFixed(2)}`;
        document.getElementById('monthly-cf').textContent = `₹${data.carry_forward.toFixed(2)}`;

        const container = document.getElementById('monthly-summary-container');
        container.innerHTML = '';

        data.grouped_by_date.forEach(entry => {
            const creditHTML = entry.credit.length > 0
                ? entry.credit.map(txn => `
                    <div class="d-flex justify-content-between">
                        <span>${txn.reason}</span>
                        <span class="text-success">₹${(+txn.amount || 0).toFixed(2)}</span>
                    </div>
                `).join('')
                : '<div class="text-muted">No Income</div>';

            const debitHTML = entry.debit.length > 0
                ? entry.debit.map(txn => `
                    <div class="d-flex justify-content-between">
                        <span>${txn.reason}</span>
                        <span class="text-danger">₹${(+txn.amount || 0).toFixed(2)}</span>
                    </div>
                `).join('')
                : '<div class="text-muted">No Expense</div>';

            const card = `
                <div class="card mb-4 p-3">
                    <div class="fw-bold fs-6 mb-3">${entry.date_formatted}</div>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-success">Income (Credit)</h6>
                                    ${creditHTML}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="card border-danger h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-danger">Expense (Debit)</h6>
                                    ${debitHTML}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        <strong>Balance</strong><br>
                        ₹${entry.balance.toFixed(2)}
                    </div>
                </div>
            `;

            container.innerHTML += card;
        });
    }

    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const date = document.getElementById('expense-date').value || formatDateToYYYYMMDD(new Date);
        const type = document.querySelector('input[name="type"]:checked')?.value || '';
        const reason = document.getElementById('reason').value;
        const amount = document.getElementById('amount').value;
        const description = document.getElementById('description').value;

        axios.post('{{ route('expenses.store') }}', {
            date: date,
            type: type,
            reason: reason,
            amount: amount,
            description: description,
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            const data = response.data;

            if (data.status == 200) {
                let message = data.message || 'Successfully Stored';
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });

                bootstrap.Modal.getInstance(document.getElementById('addModal')).hide();

                document.getElementById('addForm').reset();
                document.getElementById('debit').checked = true;

                const selectedDate = date;

                fetchExpense(selectedDate);

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something Went Wrong',
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during the request.',
            });
        });
    });

    document.querySelector('[data-bs-target="#addModal"]').addEventListener('click', function () {
        const label = document.getElementById('addModalLabel');

        if (selectedElement && selectedElement.dataset.date) {
            const parts = selectedElement.dataset.date.split('-');

            const selectedDate = new Date(parseInt(parts[2]), parseInt(parts[1]) + 1, parseInt(parts[0]));

            label.textContent = `Date: ${selectedElement.dataset.date}`;
        } else {
            const today = new Date();
            const todayStr = formatDateToDDMMYYYY(today);
            label.textContent = `Date: ${todayStr}`;
        }
    });

    function editExpense(id, amount, reason, description, type, date){

        document.getElementById('expense-id').value = id;

        document.getElementById('edit-expense-date').value = date;

        type === 'credit' ? document.getElementById('edit-credit').checked = true : document.getElementById('edit-debit').checked = true;

        document.getElementById('edit-reason').value = reason;
        document.getElementById('edit-amount').value = amount;
        document.getElementById('edit-description').value = description ?? '';
    }

    document.getElementById('editForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const id = document.getElementById('expense-id').value;
        const date = document.getElementById('expense-date').value || formatDateToYYYYMMDD(new Date);
        const type = document.querySelector('input[name="edit-type"]:checked')?.value || '';
        const reason = document.getElementById('edit-reason').value;
        const amount = document.getElementById('edit-amount').value;
        const description = document.getElementById('edit-description').value;

        axios.put(`/expenses/${id}`, {
            id: id,
            date: date,
            type: type,
            reason: reason,
            amount: amount,
            description: description,
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            const data = response.data;

            if (data.status == 200) {
                let message = data.message || 'Successfully Updated';
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });

                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();

                document.getElementById('editForm').reset();
                document.getElementById('edit-debit').checked = true;

                const selectedDate = date;

                fetchExpense(selectedDate);

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something Went Wrong',
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred during the request.',
            });
        });
    });

    function deleteExpense(id) {
        Swal.fire({
            title: 'Are you sure delete this expense?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/expenses/${id}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    const data = response.data;

                    if (data.status === 200) {
                        let message = data.message || 'Successfully deleted';
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: message,
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                        });

                        const selectedDate = document.getElementById('expense-date')?.value || formatDateToYYYYMMDD(new Date);
                        fetchExpense(selectedDate);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred during the request.',
                    });
                });
            }
        });
    }

    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('inside');

        const period = document.getElementById('report-expense-period').value;
        const transactionType = document.querySelector('input[name="transaction_type"]:checked').value;
        const format = document.querySelector('input[name="format"]:checked').value;

        axios.post('/report', {
            period: period,
            transaction_type: transactionType,
            format: format,
        }, {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            responseType: 'blob' // Important for file download
        })
        .then(response => {
            const blob = new Blob([response.data], { type: response.headers['content-type'] });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `expenses-${month}.${format}`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
            Swal.fire({
                icon: 'success',
                title: 'Exported',
                text: `Expenses for ${month} exported successfully.`,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        })
        .catch(error => {
            console.error('Error exporting expenses:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while exporting expenses.',
            });
        });
    });
</script>
@endpush
