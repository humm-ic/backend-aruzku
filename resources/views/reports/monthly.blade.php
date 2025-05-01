<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            margin-top: 0;
        }
        .summary {
            margin-bottom: 30px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .summary-table th {
            background-color: #f5f5f5;
            text-align: left;
        }
        .transactions {
            margin-bottom: 30px;
        }
        .transactions h2 {
            margin-bottom: 10px;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }
        .transactions-table th, .transactions-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .transactions-table th {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .positive {
            color: #28a745;
        }
        .negative {
            color: #dc3545;
        }
        .category-chart {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan</h1>
        <p>Periode: {{ $period }}</p>
        <p>Nama: {{ $user->name }}</p>
    </div>
    
    <div class="summary">
        <h2>Ringkasan</h2>
        <table class="summary-table">
            <tr>
                <th>Total Pemasukan</th>
                <td class="positive">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total Pengeluaran</th>
                <td class="negative">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Saldo</th>
                <td class="{{ $balance >= 0 ? 'positive' : 'negative' }}">
                    Rp {{ number_format($balance, 0, ',', '.') }}
                </td>
            </tr>
        </table>
    </div>
    
    <div class="category-chart">
        <h2>Pengeluaran per Kategori</h2>
        <table class="summary-table">
            <tr>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
            @foreach($expenseByCategory as $category => $amount)
            <tr>
                <td>{{ $category }}</td>
                <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
                <td>{{ $totalExpense > 0 ? number_format(($amount / $totalExpense) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="category-chart">
        <h2>Pemasukan per Kategori</h2>
        <table class="summary-table">
            <tr>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Persentase</th>
            </tr>
            @foreach($incomeByCategory as $category => $amount)
            <tr>
                <td>{{ $category }}</td>
                <td>Rp {{ number_format($amount, 0, ',', '.') }}</td>
                <td>{{ $totalIncome > 0 ? number_format(($amount / $totalIncome) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="transactions">
        <h2>Daftar Pemasukan</h2>
        <table class="transactions-table">
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
            @foreach($incomes as $income)
            <tr>
                <td>{{ $income->date->format('d/m/Y') }}</td>
                <td>{{ $income->category->name }}</td>
                <td>{{ $income->description ?: '-' }}</td>
                <td>Rp {{ number_format($income->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="transactions">
        <h2>Daftar Pengeluaran</h2>
        <table class="transactions-table">
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->date->format('d/m/Y') }}</td>
                <td>{{ $expense->category->name }}</td>
                <td>{{ $expense->description ?: '-' }}</td>
                <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    
    <div class="footer">
        <p>Laporan ini dibuat pada {{ $generatedAt }}</p>
        <p>Aplikasi Keuangan Pribadi</p>
    </div>
</body>
</html>