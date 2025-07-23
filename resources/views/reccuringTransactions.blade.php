@extends('layouts.master')
@section('content')

<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-dark text-white">
            <h1 class="modal-title fs-5" id="addTransactionModalLabel">Add Transaction</h1>
            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" id="addTransactionForm">
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
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="frequency" class="form-label">Frequency</label>
                        <select class="form-select" id="frequency" name="frequency" required>
                            <option value="" disabled selected>Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3" id="frequency_value_wrapper" style="display: none;">
                        <label for="frequency_value" class="form-label" id="frequency_label">Frequency Detail</label>
                        <select class="form-select" id="frequency_value" name="frequency_value"></select>
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

<!-- Edit Transaction Modal -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header bg-dark text-white">
            <h1 class="modal-title fs-5" id="editTransactionModalLabel">Edit Transaction</h1>
            <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editTransactionForm">
            <div class="modal-body">
                <input type="hidden" id="editTransactionId" name="id">

                <div class="mb-3">
                    <label class="form-label fw-semibold me-3">Type:</label>
                    <div class="d-inline-flex gap-3 align-items-center">
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="radio" name="type" id="editCredit" value="credit" required>
                            <label class="form-check-label" for="editCredit">Credit</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input" type="radio" name="type" id="editDebit" value="debit">
                            <label class="form-check-label" for="editDebit">Debit</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <input type="text" class="form-control" id="editReason" name="reason" placeholder="Reason" list="reason-list" required>
                        <datalist id="reason-list">
                            @foreach ($reasons as $reason)
                                <option value="{{ $reason->name }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input type="number" step="0.01" class="form-control" id="editAmount" name="amount" placeholder="Amount" required>
                        {{-- <input type="number" step="0.01" class="form-control" id="editAmount" name="amount" placeholder="Amount" required> --}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="editFrequency" class="form-label">Frequency</label>
                        <select class="form-select" id="editFrequency" name="frequency" required>
                            <option value="" disabled>Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="editFrequencyValueWrapper" style="display: none;">
                        <label for="editFrequencyValue" class="form-label">Frequency Detail</label>
                        <select class="form-select" id="editFrequencyValue" name="frequency_value"></select>
                    </div>
                </div>

                <textarea class="form-control" name="description" id="editDescription" placeholder="Description"></textarea>
            </div>

            <div class="modal-footer bg-dark">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Transaction</button>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between mb-3">
    <h1>Reccuring Transactions</h1>

    <div class="input-group mb-3 w-50">
        <input type="text" class="form-control" id="searchInput" placeholder="Search by reason, type, frequency...">
    </div>

    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        Add Transaction
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped" id="reccuringTransactionsTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Frequency</th>
                <th>Next Occurence</th>
                <th>Description</th>
                <th>Active</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="transactions-body"></tbody>
    </table>
</div>

<nav>
    <ul class="pagination" id="pagination"></ul>
</nav>

@endsection
@push('custom-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const frequency = document.getElementById('frequency');
            const wrapper = document.getElementById('frequency_value_wrapper');
            const input = document.getElementById('frequency_value');
            const label = document.getElementById('frequency_label');

            frequency.addEventListener('change', function () {
                const value = this.value;
                input.innerHTML = ''; // Clear existing options

                switch (value) {
                    case 'daily':
                        wrapper.style.display = 'none';
                        break;
                    case 'weekly':
                        label.innerText = 'Select Day of Week';
                        wrapper.style.display = 'block';
                        ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].forEach((day, index) => {
                            input.appendChild(new Option(day, index));
                        });
                        break;
                    case 'monthly':
                        label.innerText = 'Select Day of Month';
                        wrapper.style.display = 'block';
                        for (let i = 1; i <= 30; i++) {
                            input.appendChild(new Option(i, i));
                        }
                        break;
                    case 'yearly':
                        label.innerText = 'Select Month';
                        wrapper.style.display = 'block';
                        ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'].forEach((month, index) => {
                            input.appendChild(new Option(month, index + 1));
                        });
                        break;
                    default:
                        wrapper.style.display = 'none';
                }
            });

            function setupFrequencyHandler({ frequencySelect, wrapper, input, label }) {
                frequencySelect.addEventListener('change', function () {
                    const value = this.value;
                    input.innerHTML = ''; // Clear previous options

                    switch (value) {
                        case 'daily':
                            wrapper.style.display = 'none';
                            break;
                        case 'weekly':
                            label.innerText = 'Select Day of Week';
                            wrapper.style.display = 'block';
                            ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
                                .forEach((day, index) => input.appendChild(new Option(day, index)));
                            break;
                        case 'monthly':
                            label.innerText = 'Select Day of Month';
                            wrapper.style.display = 'block';
                            for (let i = 1; i <= 30; i++) {
                                input.appendChild(new Option(i, i));
                            }
                            break;
                        case 'yearly':
                            label.innerText = 'Select Month';
                            wrapper.style.display = 'block';
                            ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
                            'September', 'October', 'November', 'December']
                                .forEach((month, index) => input.appendChild(new Option(month, index + 1)));
                            break;
                        default:
                            wrapper.style.display = 'none';
                    }
                });
            }


            setupFrequencyHandler({
                frequencySelect: document.getElementById('frequency'),
                wrapper: document.getElementById('frequency_value_wrapper'),
                input: document.getElementById('frequency_value'),
                label: document.getElementById('frequency_label')
            });

            // Edit Modal
            setupFrequencyHandler({
                frequencySelect: document.getElementById('editFrequency'),
                wrapper: document.getElementById('editFrequencyValueWrapper'),
                input: document.getElementById('editFrequencyValue'),
                label: document.querySelector('#editFrequencyValueWrapper label')
            });

            document.getElementById('addTransactionForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const url = "{{ route('reccuring-transactions.store') }}";

                axios.post(url, formData)
                    .then(response => {
                        const data = response.data;
                        if (data.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message || 'Successfully Stored',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                            });
                            bootstrap.Modal.getInstance(document.getElementById('addTransactionModal')).hide();
                            document.getElementById('addTransactionForm').reset();
                            document.getElementById('debit').checked = true;
                            loadTransactions(1);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something Went Wrong',
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while adding the transaction.',
                            showConfirmButton: true,
                        });
                    });
            });

