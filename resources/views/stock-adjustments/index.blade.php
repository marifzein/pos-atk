@extends('layouts.app')

@section('title', 'Stock Adjustment')

@section('content')

<x-page-header
    title="Stock Adjustment (SA)"
    subtitle="Kelola penyesuaian stok barang rusak, cacat, atau expired"
>
    <x-slot:action>
        <div class="flex items-center gap-3">

            {{-- Dropdown Pilih Cabang --}}
            <div class="w-56">
                <select 
                    id="select-branch-sa" 
                    class="w-full bg-white border border-slate-300 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm"
                >
                    {{-- <option value="">-- Pilih Cabang --</option> --}}
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- <a href="{{ route('stock-adjustments.create') }}"> --}}
                <x-button color="primary" full onclick="goToCreateSA()">
                    <i class="ri-add-line"></i>
                    Buat SA Baru
                </x-button>
            {{-- </a> --}}
        </div>
    </x-slot:action>
</x-page-header>

<x-card>
    {{-- Toolbar Pencarian --}}
    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex gap-3">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari Nomor SA..."
                class="w-80 rounded-xl border border-slate-300 px-4 py-3"
            >
            <x-button>
                <i class="ri-search-line"></i>
                Cari
            </x-button>
        </form>
    </div>

    {{-- Tabel Data --}}
    <x-table>
        <x-table-header>
            <tr>
                <x-table-head class="text-left">No SA</x-table-head>
                <x-table-head class="text-left">Tanggal</x-table-head>
                <x-table-head class="text-left">Operator</x-table-head>
                <x-table-head class="text-left">Catatan</x-table-head>
                <x-table-head class="text-center">Status</x-table-head>
                <x-table-head class="text-center">Aksi</x-table-head>
            </tr>
        </x-table-header>

        <tbody>
        @forelse($adjustments as $sa)
            <tr>
                <x-table-cell class="font-semibold text-indigo-600">
                    {{ $sa->nomor_sa }}
                </x-table-cell>
                <x-table-cell>
                    {{ \Carbon\Carbon::parse($sa->tgl_sa)->format('d-m-Y') }}
                </x-table-cell>
                <x-table-cell>
                    {{ $sa->user->name }}
                </x-table-cell>
                <x-table-cell>
                    <span class="text-slate-500 text-sm">{{ $sa->catatan ?? '-' }}</span>
                </x-table-cell>
                <x-table-cell class="text-center">
                    @if($sa->status === 'draft')
                        <x-badge color="gray">Draft</x-badge>
                    @else
                        <x-badge color="green">Closed / Posted</x-badge>
                    @endif
                </x-table-cell>
                <x-table-cell class="text-center">
                    <div class="flex justify-center gap-2">
                        @if($sa->status === 'draft')
                        
                            <a href="{{ route('stock-adjustments.edit', $sa->id) }}" class="text-blue-600 hover:underline">
                            {{-- <a href="{{ route('stock-adjustments.edit', $sa) }}"> --}}
                                <x-button color="blue" size="sm" title="Edit Draft">
                                    <i class="ri-edit-line"></i>
                                </x-button>
                            </a>
                        @else
                            <a href="{{ route('stock-adjustments.edit', $sa->id) }}">
                                <x-button color="blue" size="sm" title="Lihat Detail">
                                    <i class="ri-eye-line"></i>
                                </x-button>
                            </a>
                            {{-- <a href="{{ route('stock-adjustments.edit', $sa->id) }}">
                                <x-button color="gray" size="sm" class="opacity-50 cursor-not-allowed" title="Sudah Terkunci" >
                                    <i class="ri-lock-line"></i>
                                </x-button>
                            </a> --}}
                        @endif
                    </div>
                </x-table-cell>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-empty-state
                        icon="ri-file-shield-2-line"
                        title="Belum ada Stock Adjustment"
                        description="Klik tombol Buat SA Baru untuk mencatat penyesuaian barang keluar."
                    />
                </td>
            </tr>
        @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $adjustments->links() }}
    </div>
</x-card>

@push('scripts')
<script>
function goToCreateSA() {
    const branchSelect = document.getElementById('select-branch-sa');
    const branchId = branchSelect.value;
    const branchName = branchSelect.options[branchSelect.selectedIndex].text.trim();

    Swal.fire({
        title: 'Konfirmasi Cabang',
        text: `Apakah anda akan membuat SA di cabang ${branchName}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Lanjut!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('stock-adjustments.create') }}?branch_id=" + branchId;
        }
    });
}
</script>
@endpush

@endsection