<?php
// asdas
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\StockParent;
use App\Models\CustomField;
use App\Models\CustomFieldStockParent;
use App\Http\Resources\StockParentResource;

class StockParentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {   
        $user = $request->user();
        $stock = StockParent::where('tenant_id', $user->tenant_id)->with('customFieldValues.field')->get();
        return StockParentResource::collection($stock);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            $request->validate([
                'name' => 'string|required',
                'type' => 'required|string',
                'custom_field' => 'nullable|array'
            ]);

            $stockParent = StockParent::create([
                'tenant_id' => $user->tenant_id,
                'name' => $request->name,
                'type' => $request->type,
            ]);
            $customField = CustomField::where('tenant_id', $user->tenant_id);
            $customFieldCount = $customField->count();
            $customField= $customField->get();
            $inputFields = collect($request->custom_field)->keyBy('custom_field_id');

            if ($customFieldCount > 0) {
                foreach($customField as $cf){
                    $inputValue = $inputFields[$cf->id]['value'] ?? null;
                    CustomFieldStockParent::create([
                        'tenant_id' => $user->tenant_id,
                        'custom_field_id' => $cf->id,
                        'stock_parent_id' => $stockParent->id,
                        'value' => !empty($inputValue) ? $inputValue : '-'
                    ]);
                }
            }
            DB::commit();
            return response()->json(['message' => 'berhasil']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
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
        try {
            DB::beginTransaction();
            $user = $request->user();
            $stockParent = StockParent::findOrFail($id);
            $request->validate([
                'name' => 'required|string',
                'type' => 'required|string',
                'custom_field' => 'nullable|array'
            ]);

            $customFields = CustomField::where('tenant_id', $user->tenant_id)->get();
            $inputFields = collect($request->custom_field ?? [])->keyBy('custom_field_id');

            $stockParent->name = $request->name;
            $stockParent->type = $request->type;
            $stockParent->save();
            
            foreach($customFields as $cf){
                
                $customFieldStock = CustomFieldStockParent::where('custom_field_id',$cf->id)
                                                            ->where('stock_parent_id', $id)->first();
                $inputValue = $inputFields[$cf->id]['value'] ?? $customFieldStock->value;
                $customFieldStock->value = $inputValue;
                $customFieldStock->save();
            }
            DB::commit();
            return response()->json(['message' => 'berhasil']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'terjadi kesalahan: '. $e]);
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