function editTransaction(id) {
    axios.get(`/reccuring-transactions/${id}`)
        .then(response => {
            const txn = response.data.data;

            // Fill basic fields
            document.getElementById('editTransactionId').value = txn.id;
            document.getElementById('editReason').value = txn.reason?.name || '';
            document.getElementById('editAmount').value = txn.amount;
            document.getElementById('editDescription').value = txn.description || '';

            // Type radio
            document.getElementById('editCredit').checked = txn.type === 'credit';
            document.getElementById('editDebit').checked = txn.type === 'debit';

            // Frequency dropdown
            const frequencySelect = document.getElementById('editFrequency');
            frequencySelect.value = txn.frequency;

            // Trigger change to populate frequency value options
            frequencySelect.dispatchEvent(new Event('change'));

            // Wait for options to render, then set selected value
            setTimeout(() => {
                const frequencyValueSelect = document.getElementById('editFrequencyValue');
                const val = String(txn.frequency_value); // Always convert to string for matching
                const foundOption = [...frequencyValueSelect.options].find(option => option.value === val);

                if (foundOption) {
                    frequencyValueSelect.value = val;
                } else {
                    console.warn(`Value '${val}' not found in options`);
                }
            }, 100); // Slightly longer delay to ensure DOM updates

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching transaction:', error);
            alert('Failed to load transaction.');
        });
}

            document.getElementById('editTransactionForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const id = document.getElementById('editTransactionId').value;
                const type = document.querySelector('input[name="type"]:checked').value;
                const reason = document.getElementById('editReason').value;
                const amount = document.getElementById('editAmount').value;
                const frequency = document.getElementById('editFrequency').value;
                const frequencyValue = document.getElementById('editFrequencyValue').value;
                const description = document.getElementById('editDescription').value;
                console.log("Frequency Value: " + frequencyValue);

                const payload = {
                    type,
                    reason,
                    amount,
                    frequency,
                    frequencyValue,
                    description
                };

                if (frequency === 'weekly') {
                    payload.day_of_week = frequencyValue;
                } else if (frequency === 'monthly') {
                    payload.day_of_month = frequencyValue;
                } else if (frequency === 'yearly') {
                    payload.month_of_year = frequencyValue;
                }

                axios.patch(`/reccuring-transactions/${id}`, payload)
                    .then(response => {
                        const data = response.data;
                        if (data.status == 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: data.message || 'Successfully Updated',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                            });
                            bootstrap.Modal.getInstance(document.getElementById('editTransactionModal')).hide();
                            document.getElementById('editTransactionForm').reset();
                            document.getElementById('debit').checked = true;
                            loadTransactions(1);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something Went Wrong',
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Error updating transaction:", error);
                        // alert('Failed to update transaction.');
                        Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update transaction.',
                            });
                    });
            });


            function deleteTransaction(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.delete(`/reccuring-transactions/${id}`)
                            .then(response => {
                                const data = response.data;
                                if (data.status == 200) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: data.message || 'Transaction has been deleted.',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        timerProgressBar: true,
                                    });
                                    loadTransactions(currentPage);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Failed to delete transaction.',
                                    });
                                }
                            })
                            .catch(() => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while deleting the transaction.',
                                });
                            });
                    }
                });
            }

            function renderPagination(current, total) {
                const maxVisible = 7;
                let html = '';

                // Previous button
                if (current > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTransactions(${current - 1})">Previous</a></li>`;
                }

                let startPage = Math.max(1, current - Math.floor(maxVisible / 2));
                let endPage = startPage + maxVisible - 1;

                if (endPage > total) {
                    endPage = total;
                    startPage = Math.max(1, endPage - maxVisible + 1);
                }

                if (startPage > 1) {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTransactions(1)">1</a></li>`;
                    if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }

                for (let i = startPage; i <= endPage; i++) {
                    html += `<li class="page-item ${i === current ? 'active' : ''}">
                                <a class="page-link" href="javascript:void(0)" onclick="loadTransactions(${i})">${i}</a>
                            </li>`;
                }

                if (endPage < total) {
                    if (endPage < total - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTransactions(${total})">${total}</a></li>`;
                }

                // Next button
                if (current < total) {
                    html += `<li class="page-item"><a class="page-link" href="#" onclick="loadTransactions(${current + 1})">Next</a></li>`;
                }

                document.getElementById('pagination').innerHTML = html;
            }


            let currentPage = 1;
            let currentSearch = '';

            // Debounce function: limits how often a function can fire
            function debounce(func, delay) {
                let timeout;
                return function (...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), delay);
                };
            }

            document.getElementById('searchInput').addEventListener('input', debounce(function (e) {
                currentSearch = e.target.value.trim();
                loadTransactions(1, currentSearch);
            }, 300));

            function formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            function loadTransactions(page = 1, search = currentSearch) {
                currentPage = page;
                currentSearch = search;

                axios.get(`/transactions?page=${page}&per_page=10&search=${encodeURIComponent(search)}`)
                    .then(response => {
                        const res = response.data;
                        const tbody = document.getElementById('transactions-body');
                        tbody.innerHTML = '';

                        if (res.data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No transactions found.</td>
                                </tr>`;
                            document.getElementById('pagination').innerHTML = '';
                            return;
                        }

                        res.data.forEach((txn, index) => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${(res.current_page - 1) * res.per_page + index + 1}</td>
                                    <td>${txn.type}</td>
                                    <td>${txn.reason?.name || ''}</td>
                                    <td>${txn.amount}</td>
                                    <td>${txn.frequency}</td>
                                    <td>${formatDate(txn.next_occurence)}</td>
                                    <td>${txn.description || ''}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" ${txn.is_active ? 'checked' : ''}>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-primary me-2" onclick="editTransaction(${txn.id})" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                            </svg>
                                        </button>

                                        <button class="btn btn-sm btn-danger" onclick="deleteTransaction(${txn.id})" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>`;
                        });

                        renderPagination(res.current_page, res.last_page);
                    })
                    .catch(error => {
                        console.error("Error loading transactions", error);
                        document.getElementById('transactions-body').innerHTML = `
                            <tr>
                                <td colspan="9" class="text-center text-danger">Failed to load transactions.</td>
                            </tr>`;
                        document.getElementById('pagination').innerHTML = '';
                    });
            }

            window.loadTransactions = loadTransactions;
            window.deleteTransaction = deleteTransaction;
            window.editTransaction = editTransaction;
            loadTransactions(currentPage);
        });
    </script>
@endpush
