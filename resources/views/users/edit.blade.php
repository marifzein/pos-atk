@extends('layouts.app')

@section('title','Edit User')

@section('content')

<x-page-header

    title="Edit User"

    subtitle="Perbarui informasi akun pengguna"

>

    <x-slot:action>

        <a href="{{ route('users.index') }}">

            <x-button color="gray">

                <i class="ri-arrow-left-line"></i>

                Kembali

            </x-button>

        </a>

    </x-slot:action>

</x-page-header>

@if($errors->any())

<x-alert type="error">

    <div class="font-semibold mb-2">

        Terdapat kesalahan:

    </div>

    <ul class="list-disc ml-5">

        @foreach($errors->all() as $error)

            <li>{{ $error }}</li>

        @endforeach

    </ul>

</x-alert>

@endif

<x-card>

<form

    method="POST"

    action="{{ route('users.update',$user) }}"

>

@csrf
@method('PUT')

<div class="grid grid-cols-2 gap-6">

    <x-input

        label="Nama Lengkap"

        name="name"

        icon="ri-user-line"

        :value="$user->name"

        required

    />

    <x-input

        label="Email"

        name="email"

        type="email"

        icon="ri-mail-line"

        :value="$user->email"

        

    />

    <select
            name="role"
            required
            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-300 text-slate-800 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block"
        >
            <option value="Admin" {{ old('role', $user->role) == 'Admin' ? 'selected' : '' }}>Admin</option>
            <option value="Owner" {{ old('role', $user->role) == 'Owner' ? 'selected' : '' }}>Owner</option>
            <option value="Supervisor" {{ old('role', $user->role) == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
            <option value="Kasir" {{ old('role', $user->role) == 'Kasir' ? 'selected' : '' }}>Kasir</option>
            <option value="Staff Barang" {{ old('role', $user->role) == 'Staff Barang' ? 'selected' : '' }}>Staff Barang</option>
            <option value="Staff Jasa" {{ old('role', $user->role) == 'Staff Jasa' ? 'selected' : '' }}>Staff Jasa</option>
        </select>

    

    {{-- Menggunakan komponen kustom checkbox untuk status Aktif --}}
    <div class="col-span-2">
        <x-checkbox
            label="User Aktif"
            name="is_active"
            value="1"
            :checked="true"
        />
    </div>

    <div class="col-span-2">

        <x-input

            label="Password Baru (Opsional)"

            name="password"

            type="password"

            icon="ri-lock-password-line"

            placeholder="Kosongkan jika tidak ingin mengubah password"

        />

    </div>

    <div>
        <x-input
            label="Konfirmasi Password Baru"
            name="password_confirmation"
            type="password"
            icon="ri-lock-check-line"
            placeholder="Kosongkan jika tidak ingin diubah"
        />
    </div>

</div>

{{-- <div class="flex justify-between items-center mt-8"> --}}
<div class="mt-8 flex justify-end">    

   

    <div class="flex gap-3">
    {{-- <div class="flex justify-end gap-3 mt-8"> --}}

        <a href="{{ route('users.index') }}">

            <x-button color="gray">

                <i class="ri-close-line"></i>

                Batal

            </x-button>

        </a>

        <x-button

            color="primary"

            type="submit"

        >

            <i class="ri-save-line"></i>

            Simpan Perubahan

        </x-button>

    </div>

</div>

</form>

</x-card>

{{-- reset pwd --}}
<x-card class="mt-6">

    <div class="flex items-start gap-4">
    {{-- <div class="mt-6 flex justify-end"> --}}

        <div
            class="w-12 h-12 rounded-xl
            bg-amber-100
            flex items-center justify-center"
        >

            <i
                class="ri-lock-password-line
                text-2xl
                text-amber-600"
            ></i>

        </div>

        <div class="flex-1">

            <h3
                class="text-lg font-semibold
                text-slate-800"
            >

                Keamanan Akun

            </h3>

            <p
                class="mt-2 text-sm
                text-slate-500"
            >

                Password pengguna tidak dapat
                dilihat.

                Jika diperlukan, Admin dapat
                mereset password menjadi
                password default sistem ( 87654321 ).

            </p>

        </div>

    </div>

    {{-- <div class="mt-6"> --}}
    <div class="mt-6 flex justify-end">    
        <form

            id="formResetPassword"

            method="POST"

            action="{{ route('users.reset-password',$user) }}"

        >
        
            @csrf
        </form>

        <x-button 
            color="red"
            id="btnResetPassword"
                type="button"
        >

            <i class="ri-key-2-line"></i>

            Reset Password

        </x-button>

        

    </div>

</x-card>
{{-- reset pwd end --}}

@push('scripts')

<script>

document
.getElementById('btnResetPassword')
.addEventListener('click',function(){

    Swal.fire({

        title:'Reset Password?',

        html:`

            Password akan direset menjadi:

            <br><br>

            <b>87654321</b>

        `,

        icon:'warning',

        showCancelButton:true,

        confirmButtonText:'Ya, Reset',

        cancelButtonText:'Batal',

        confirmButtonColor:'#4F46E5',

        cancelButtonColor:'#94A3B8'

    }).then((result)=>{

        if(result.isConfirmed){

            document
            .getElementById('formResetPassword')
            .submit();

        }

    });

});

</script>

@endpush

@endsection