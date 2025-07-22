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

<div class="d-flex justify-content-between mb-3">
    <h1>Reccuring Transactions</h1>

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
                input.innerHTML = '';

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

            let currentPage = 1;

            function formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            function loadTransactions(page = 1) {
                axios.get(`/transactions?page=${page}&per_page=10`)
                    .then(response => {
                        const res = response.data;
                        console.log(res);
                        const tbody = document.getElementById('transactions-body');
                        const pagination = document.getElementById('pagination');
                        tbody.innerHTML = '';
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
                                    <td>
                                        <button class="btn btn-sm btn-primary me-1" onclick="editTransaction(${txn.id})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
                                                <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
                                            </svg>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteTransaction(${txn.id})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                                <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>`;
                        });

                        pagination.innerHTML = '';
                        for (let i = 1; i <= res.last_page; i++) {
                            pagination.innerHTML += `
                                <li class="page-item ${i === res.current_page ? 'active' : ''}">
                                    <a class="page-link" href="#" onclick="loadTransactions(${i})">${i}</a>
                                </li>`;
                        }
                        currentPage = res.current_page;
                    })
                    .catch(error => {
                        console.error("Error loading transactions", error);
                    });
            }

            window.loadTransactions = loadTransactions;
            loadTransactions(currentPage);
        });
    </script>
@endpush
