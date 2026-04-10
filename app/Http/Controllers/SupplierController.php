<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Supplier;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        try {
        
            $user = $request->user();
            $search = $request->search ?? '';
            $orderBy = $request->order ?? 'name';
            $sortBy = $request->by ?? 'asc';
            $supplier = Supplier::where('tenant_id', $user->tenant_id)->where('name', 'LIKE' , "%{$search}%")
            ->withCount('inventory')->orderBy($orderBy, $sortBy)->get();
            if (empty($supplier)) {
                return response()->json(['Kosong']);
            }
            return response()->json($supplier);

        } catch (\Exception $e) {
            return response()->json(['message' => 'terjadi kesalahan : '. $e]);
        }
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
