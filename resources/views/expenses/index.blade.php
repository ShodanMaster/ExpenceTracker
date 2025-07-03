@extends('layouts.master')
@section('content')
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
                <form id="">
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('custom-scripts')
<script>
    let currentDate = new Date();
    let selectedElement = null;

    function renderCalendar(date) {
        const calendar = document.getElementById("calendar");
        calendar.innerHTML = "";

        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        const today = new Date();
        const todayStr = today.toLocaleDateString('en-CA'); // Format: YYYY-MM-DD

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
                    const fullDateStr = fullDate.toLocaleDateString('en-CA');

                    col.dataset.date = fullDateStr;
                    col.style.cursor = "pointer";

                    if (fullDateStr === todayStr && year === today.getFullYear() && month === today.getMonth()) {
                        col.classList.add("bg-primary", "text-white");
                        selectedElement = col;
                        console.log("Selected Date:", fullDateStr);
                    } else {
                        col.classList.add("bg-light");
                    }

                    col.onclick = function () {
                        if (selectedElement) {
                            selectedElement.classList.remove("bg-primary", "text-white");
                            selectedElement.classList.add("bg-light");
                        }
                        col.classList.remove("bg-light");
                        col.classList.add("bg-primary", "text-white");
                        selectedElement = col;

                        console.log("Selected Date:", col.dataset.date);
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
</script>
@endpush
