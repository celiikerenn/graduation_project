<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expenses Report</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111827;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 4px;
        }
        p {
            margin-top: 0;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Expenses Report</h1>
    <p>Period: {{ $year }}-{{ sprintf('%02d', $month) }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th class="text-right">Amount ({{ $currencySymbol ?? '₺' }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
                @php
                    $date = \Carbon\Carbon::parse($expense['expense_date'] ?? null);
                @endphp
                <tr>
                    <td>{{ $date->format('Y-m-d') }}</td>
                    <td>{{ $expense['category_name'] ?? '' }}</td>
                    <td>{{ $expense['description'] ?? '' }}</td>
                    <td class="text-right">
                        {{ number_format((float)($expense['amount'] ?? 0), 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No expenses found for this month.</td>
                </tr>
            @endforelse
            @if(!empty($expenses))
                <tr>
                    <td></td>
                    <td></td>
                    <td><strong>Total</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format((float)($total ?? 0), 2, ',', '.') }}</strong>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>

