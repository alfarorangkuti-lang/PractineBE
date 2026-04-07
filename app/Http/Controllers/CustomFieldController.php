<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\CustomField;
use Illuminate\Http\Request;

class CustomFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $customField = CustomField::where('tenant_id', $user->tenant_id)->get();
            return response()->json([$customField]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'terjadi kesalahan: '. $e]);
        }
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
                'name' => 'required|string',
                'type' => 'string|required'
            ]);

            $customField = CustomField::create([
                'tenant_id' => $user->tenant_id,
                'name' => $request->name,
                'type' => $request->type,
            ]);
            DB::commit();

            return response()->json(['message' => 'berhasil!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'terjadi kesalahan :'.$e], 500);
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
