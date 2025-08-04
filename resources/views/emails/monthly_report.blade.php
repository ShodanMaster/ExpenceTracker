@php
    $name = $user->name ?? 'User';
@endphp

<p>Dear {{ $name }},</p>

<p>Your monthly report for <strong>{{ $month }}</strong> is attached as a PDF and Excel file.</p>

<p>Thanks,<br>Your Expense Tracker Team</p>
