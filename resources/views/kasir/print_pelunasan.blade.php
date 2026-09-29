<!DOCTYPE html>
<html>
<head>
    <title>Print Struk Nota Penjualan (NP)</title>
    <style>
        body {
            font-family: monospace;
            width: 58mm;
            margin: 0 auto;
            font-size: 11px;
            color: #000;
        }
        .center {
            text-align: center;
        }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .total-row {
            font-weight: bold;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 1px 0;
            vertical-align: top;
        }
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>

@php
    // Prioritaskan relasi branch dari order atau langsung dari transaction
    $branch = $order->branch ?? $transaction->branch ?? null;

    // Hitung pembayaran riil terdahulu dari tabel order_payments
    $totalBayarSebelumnya = 0;
    if ($order && $order->payments) {
        $totalBayarSebelumnya = (float) $order->payments
            ->where('no_bukti_bayar', '!=', $transaction->no_nota)
            ->sum('nominal');
    }
    
    // Jika totalBayarSebelumnya 0 tapi controller mengirim $totalDp, gunakan $totalDp
    if ($totalBayarSebelumnya == 0 && isset($totalDp) && $totalDp > 0) {
        $totalBayarSebelumnya = $totalDp;
    }

    $sisaHarusDilunasi = max(0, (float) $transaction->grand_total - $totalBayarSebelumnya);
@endphp

<!-- HEADER TOKO & CABANG -->
<div class="center">
    <!-- 1. Nama Toko dari settings -->
    <h3 style="margin-bottom: 2px; text-transform: uppercase;">
        {{ $shopSetting->nama_toko ?? 'CAHAYA BUSUR GROUP' }}
    </h3>
    
    <!-- 2. Nama Cabang dari branches -->
    <div style="font-weight: bold; font-size: 12px; margin-bottom: 2px;">
        {{ strtoupper($branch->nama_cabang ?? $branch->name ?? 'CABANG') }}
    </div>

    <!-- 3. Alamat Cabang dari branches -->
    <div>{{ $branch->alamat ?? $branch->address ?? ($shopSetting->alamat ?? '-') }}</div>

    <!-- 4. Telepon Cabang dari branches (fallback ke settings jika kosong) -->
    <div>Telp: {{ $branch->telepon ?? $branch->phone ?? ($shopSetting->telepon ?? '-') }}</div>
</div>

<hr>

<div>Nota  : <b>{{ $transaction->no_nota }}</b></div>
@if($order)
<div>Ref SP   : {{ $order->no_pesanan }}</div>
@endif
<div>Tgl Lunas: {{ $transaction->created_at->format('d-m-Y H:i') }}</div>
<div>Pelanggan: {{ $customer->nama ?? 'Umum' }}</div>
<div>Kasir    : {{ $transaction->cashier->name ?? 'Kasir' }}</div>

<hr>

<!-- RINCIAN PEKERJAAN / BARANG -->
<table>
@foreach($transaction->details as $item)
<tr>
    <td colspan="2">
        {{ $item->nama_barang }}
    </td>
</tr>
<tr>
    <td>
        {{ $item->qty }} x {{ number_format($item->harga, 0, ',', '.') }}
    </td>
    <td align="right">
        {{ number_format($item->subtotal, 0, ',', '.') }}
    </td>
</tr>
@endforeach
</table>

<hr>

<!-- BREAKDOWN FINANSIAL PELUNASAN -->
<table>
<tr class="total-row">
    <td>TOTAL ORDER</td>
    <td align="right">{{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
</tr>
@if($totalBayarSebelumnya > 0)
<tr>
    <td>Uang Muka / Sudah Bayar</td>
    <td align="right">-{{ number_format($totalBayarSebelumnya, 0, ',', '.') }}</td>
</tr>
@else
<tr>
    <td>Uang Muka (DP)</td>
    <td align="right">Rp 0</td>
</tr>
@endif
<tr>
    <td><b>Sisa Pelunasan</b></td>
    <td align="right"><b>{{ number_format($sisaHarusDilunasi, 0, ',', '.') }}</b></td>
</tr>
</table>

<hr>

<!-- METODE PEMBAYARAN HARI INI -->
<table>
<tr>
    <td>Bayar Saat Ambil</td>
    <td align="right">
        {{ number_format($sisaHarusDilunasi > 0 ? $nominalBayarHariIni : 0, 0, ',', '.') }}
    </td>
</tr>
<tr>
    <td><b>Kembalian</b></td>
    <td align="right"><b>{{ number_format($transaction->kembalian ?? 0, 0, ',', '.') }}</b></td>
</tr>
</table>

<hr>

<div>
    Status: <b>LUNAS (SELESAI)</b>
</div>

<hr>

<div class="center">
    {!! nl2br(e($shopSetting->footer_nota ?? 'Barang yang sudah dibeli tidak dapat ditukar kembali')) !!}
</div>

<br>

<div class="center">
    <button onclick="eksekusiCetak()" style="padding: 5px 15px; cursor: pointer;">
        Cetak Struk
    </button>
</div>

<script>
function eksekusiCetak() {
    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    if (isMobile) {
        var currentUrl = window.location.href;
        var rawBtUrl = "rawbt:" + currentUrl;
        window.location.href = rawBtUrl;
    } else {
        window.print();
    }
}

window.onload = function() {
    eksekusiCetak();
};
</script>

</body>
</html>