<!DOCTYPE html>
<html>
<head>
    <title>Cetak Surat Pesanan (SP)</title>
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

<!-- HEADER TOKO & CABANG -->
<div class="center">
    <!-- 1. Nama Toko dari settings -->
    <h3 style="margin-bottom: 2px; text-transform: uppercase;">
        {{ $shopSetting->nama_toko ?? 'CAHAYA BUSUR GROUP' }}
    </h3>
    
    <!-- 2. Nama Cabang dari branches -->
    <div style="font-weight: bold; font-size: 12px; margin-bottom: 2px;">
        {{ strtoupper($order->branch->name ?? 'CABANG') }}
    </div>

    <!-- 3. Alamat Cabang dari branches -->
    <div>{{ $order->branch->address ?? '-' }}</div>

    <!-- 4. Telepon Cabang dari branches (fallback ke settings jika kosong) -->
    <div>Telp: {{ $order->branch->phone ?? $shopSetting->telepon ?? '-' }}</div>
</div>

<hr>

<div class="center" style="font-weight: bold; font-size: 12px; margin-bottom: 4px;">
    SURAT PESANAN (SP)
</div>

<div>No. SP   : <b>{{ $order->no_pesanan }}</b></div>
<div>Tgl Order: {{ $order->created_at->format('d-m-Y H:i') }}</div>
<div>Estimasi : <b>{{ $order->tgl_estimasi_selesai ? \Carbon\Carbon::parse($order->tgl_estimasi_selesai)->format('d-m-Y H:i') : '-' }}</b></div>
<div>Pelanggan: {{ $order->customer->nama ?? $order->customer_name_manual ?? 'Umum' }}</div>
<div>Kasir    : {{ $order->operator->name ?? 'Kasir' }}</div>

@if($order->catatan)
<hr>
<div>Catatan: {{ $order->catatan }}</div>
@endif

<hr>

<!-- RINCIAN PEKERJAAN / BARANG -->
<table>
@foreach($order->items as $item)
<tr>
    <td colspan="2">
        {{ $item->item_name }}
        @if($item->notes)
            <br><small style="font-style: italic;">* {{ $item->notes }}</small>
        @endif
    </td>
</tr>
<tr>
    <td>
        {{ $item->qty }} x {{ number_format($item->unit_price, 0, ',', '.') }}
    </td>
    <td align="right">
        {{ number_format($item->subtotal, 0, ',', '.') }}
    </td>
</tr>
@endforeach
</table>

<hr>

<!-- BREAKDOWN FINANSIAL SP -->
<table>
<tr class="total-row">
    <td>TOTAL BIAYA</td>
    <td align="right">{{ number_format($order->total_amount, 0, ',', '.') }}</td>
</tr>
<tr>
    <td>Uang Muka (DP)</td>
    <td align="right">{{ $totalDp > 0 ? '-' . number_format($totalDp, 0, ',', '.') : 'Rp 0' }}</td>
</tr>
<tr class="total-row">
    <td>SISA PELUNASAN</td>
    <td align="right">{{ number_format($sisaTagihan, 0, ',', '.') }}</td>
</tr>
</table>

<hr>
<!-- FOOTER NOTA DARI SETTINGS -->
<div class="center">
    {!! nl2br(e($shopSetting->footer_nota ?? 'Barang yang sudah dibeli tidak dapat ditukar kembali')) !!}
</div>

<br>

<div class="center" style="font-size: 10px;">
    STATUS: <b>{{ $order->payment_status === 'lunas' ? 'LUNAS' : ($totalDp > 0 ? 'DP DITERIMA' : 'BELUM DIBAYAR') }}</b>
    <br>
    <i>* Harap simpan struk ini sebagai bukti pengambilan barang *</i>
</div>

<hr>

<div class="center">
    {!! nl2br(e($shopSetting->footer_nota)) !!}
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