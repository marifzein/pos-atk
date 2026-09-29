@extends('layouts.app')

@section('title', 'History Stock Opname')

@section('content')

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">History Stock Opname</h2>
        <p class="text-sm text-slate-500">Pencatatan penyesuaian stok fisik periodik toko per cabang</p>
    </div>

    {{-- Form Filter Cabang & Mulai SO --}}
    <form id="formSOAction" method="GET" action="{{ url('/stock-opname') }}" class="flex flex-wrap items-center gap-2">
        <div class="w-56">
            <select 
                id="branchSelect" 
                name="branch_id" 
                class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-700 bg-white focus:ring-4 focus:ring-indigo-100 outline-none"
                @if($isSpv) disabled @endif
            >
                @if(!$isSpv)
                    <option value="">Semua Cabang</option>
                @endif
                @foreach($branches as $b)
                    <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>
            @if($isSpv)
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
            @endif
        </div>

        {{-- Tombol Filter --}}
        <x-button color="gray" type="submit">
            <i class="ri-filter-3-line mr-1"></i> Filter
        </x-button>

        {{-- Tombol Mulai SO --}}
        <button 
            type="button" 
            onclick="startOpname()" 
            class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition inline-flex items-center"
        >
            <i class="ri-add-line mr-1"></i> Mulai Stock Opname
        </button>
    </form>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-4">
        {{ session('success') }}
    </x-alert>
@endif

@if(session('warning'))
    <x-alert type="warning" class="mb-4">
        {{ session('warning') }}
    </x-alert>
@endif

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200">
                <tr>
                    <th class="p-3 text-left">No SO</th>
                    <th class="p-3">Cabang</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-center">Item</th>
                    <th class="p-3">Operator</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($opnames as $so)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 font-mono font-bold text-slate-800">{{ $so->opname_no }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md font-medium text-xs">
                                {{ $so->branch->name ?? 'Cabang #' . $so->branch_id }}
                            </span>
                        </td>
                        <td class="p-3">{{ \Carbon\Carbon::parse($so->opname_date)->format('d-m-Y H:i') }}</td>
                        <td class="p-3">
                            @if($so->status == 'OPEN')
                                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-full">OPEN</span>
                            @else
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-xs font-bold rounded-full">POSTED</span>
                            @endif
                        </td>
                        <td class="p-3 text-center font-bold text-slate-700">{{ $so->details_count }}</td>
                        <td class="p-3">{{ $so->user_name }}</td>
                        <td class="p-3 text-center">
                            <a href="{{ url('/stock-opname/' . $so->id) }}">
                                <x-button color="gray" size="sm">
                                    <i class="ri-eye-line mr-1"></i> Buka SO
                                </x-button>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            <i class="ri-inbox-line text-3xl block mb-1"></i>
                            Belum ada riwayat Stock Opname untuk cabang ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-slate-100">
        {{ $opnames->links() }}
    </div>
</div>

@push('scripts')
<script>
function startOpname2() {
    const branchSelect = document.getElementById('branchSelect');
    const branchId = branchSelect ? branchSelect.value : '{{ $selectedBranchId }}';

    if (!branchId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Cabang Dulu!',
            text: 'Untuk memulai Stock Opname, silakan pilih salah satu cabang pada dropdown!',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    // Submit via form POST ke /stock-opname/start
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ url('/stock-opname/start') }}";

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const inputBranch = document.createElement('input');
    inputBranch.type = 'hidden';
    inputBranch.name = 'branch_id';
    inputBranch.value = branchId;
    form.appendChild(inputBranch);

    document.body.appendChild(form);
    form.submit();
}

async function startOpname() {
    const branchSelect = document.getElementById('branchSelect');
    const branchId = branchSelect ? branchSelect.value : '{{ $selectedBranchId }}';

    if (!branchId) {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Cabang Dulu!',
            text: 'Untuk memulai Stock Opname, silakan pilih salah satu cabang pada dropdown!',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    // Tampilkan loading saat memeriksa status cabang
    Swal.fire({
        title: 'Memeriksa Dokumen...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    try {
        let response = await fetch("{{ route('stock-opname.check-active') }}?branch_id=" + branchId);
        let data = await response.json();

        // JIKA MASIH ADA SO YANG OPEN: CEGAT & ARAHKAN
        if (data.has_open) {
            Swal.fire({
                icon: 'warning',
                title: 'Masih Ada SO yang Terbuka!',
                html: `Cabang ini masih memiliki Stock Opname yang belum diposting/dikunci.<br><br><b>No Dokumen:</b> <span class="text-indigo-600 font-mono">${data.opname_no}</span><br><br>Silakan lanjutkan dan selesaikan dokumen tersebut.`,
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="ri-arrow-right-line"></i> Lanjutkan SO Ini',
                cancelButtonText: 'Tutup'
            }).then((res) => {
                if (res.isConfirmed) {
                    window.location.href = "{{ url('/stock-opname') }}/" + data.opname_id;
                }
            });
            return;
        }

        // JIKA TIDAK ADA SO OPEN: KONFIRMASI BUAT BARU
        Swal.fire({
            title: 'Mulai Stock Opname Baru?',
            text: 'Dokumen Stock Opname baru akan dibuat untuk cabang yang dipilih.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Mulai!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ url('/stock-opname/start') }}";

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const inputBranch = document.createElement('input');
                inputBranch.type = 'hidden';
                inputBranch.name = 'branch_id';
                inputBranch.value = branchId;
                form.appendChild(inputBranch);

                document.body.appendChild(form);
                form.submit();
            }
        });

    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal memeriksa status opname: ' + err.message
        });
    }
}
</script>
@endpush

@endsection