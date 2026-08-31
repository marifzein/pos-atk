<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('pic', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->latest()->paginate(10)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pic' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'catatan' => 'nullable|string',
        ]);

        Supplier::create([
            'name' => $request->name,
            'pic' => $request->pic,
            'phone' => $request->phone,
            'address' => $request->address,
            'email' => $request->email,
            'catatan' => $request->catatan,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pic' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'catatan' => 'nullable|string',
        ]);

        $supplier->update([
            'name' => $request->name,
            'pic' => $request->pic,
            'phone' => $request->phone,
            'address' => $request->address,
            'email' => $request->email,
            'catatan' => $request->catatan,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }
}