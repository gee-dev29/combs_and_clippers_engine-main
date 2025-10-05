<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Billing Invoice</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="UTF-8">
    <style media="all">
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            font-size: 1.2rem;
            font-family: '<?php echo $font_family; ?>';
            font-weight: normal;
            direction: <?php echo $direction; ?>;
            text-align: <?php echo $text_align; ?>;
            padding: 0;
            margin: 0;
        }

        .gry-color *,
        .gry-color {
            color: #000;
        }

        table {
            width: 100%;
        }

        table th {
            font-weight: normal;
        }

        table.padding th {
            padding: .5rem .5rem;
        }

        table.padding td {
            padding: .5rem .5rem;
        }

        table.sm-padding td {
            padding: .1rem .7rem;
        }

        .border-bottom td,
        .border-bottom th {
            border-bottom: 1px solid #eceff4;
        }

        .text-left {
            text-align: <?php echo $text_align; ?>;
        }

        .text-right {
            text-align: <?php echo $not_text_align; ?>;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div>
        <div style="background: #eceff4;padding: 1rem;">
            <table>
                <tr>
                    <td>
                        <img src="{{ asset('img/logo.png') }}" alt="logo" style="height: 32px; width: 70px;">
                    </td>
                </tr>
            </table>
        </div>
        @foreach ($invoices as $invoice)
            <div style="padding: 1.5rem;">
                <table class="padding text-left small border-bottom">
                    <thead>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Invoice no</th>
                            <td width="15%" class="text-left">{{ $invoice->invoice_number }}</td>
                        </tr>
                    </thead>
                    <tbody class="strong">
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Plan</th>
                            <td width="15%" class="text-left">{{ $invoice->plan }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Currency</th>
                            <td width="15%" class="text-left">{{ $invoice->currency }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Amount</th>
                            <td width="15%" class="text-left currency">{{ number_format($invoice->amount, 2) }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Status</th>
                            <td width="15%" class="text-left">{{ $invoice->status == 1 ? 'Paid' : 'Unpaid' }}</td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Billing date</th>
                            <td width="15%" class="text-left">{{ $invoice->formatted_billing_date() }} </td>
                        </tr>
                        <tr class="gry-color" style="background: #eceff4;">
                            <th width="35%" class="text-left">Next Billing date</th>
                            <td width="15%" class="text-left">{{ $invoice->formatted_next_billing_date() }} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="page-break"></div>
        @endforeach
    </div>
</body>

</html>
