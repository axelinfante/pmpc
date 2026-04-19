<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices Export</title>
    <style>
        body {
            font-size: 10px; /* Tamaño de letra más pequeño */
        }
    
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px; /* Tamaño de fuente específico para tablas */
        }
    
        table th, table td {
            border: 1px solid #ddd;
            padding: 5px; /* Ajusta el espaciado */
            text-align: left;
        }
    
        table th {
            background-color: #f2f2f2;
        }
    
        h2 {
            text-align: center;
            font-size: 14px; /* Tamaño de título más pequeño */
        }
    </style>
    
</head>
<body>
    <h2>Invoices Report</h2>
    <table>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
