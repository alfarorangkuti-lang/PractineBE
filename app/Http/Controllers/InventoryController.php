<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\Supplier;
use App\Models\StockParent;
use App\Models\CustomField;
use App\Models\CustomFieldStockParent;
use App\Http\Resources\InventoryResource;
use Illuminate\Support\Facades\DB;


class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexSerial(Request $request)
    {
        $user = $request->user();
        $data = Inventory::where('tenant_id', $user->tenant_id)->whereHas('stockParent', function ($q) {$q->where('type', 'serial');} )->with(['stockParent.customFieldValues.field', 'supplier'])->get();
        return InventoryResource::collection($data);
        // return response()->json(data:$data);
    }

    public function indexMass(Request $request)
    {
        $user = $request->user();
        $data = Inventory::where('tenant_id', $user->tenant_id)->whereHas('stockParent', function ($q) {$q->where('type', 'mass');} )->with('stockParent')->get();
        return response()->json(data:$data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeSerial(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            $request->validate([
                'supplier_id' => 'nullable|exists:supplier,id',
                'stock_parent_id' => 'nullable|exists:stock_parent,id',
                'serial_number' => 'required|string|unique:inventory,serial_number',
                'price' => 'numeric|required',

                'supplier' => 'string',
                'stock_parent_name' => 'string',
                'custom_field' => 'array|nullable',
            ]);
            
            $supplierId = $request->supplier_id;              
            $stockParentId = $request->stock_parent_id;

            if ($supplierId == null) {
                $supplier = Supplier::firstOrCreate([
                    'tenant_id' => $user->tenant_id,
                    'name' => $request->supplier
                ]);
                $supplierId = $supplier->id;
            }

            if($stockParentId == null){
                $stockParent = StockParent::firstOrCreate([
                    'tenant_id' => $user->tenant_id,
                    'name' => $request->stock_parent_name,
                    'type' => 'serial',
                ]);

                $customField = CustomField::where('tenant_id', $user->tenant_id);
                $customField= $customField->get();
                $inputFields = collect($request->custom_field ?? [])->keyBy('custom_field_id');

                
                foreach($customField as $cf){
                    $inputValue = $inputFields[$cf->id]['value'] ?? null;
                    CustomFieldStockParent::updateOrCreate(
                        [
                            'stock_parent_id' => $stockParent->id,
                            'custom_field_id' => $cf->id,
                        ],
                        [
                            'tenant_id' => $user->tenant_id,
                            'value' => !empty($inputValue) ? $inputValue : '-'
                        ]
                    );
                }

                $stockParentId = $stockParent->id;
            }
            
            $inventory = Inventory::create([
                'tenant_id' => $user->tenant_id,
                'supplier_id' => $supplierId,
                'stock_parent_id' => $stockParentId,
                'serial_number' => $request->serial_number,
                'price' => $request->price,
                'status' => 'available'
            ]);
            
            DB::commit();
            return response()->json(['message' => 'berhasil']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'terjadi kesalahan :'. $e]);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
