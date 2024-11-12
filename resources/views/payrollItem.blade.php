<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<html>

<head>
    <style>
        table {
            border: 1px solid black;
            border-collapse: collapse;
            width: 100%;
        }

        tr.section {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            border-collapse: collapse;
            width: 100%;
        }

        tr.section>table {
            border: none;
            padding-left: 1rem;
            width: 75%;
        }

        .currency {
            font-family: DejaVu Sans;
            text-align: right;
            width: 5%;
        }

        .amount {
            text-align: right;
            width: 20%;
        }
    </style>
</head>

<body>
    <h1>Asia Pacific College</h1>
    <p>3 Humabon Place, Magallanes, Makati City, Philippines 1232</p>
    <table>
        <tr class="section">
            <td><h2 class="text-transform:uppercase;">{{ $item->user->name }}</h2></td>
        </tr>
        <tr class="section">
            <td><b>Email: {{ $item->user->email }}</b></td>
        </tr>
        <tr class="section">
            <td>
                Payroll Period:
                {{ \Carbon\Carbon::parse($item->cutoff->start_date)->format('F j, Y') }}
                to
                {{ \Carbon\Carbon::parse($item->cutoff->end_date)->format('F j, Y') }}
            </td>
        </tr>
        <tr class="section">
            <table>
                @foreach ($item->itemAdditions as $addition)
                    <tr>
                        <td>{{ $addition->addition->name }}</td>
                        <td class="currency">&#x20B1;</td>
                        <td class="amount">{{ number_format($addition->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold;">
                    <td>Gross Pay</td>
                    <td class="currency">&#x20B1;</td>
                    <td class="amount">{{ number_format($item->itemAdditions->sum('amount'), 2) }}</td>
                </tr>
            </table>
        </tr>
        <tr class="section">
            <table>
                @foreach ($item->itemDeductions as $deduction)
                    <tr>
                        <td>{{ $deduction->deduction->name }}</td>
                        <td class="currency">&#x20B1;</td>
                        <td class="amount">{{ number_format($deduction->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold;">
                    <td>Total Deductions</td>
                    <td class="currency">&#x20B1;</td>
                    <td class="amount">{{ number_format($item->itemDeductions->sum('amount'), 2) }}</td>
                </tr>
            </table>
        </tr>
        <tr class="section">
            <td>
                Net Pay:
                <span class="currency">&#x20B1;</span>
                {{ number_format($item->amount, 2) }}
            </td>
        </tr>
    </table>
</body>

</html>
