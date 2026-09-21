<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $branches = $query->latest()->paginate(10)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:branches,code',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        Branch::create([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'code' => 'required|string|max:20|unique:branches,code,' . $branch->id,
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        $branch->update([
            'code' => strtoupper($request->code),
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }
}