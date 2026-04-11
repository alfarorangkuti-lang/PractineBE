<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

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

    public function edit(Request $request)
    {   
        try {
            $user = $request->user();
            $ids = explode(',', $request->ids);
            $data = Supplier::where('tenant_id', $user->tenant_id)->whereIn('id', $ids)->get();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => 'terjadi kesalahan : '. $e]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $request->validate([
                'names' => 'required|array',
                'names.*' => 'required|string|max:255|unique:supplier,name'
            ]);

            foreach($request->names as $name){
                $supplier = Supplier::create([
                    'tenant_id' => $user->tenant_id,
                    'name' => $name
                ]);
            }

            return response()->json('berhasil!',200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'terjadi kesalahan:'.$e->getMessage()]);
        }
        
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

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'data' => 'required|array|min:1',
            'data.*.id' => 'required|integer|exists:suppliers,id',
            'data.*.name' => 'required|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->data as $data) {

                $supplier = Supplier::where('tenant_id', $user->tenant_id)
                    ->where('id', $data['id'])
                    ->firstOrFail();

                $supplier->update([
                    'name' => $data['name']
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'berhasil update'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat update:' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
