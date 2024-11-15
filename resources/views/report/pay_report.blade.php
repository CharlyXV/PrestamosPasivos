<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte del Préstamo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Reporte del Préstamo</h1>
    <h2>Encabezado del Préstamo</h2>
    <table>
        <tr>
            <th>Monto</th>
            <td>{{ $loan->amount }}</td>
        </tr>
        <tr>
            <th>Tasa de Interés</th>
            <td>{{ $loan->interest_rate }}</td>
        </tr>
        <tr>
            <th>Plazo</th>
            <td>{{ $loan->term }}</td>
        </tr>
        <tr>
            <th>Periodicidad</th>
            <td>{{ $loan->frequency }}</td>
        </tr>
        <tr>
            <th>Tipo de Calendario</th>
            <td>{{ $loan->calendar_type }}</td>
        </tr>
    </table>
    
    <h2>Plan de Pagos</h2>
    <table>
        <thead>
            <tr>
                <th>Número de Cuota</th>
                <th>Monto de Pago</th>
                <th>Interés Mensual</th>
                <th>Fecha de Pago</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->installment_number }}</td>
                    <td>{{ $payment->amount }}</td>
                    <td>{{ $payment->monthly_interest }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>