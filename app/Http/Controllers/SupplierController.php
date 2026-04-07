<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $supplier = Supplier::where('tenant_id', $user->tenant_id)->get();
        return response()->json($supplier);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string'
        ]);

        $supplier = Supplier::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name
        ]);

        return response()->json($supplier);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
        if ($supplier) {
            $supplier->name = $request->name;
            $supplier->save();
        }
        return response()->json(['message' => 'berhasil update']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
