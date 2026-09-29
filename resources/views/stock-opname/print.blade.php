<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>

Laporan Stock Opname

</title>

<style>

body{

font-family:Arial;

font-size:13px;

margin:30px;

}

table{

width:100%;

border-collapse:collapse;

margin-top:20px;

}

th,td{

border:1px solid #000;

padding:6px;

}

th{

background:#eee;

}

h2{

margin-bottom:0;

}

small{

color:#666;

}

.right{

text-align:right;

}

.center{

text-align:center;

}

</style>

</head>

<body>

{{-- Header / Kop Laporan Toko --}}
<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
    <div>
        <div style="font-size: 18px; font-weight: bold; text-transform: uppercase; color: #1e293b;">
            {{ $setting->nama_toko ?? 'TataKas' }}
        </div>
        @if(isset($setting->alamat))
            <div style="font-size: 11px; color: #64748b; max-width: 450px; line-height: 1.4;">
                {{ $setting->alamat }} {{ $setting->telepon ? ' | Telp: ' . $setting->telepon : '' }}
            </div>
        @endif
    </div>
    <div style="text-align: right;">
        <h2 style="margin: 0; font-size: 16px; color: #0f172a;">LAPORAN STOCK OPNAME</h2>
        <small style="color: #64748b;">Dicetak: {{ now()->format('d-m-Y H:i') }}</small>
    </div>
</div>

<hr style="border: 0; border-top: 2px solid #0f172a; margin-bottom: 15px;">

{{-- <h2>

LAPORAN STOCK OPNAME

</h2>

<small>

Dicetak :
{{ now()->format('d-m-Y H:i') }}

</small>

<hr> --}}

<table style="width:40%;">

<tr>

<td style="width:120px; font-weight:bold;">

No SO

</td>

<td>

{{ $stockOpname->opname_no }}

</td>

</tr>

<tr>

<td style="font-weight:bold;">

Tanggal

</td>

<td>

{{ $stockOpname->opname_date }}

</td>

</tr>

<tr>

<td style="font-weight:bold;">

Operator

</td>

<td>

{{ $stockOpname->user_name }}

</td>

</tr>

<tr>

<td style="font-weight:bold;">

Cabang

</td>

<td>

{{ $stockOpname->branch->name }}

</td>

</tr>

</table>

<table>

<thead>

<tr>

<th>No</th>

<th>Kode</th>

<th>Nama Barang</th>

<th>Stok Sistem</th>

<th>Stok Fisik</th>

<th>Selisih</th>

<th>Keterangan</th>

</tr>

</thead>

<tbody>

@foreach($details as $i=>$item)

<tr>

<td class="center">

{{ $i+1 }}

</td>

<td>

{{ $item->product->sku }}

</td>

<td>

{{ $item->product->name }}

</td>

<td class="center">

{{ $item->stock_system }}

</td>

<td class="center">

{{ $item->stock_physical }}

</td>

<td class="center">

{{ $item->difference }}

</td>

<td>

{{ $item->notes }}

</td>

</tr>

@endforeach

</tbody>

</table>

<br>

Jumlah Item :

<b>

{{ $details->count() }}

</b>

<br><br><br>

<table style="border:none">

<tr style="border:none">

<td style="border:none" class="center">

Petugas

<br><br><br><br>

(______________)

</td>

<td style="border:none" class="center">

Supervisor

<br><br><br><br>

(______________)

</td>

</tr>

</table>

<script>

window.print();

</script>

</body>

</html>