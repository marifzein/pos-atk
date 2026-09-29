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
    // Prioritaskan cabang transaksi atau cabang pesanan
    $branch = $transaction->branch ?? $transaction->order?->branch ?? null;
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

<div>Nota   : <b>{{ $transaction->no_nota }}</b></div>
@if($transaction->order)
<div>Ref SP : {{ $transaction->order->no_pesanan }}</div>
@endif
<div>Tgl    : {{ $transaction->created_at->format('d-m-Y H:i') }}</div>
<div>Pelanggan: {{ $customer->nama ?? ($transaction->pelanggan ?: 'Umum') }}</div>
<div>Kasir  : {{ $transaction->cashier->name ?? 'Kasir' }}</div>

<hr>

<table width="100%">
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

<table width="100%">
<tr>
    <td>Subtotal</td>
    <td align="right">
        {{ number_format($transaction->subtotal, 0, ',', '.') }}
    </td>
</tr>
@if(($transaction->diskon ?? 0) > 0)
<tr>
    <td>Diskon</td>
    <td align="right">-{{ number_format($transaction->diskon, 0, ',', '.') }}</td>
</tr>
@endif
<tr class="total-row">
    <td>TOTAL</td>
    <td align="right">
        {{ number_format($transaction->grand_total, 0, ',', '.') }}
    </td>
</tr>
</table>

<hr>

<table width="100%">
@if($transaction->cash > 0)
<tr>
    <td>Cash</td>
    <td align="right">
        {{ number_format($transaction->cash, 0, ',', '.') }}
    </td>
</tr>
@endif

@if($transaction->voucher > 0)
<tr>
    <td>Voucher</td>
    <td align="right">
        {{ number_format($transaction->voucher, 0, ',', '.') }}
    </td>
</tr>
@endif

@if($transaction->card > 0)
<tr>
    <td>Card/Non-Cash</td>
    <td align="right">
        {{ number_format($transaction->card, 0, ',', '.') }}
    </td>
</tr>
@endif

@if(($transaction->hutang ?? 0) > 0)
<tr>
    <td>Kasbon</td>
    <td align="right">{{ number_format($transaction->hutang, 0, ',', '.') }}</td>
</tr>
@endif

<tr>
    <td><b>Kembali</b></td>
    <td align="right">
        <b>{{ number_format($transaction->kembalian, 0, ',', '.') }}</b>
    </td>
</tr>
</table>

<hr>

<div>
    Total Item : {{ $transaction->details->sum('qty') }}
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