@extends('layouts.master')
@section('content')


    <button type="button" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-3 d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#addModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/>
        </svg>
    </button>

    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <h1 class="modal-title fs-5" id="addModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addForm">
                    <input type="hidden" id="expense-date" name="expenseDate">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold me-3">Type:</label>
                            <div class="d-inline-flex gap-3 align-items-center">
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="type" id="credit" value="credit" checked required>
                                    <label class="form-check-label" for="credit">Credit</label>
                                </div>
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="type" id="debit" value="debit">
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <h1>Expense</h1>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-outline-primary" onclick="changeMonth(-1)">&#8592; Prev</button>
        <h3 id="monthYear" class="mb-0"></h3>
        <button class="btn btn-outline-primary" onclick="changeMonth(1)">Next &#8594;</button>
    </div>

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
    <div id="expense" class="d-none mt-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">Credits</div>
                    <div class="card-body">
                        <ul id="creditList" class="list-group list-group-flush"></ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">Debits</div>
                    <div class="card-body">
                        <ul id="debitList" class="list-group list-group-flush"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('custom-scripts')
<script>
    let currentDate = new Date();
    let selectedElement = null;
    let formattedDate = formatDateToYYYYMMDD(currentDate);
    fetchExpense(formattedDate);

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


    function renderCalendar(date) {
        const calendar = document.getElementById("calendar");
        calendar.innerHTML = "";

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

                        const selectedDate = new Date(year, month, currentDay); // ✅ Correct scoped day
                        const formattedDate = formatDateToYYYYMMDD(selectedDate);
                        const displayDate = formatDateToDDMMYYYY(selectedDate);

                        console.log("SelectedDate: " + selectedDate);
                        console.log("formattedDate: " + formattedDate);
                        console.log("displayDate: " + displayDate);

                        document.getElementById('addModalLabel').textContent = `Date: ${displayDate}`;
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
        currentDate.setMonth(currentDate.getMonth() + offset);
        selectedElement = null;
        renderCalendar(currentDate);
    }

    renderCalendar(currentDate);

    function fetchExpense(date) {
        console.log('inside: '+ date);

        axios.get('/get-expenses/' + date)
            .then(response => {
                const res = response.data;

                if (res.status === 200) {
                    const data = res.data;

                    const debitList = data.debit || [];
                    const creditList = data.credit || [];

                    if (creditList.length === 0) {
                        document.getElementById('creditList').innerHTML =
                            `<li class="list-group-item text-center text-muted">No Credit Data</li>`;
                    } else {
                        document.getElementById('creditList').innerHTML = creditList.map(item =>
                            `<li class="list-group-item d-flex justify-content-between">
                                <span>${item.reason}</span>
                                <span class="text-success fw-semibold">₹${item.amount}</span>
                            </li>`
                        ).join('');
                    }

                    if (debitList.length === 0) {
                        document.getElementById('debitList').innerHTML =
                            `<li class="list-group-item text-center text-muted">No Debit Data</li>`;
                    } else {
                        document.getElementById('debitList').innerHTML = debitList.map(item =>
                            `<li class="list-group-item d-flex justify-content-between">
                                <span>${item.reason}</span>
                                <span class="text-danger fw-semibold">₹${item.amount}</span>
                            </li>`
                        ).join('');
                    }

                    document.getElementById('expense').classList.remove('d-none');
                } else {
                    document.getElementById('creditList').innerHTML =
                        `<li class="list-group-item text-center text-muted">No Credit Data</li>`;
                    document.getElementById('debitList').innerHTML =
                        `<li class="list-group-item text-center text-muted">No Debit Data</li>`;
                }

                document.getElementById('expense').classList.remove('d-none');
            })
            .catch(error => {
                console.error("Error fetching expenses:", error);

                document.getElementById('creditList').innerHTML =
                    `<li class="list-group-item text-center text-muted">Error loading Credit Data</li>`;
                document.getElementById('debitList').innerHTML =
                    `<li class="list-group-item text-center text-muted">Error loading Debit Data</li>`;

                document.getElementById('expense').classList.remove('d-none');
            });
    }

    document.getElementById('addForm').addEventListener('submit', function(e) {
        e.preventDefault();

        console.log("jibhuvgycftxdr "+new Date);

        const date = document.getElementById('expense-date').value || formatDateToYYYYMMDD(new Date);
        const type = document.querySelector('input[name="type"]:checked')?.value || '';
        const reason = document.getElementById('reason').value;
        const amount = document.getElementById('amount').value;
        const description = document.getElementById('description').value;

        console.log(date);

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
            console.log(data);

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
                document.getElementById('credit').checked = true;

                const selectedDate = date;
                console.log("submitted Date: " + selectedDate);

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
        // const input = document.getElementById('expense-date');

        if (selectedElement && selectedElement.dataset.date) {
            const parts = selectedElement.dataset.date.split('-');

            const selectedDate = new Date(parseInt(parts[2]), parseInt(parts[1]) + 1, parseInt(parts[0]));

            label.textContent = `Date: ${selectedElement.dataset.date}`;
            // input.value = selectedDate.toISOString().split('T')[0];
        } else {
            const today = new Date();
            const todayStr = formatDateToDDMMYYYY(today);
            label.textContent = `Date: ${todayStr}`;
            // input.value = today.toISOString().split('T')[0];
        }
    });

</script>
@endpush
