<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Machine;

class MachineController extends Controller
{
    public function index()
    {
        $machines = Machine::all();
        return view('admin.index', compact('machines'));
    }

    public function create()
    {
        return view('admin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required|in:available,booked,maintenance'
        ]);

        Machine::create($request->only(['name', 'status']));

        return redirect()->route('machines.index')->with('success', 'Machine berhasil ditambahkan');
    }

    public function edit(Machine $machine)
    {
        return view('admin.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required|in:available,booked,maintenance'
        ]);

        $machine->update($request->all());

        return redirect()->route('machines.index')->with('success', 'Machine berhasil diperbarui');
    }

    public function destroy(Machine $machine)
    {
        $machine->delete();
        return redirect()->route('machines.index')->with('success', 'Machine berhasil dihapus');
    }
}

