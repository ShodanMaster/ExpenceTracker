@php
    $isMonthly = preg_match('/^\d{4}-\d{2}$/', $period);
    $displayPeriod = $isMonthly
        ? \Carbon\Carbon::parse($period)->format('F Y')  // e.g., July 2025
        : $period;                                      // e.g., 2025
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        h2, h3 { margin: 10px 0; }
    </style>
</head>
<body>
    <h2>Expense Report - {{ ucfirst($type) }} - {{ $displayPeriod }}</h2>

    @if($type === 'both')
        <h3>Credit Transactions</h3>
        <table>
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
        <table>
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
        <table>
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
</body>
</html>
