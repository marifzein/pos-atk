<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;


class UserController extends Controller
{

    public function index()
    {
        $currentUser = Auth::user();

        $users = User::with('branch') // Load relasi branch
            // 🚀 JIKA BUKAN Owner/Admin, KUNCI HANYA BISA LIHAT CABANGNYA SENDIRI
            ->when(!Gate::allows('akses-owner-admin'), function ($query) use ($currentUser) {
                return $query->where('branch_id', $currentUser->branch_id);
            })
            // Filter proteksi role supervisor
            ->when($currentUser->role === 'Supervisor', function ($query) {
                return $query->whereNotIn('role', ['Admin', 'Owner']);
            })
            // Filter proteksi role owner
            ->when($currentUser->role === 'Owner', function ($query) {
                return $query->where('role', '!=', 'Admin');
            })
            ->latest() 
            ->paginate(10); 

        return view('users.index', compact('users'));

    }

    public function create()
    {

        $branches = Branch::where('is_active', true)->get();
        return view('users.create', compact('branches'));

    }

    public function store(Request $request)
    {

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'=>'required',
            'email'    => 'nullable|email|unique:users',
            'role'=>'required',
            // Tambahkan 'confirmed' untuk mencocokkan dengan password_confirmation
            'password' => 'required|min:6|confirmed'

        ]);

        User::create([
            'branch_id' => $request->branch_id,
            'name'=>$request->name,
            'email'=>$request->email,
            'role'=>$request->role,
            'password'=>Hash::make(
                $request->password
            ),
            // Secara default user baru statusnya Aktif
            'is_active' => true

        ]);

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'User berhasil ditambahkan.'
            );

    }

    public function edit(User $user)
    {
        // tambahan kalo spv tdk bisa edit admin dan owner
        // Cek jika yang login adalah Supervisor, dan yang mau diedit adalah Admin/Owner
        if (Auth::user()->role === 'Supervisor' && in_array($user->role, ['Admin', 'Owner'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data akun ini.');
        }

        // 🚀 TAMBAHAN: Proteksi jika Owner mencoba mengedit Admin via URL langsung
        if (Auth::user()->role === 'Owner' && $user->role === 'Admin') {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data akun ini.');
        }

        $branches = Branch::where('is_active', true)->get();

        return view(
            'users.edit',
            compact('user', 'branches')
        );

    }

    public function update(
        Request $request,
        User $user
    )
    {

        // Cek proteksi yang sama sebelum data sempat disimpan
        if (Auth::user()->role === 'Supervisor' && in_array($user->role, ['Admin', 'Owner'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data akun ini.');
        }

        // 🚀 TAMBAHAN: Proteksi backend untuk Owner sebelum data tersimpan
        if (Auth::user()->role === 'Owner' && $user->role === 'Admin') {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah data akun ini.');
        }
        
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required',
            'email'     => 'nullable|email|unique:users,email,' . $user->id,
            'role'      => 'required',
            'is_active' => 'nullable', // BISA BOLEH KOSONG KARENA PAKAI CHECKBOX
            'password'  => 'nullable|min:6|confirmed',
        ]);

        $data = [
            'branch_id' => $request->branch_id,
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            // Jika checkbox dicentang, nilainya "1" (true). Jika tidak dikirim, berarti false.
            'is_active' => $request->has('is_active') ? true : false,

        ];

        // Jika password diisi, update password
        if ($request->filled('password')) {

            $data['password'] = Hash::make($request->password);

        }

        $user->update($data);

        return redirect()
        ->route('users.index')
        ->with(
            'success',
            'User berhasil diperbarui.'
        );

    }

    // reset pwd----------------------------------
    public function resetPassword(User $user)
    {
        $user->update([
            'password'=>Hash::make('87654321')
        ]);

        return back()
            ->with(
                'success',
                'Password berhasil direset menjadi 87654321.'
            );
    }

}