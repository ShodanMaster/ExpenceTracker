<h2>Expense Report - {{ ucfirst($type) }} - {{ \Carbon\Carbon::parse($period)->format('F Y') }}</h2>

@if($type === 'both')
    <h3>Credit Transactions</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($creditExpenses as $exp)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($exp->date)->format('d-m-Y') }}</td>
                    <td>{{ $exp->reason->name ?? 'N/A' }}</td>
                    <td>{{ $exp->amount }}</td>
                    <td>{{ $exp->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Debit Transactions</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($debitExpenses as $exp)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($exp->date)->format('d-m-Y') }}</td>
                    <td>{{ $exp->reason->name ?? 'N/A' }}</td>
                    <td>{{ $exp->amount }}</td>
                    <td>{{ $exp->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h3>{{ ucfirst($type) }} Transactions</h3>
    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $exp)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($exp->date)->format('d-m-Y') }}</td>
                    <td>{{ $exp->reason->name ?? 'N/A' }}</td>
                    <td>{{ $exp->amount }}</td>
                    <td>{{ $exp->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
