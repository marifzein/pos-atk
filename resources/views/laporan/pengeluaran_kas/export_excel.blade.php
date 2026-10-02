<table border="1">
    <thead>
        <tr>
            <th colspan="7" style="font-size: 14px; font-weight: bold; text-align: center;">LAPORAN RIWAYAT PENGELUARAN KAS</th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">Periode: {{ $request->start_date ?? 'Awal' }} s/d {{ $request->end_date ?? 'Sekarang' }}</th>
        </tr>
        <tr>
            <th style="background-color: #f2f2f2; font-weight: bold;">Tgl</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Jam</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Cabang</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Kasir</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Kategori</th>
            <th style="background-color: #f2f2f2; font-weight: bold;">Penerima</th>
            <th style="background-color: #f2f2f2; font-weight: bold; text-align: right;">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($expenses as $item)
        <tr>
            <td>{{ $item->created_at->format('d/m/Y') }}</td>
            <td>{{ $item->created_at->format('H:i') }}</td>
            <td>{{ $item->branch->nama_cabang ?? $item->branch->name ?? '-' }}</td>
            <td>{{ $item->user->name ?? '-' }}</td>
            <td>{{ strtoupper(str_replace('_', ' ', $item->kategori)) }}</td>
            <td>{{ $item->penerima }} {{ $item->catatan ? '('.$item->catatan.')' : '' }}</td>
            <td style="text-align: right;">{{ (float) $item->nominal }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="6" style="font-weight: bold; text-align: right;">TOTAL PENGELUARAN</td>
            <td style="font-weight: bold; text-align: right;">{{ (float) $totalNominal }}</td>
        </tr>
    </tbody>
</table>