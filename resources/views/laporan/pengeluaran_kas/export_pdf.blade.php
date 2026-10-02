<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengeluaran Kas - TataKas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #4b5563;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 7px 10px;
            font-size: 11px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            text-transform: uppercase;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .no-print-area {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background-color: #059669;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        @media print {
            .no-print-area {
                display: none;
            }
            body {
                margin: 0;
            }
            @page {
                size: A4 portrait;
                margin: 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-area">
        <button class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h2>CAHAYA BUSUR GROUP</h2>
        <p>LAPORAN AUDIT RIWAYAT PENGELUARAN KAS</p>
    </div>

    <div class="meta-info">
        <div>
            <b>Cabang:</b> {{ $branch->nama_cabang ?? $branch->name ?? 'Semua Cabang' }}
        </div>
        <div>
            <b>Periode:</b> {{ $request->start_date ? date('d/m/Y', strtotime($request->start_date)) : 'Semua' }} s/d {{ $request->end_date ? date('d/m/Y', strtotime($request->end_date)) : 'Sekarang' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Tgl</th>
                <th style="width: 8%;">Jam</th>
                <th style="width: 15%;">Cabang</th>
                <th style="width: 15%;">Kasir</th>
                <th style="width: 15%;">Kategori</th>
                <th style="width: 20%;">Penerima</th>
                <th style="width: 15%;" class="text-right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $item)
            <tr>
                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                <td>{{ $item->created_at->format('H:i') }}</td>
                <td>{{ $item->branch->nama_cabang ?? $item->branch->name ?? '-' }}</td>
                <td>{{ $item->user->name ?? '-' }}</td>
                <td>{{ strtoupper(str_replace('_', ' ', $item->kategori)) }}</td>
                <td>
                    {{ $item->penerima }}
                    @if($item->catatan)<br><small style="color: #6b7280;">({{ $item->catatan }})</small>@endif
                </td>
                <td class="text-right font-bold">{{ number_format($item->nominal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #9ca3af; padding: 20px;">
                    Tidak ada data pengeluaran kas yang sesuai filter.
                </td>
            </tr>
            @endforelse
            <tr style="background-color: #f9fafb;">
                <td colspan="6" class="text-right font-bold">TOTAL PENGELUARAN</td>
                <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>