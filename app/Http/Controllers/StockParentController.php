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
        try {

            $search = $request->search ?? '';
            $orderBy = $request->order ?? 'name';
            $sortBy = $request->by ?? 'asc';
            $user = $request->user();
            $stock = StockParent::where('tenant_id', $user->tenant_id)->where('type','serial')->where('name', 'LIKE', "%{$search}%")->withCount('inventory')->with('customFieldValues.field')->orderBy($orderBy, $sortBy)->get();
            return StockParentResource::collection($stock);
            // return response()->json($stock);

        } catch (\Exception $e) {
            return response()->json(['terjadi kesalahan']);
        }
    }

    public function indexMass(Request $request)
    {   
        $user = $request->user();
        $stock = StockParent::where('tenant_id', $user->tenant_id)->where('type','mass')->with('customFieldValues.field')->get();
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
                'custom_field' => 'nullable|array'
            ]);

            $stockParent = StockParent::create([
                'tenant_id' => $user->tenant_id,
                'name' => $request->name,
                'type' => 'serial',
            ]);

            $inputFields = collect($request->custom_field  ?? [])->keyBy('custom_field_id');
            
            foreach ($inputFields as $inputField) {
                CustomFieldStockParent::create([
                    'tenant_id' => $user->tenant_id,
                    'custom_field_id' => $inputField['custom_field_id'],
                    'stock_parent_id' => $stockParent->id,
                    'value' => !empty($inputField['value']) ? $inputField['value'] : null
                ]);
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


    public function storeMass(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = $request->user();
            $request->validate([
                'name' => 'string|required',
            ]);

            $stockParent = StockParent::create([
                'tenant_id' => $user->tenant_id,
                'name' => $request->name,
                'type' => 'mass',
            ]);
            
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
            $stockParent = StockParent::where('tenant_id', $user->tenant_id)->findOrFail($id);
            $request->validate([
                'name' => 'required|string',
                'custom_field' => 'nullable|array'
            ]);
            
            $stockParent->name = $request->name;
            $stockParent->save();


            $inputFields = collect($request->custom_field  ?? [])->keyBy('custom_field_id');
            
            foreach ($inputFields as $item) {
                CustomFieldStockParent::updateOrCreate(
                    [
                        'stock_parent_id' => $id,
                        'custom_field_id' => $item['custom_field_id'],
                    ],
                    [
                        'tenant_id' => $user->tenant_id,
                        'value' => $item['value']
                    ]
                );
            }
            
            $inputIds = $inputFields->pluck('custom_field_id');
            CustomFieldStockParent::where('stock_parent_id', $id)
            ->whereNotIn('custom_field_id', $inputIds)
            ->delete();
            
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
