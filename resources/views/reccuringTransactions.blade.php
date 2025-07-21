@extends('layouts.master')
@section('content')

<!-- Modal -->
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
                <textarea class="form-control" name="description" id="description" placeholder="Description"></textarea>
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
            </div>
            <div class="modal-footer bg-dark">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between">
    <h1>Reccuring Transactions</h1>

    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        Add Transaction
    </button>
</div>
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
                input.innerHTML = ''; // clear existing options

                switch (value) {
                    case 'daily':
                        wrapper.style.display = 'none';
                        break;

                    case 'weekly':
                        label.innerText = 'Select Day of Week';
                        wrapper.style.display = 'block';
                        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        days.forEach((day, index) => {
                            const opt = new Option(day, index);
                            input.appendChild(opt);
                        });
                        break;

                    case 'monthly':
                        label.innerText = 'Select Day of Month';
                        wrapper.style.display = 'block';
                        for (let i = 1; i <= 30; i++) {
                            const opt = new Option(i, i);
                            input.appendChild(opt);
                        }
                        break;

                    case 'yearly':
                        label.innerText = 'Select Month';
                        wrapper.style.display = 'block';
                        const months = [
                            'January', 'February', 'March', 'April', 'May', 'June',
                            'July', 'August', 'September', 'October', 'November', 'December'
                        ];
                        months.forEach((month, index) => {
                            const opt = new Option(month, index + 1);
                            input.appendChild(opt);
                        });
                        break;

                    default:
                        wrapper.style.display = 'none';
                }
            });

            document.getElementById('addTransactionForm').addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const url = "{{ route('reccuring-transactions.store') }}"; // Adjust the URL as needed

                axios.post(url, formData)
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

                        bootstrap.Modal.getInstance(document.getElementById('addTransactionModal')).hide();

                        document.getElementById('addTransactionForm').reset();
                        document.getElementById('debit').checked = true;


                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something Went Wrong',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('There was an error!', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while adding the transaction.',
                            showConfirmButton: true,
                        });
                    });
            });
        });

    </script>
@endpush
