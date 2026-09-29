<!DOCTYPE html>
<html>
<head>
    <title>Print Bukti Kas Keluar</title>
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
    $branch = $expense->branch;
@endphp

<!-- HEADER TOKO & CABANG -->
<div class="center">
    <h3 style="margin-bottom: 2px; text-transform: uppercase;">
        {{ $shopSetting->nama_toko ?? 'CAHAYA BUSUR GROUP' }}
    </h3>
    <div style="font-weight: bold; font-size: 12px; margin-bottom: 2px;">
        {{ strtoupper($branch->nama_cabang ?? $branch->name ?? 'CABANG') }}
    </div>
    <div>{{ $branch->alamat ?? $branch->address ?? ($shopSetting->alamat ?? '-') }}</div>
    <div>Telp: {{ $branch->telepon ?? $branch->phone ?? ($shopSetting->telepon ?? '-') }}</div>
</div>

<hr>

<div class="center" style="font-weight: bold; font-size: 12px; margin: 4px 0;">
    BUKTI KAS KELUAR
</div>

<hr>

<div>No Bukti : <b>{{ $expense->no_bukti }}</b></div>
<div>Tgl      : {{ $expense->created_at->format('d-m-Y H:i') }}</div>
<div>Kasir    : {{ $expense->user->name ?? 'Kasir' }}</div>
<div>Kategori : {{ strtoupper(str_replace('_', ' ', $expense->kategori)) }}</div>

<hr>

<table>
    <tr>
        <td style="width: 32%;">Penerima</td>
        <td>: <b>{{ $expense->penerima }}</b></td>
    </tr>
    @if($expense->catatan)
    <tr>
        <td>Catatan</td>
        <td>: {{ $expense->catatan }}</td>
    </tr>
    @endif
</table>

<hr>

<table>
    <tr class="total-row">
        <td>TOTAL KELUAR</td>
        <td align="right">Rp {{ number_format($expense->nominal, 0, ',', '.') }}</td>
    </tr>
</table>

<hr>

<!-- TANDA TANGAN BUKTI FISIK -->
<table style="margin-top: 10px; margin-bottom: 10px;">
    <tr class="center">
        <td style="width: 50%;">Penerima,</td>
        <td style="width: 50%;">Kasir,</td>
    </tr>
    <tr>
        <td style="height: 35px;"></td>
        <td style="height: 35px;"></td>
    </tr>
    <tr class="center">
        <td>( ......... )</td>
        <td>( {{ $expense->user->name ?? 'Kasir' }} )</td>
    </tr>
</table>

<hr>

<div class="center">
    {!! nl2br(e($shopSetting->footer_nota ?? 'Harap simpan struk ini sebagai bukti kas keluar yang sah')) !!}
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