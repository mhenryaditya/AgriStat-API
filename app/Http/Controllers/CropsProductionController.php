<?php

namespace App\Http\Controllers;

use App\Http\Resources\CropsProductionResource;
use App\Imports\CropsImport;
use App\Models\CropsProduction;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Illuminate\Http\Request;

class CropsProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageLength = request('pageLength', 10);
        $crops = CropsProduction::paginate($pageLength);

        if ($crops->isEmpty()) {
            return response()->json([
                'message' => 'No crops productions found',
                'data' => [],
                'pagination' => [
                    'current_page' => $crops->currentPage(),
                    'last_page' => $crops->lastPage(),
                    'per_page' => $crops->perPage(),
                    'total' => $crops->total(),
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Lists of Crops Production have been retrieved successfully',
            'data' => CropsProductionResource::collection($crops),
            'pagination' => [
                'current_page' => $crops->currentPage(),
                'last_page' => $crops->lastPage(),
                'per_page' => $crops->perPage(),
                'total' => $crops->total(),
            ]
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'digits:4', 'min:2000', "max:" . (date('Y') + 1)],
            'province' => ['required', 'string'],
            'vegetable' => ['required', 'string'],
            'production' => ['required', 'regex:/^\d+(\.\d{1,2})?$/']
        ]);

        $crops = CropsProduction::create([
            'year' => $validated['year'],
            'province' => $validated['province'],
            'vegetable' => $validated['vegetable'],
            'production' => $validated['production']
        ]);

        return response()->json([
            'message' => 'Crops production data has been created successfully',
            'data' => new CropsProductionResource($crops)
        ], 201);

    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'mimes:xlsx']
        ]);

        $file = $request->file('file');
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($file->getPathname());

        $header = ['year', 'province', 'vegetable', 'production'];
        $isFirstRow = true;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $cells = $row->toArray();

                if ($isFirstRow) {
                    $header = $cells;
                    $isFirstRow = false;
                    continue;
                }

                $data = array_combine($header, $cells);

                CropsProduction::create([
                    'year' => $data['year'],
                    'province' => $data['province'],
                    'vegetable' => $data['vegetable'],
                    'production' => $data['production']
                ]);
            }
        }

        $reader->close();

        return response()->json([
            'status' => 'success',
            'message' => 'Crops production data has been imported successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $crops = CropsProduction::find($id);

        if (!$crops) {
            return response()->json([
                'message' => 'Crops production data not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Crops production data has been retrieved successfully',
            'data' => new CropsProductionResource($crops)
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    // {

    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $crops = CropsProduction::find($id);

        if (!$crops) {
            return response()->json([
                'message' => 'Crops production data not found'
            ], 404);
        }

        $validated = $request->validate([
            'year' => ['required', 'integer', 'digits:4', 'min:2000', "max:" . (date('Y') + 1)],
            'province' => ['required', 'string'],
            'vegetable' => ['required', 'string'],
            'production' => ['required', 'regex:/^\d+(\.\d{1,2})?$/']
        ]);

        $crops->update([
            'year' => $validated['year'],
            'province' => $validated['province'],
            'vegetable' => $validated['vegetable'],
            'production' => $validated['production']
        ]);

        return response()->json([
            'message' => 'Crops production data has been updated successfully',
            'data' => new CropsProductionResource($crops)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $crops = CropsProduction::find($id);

        if (!$crops) {
            return response()->json([
                'message' => 'Crops production data not found'
            ], 404);
        }

        $crops->delete();

        return response()->json([
            'message' => 'Crops production data has been deleted successfully'
        ], 200);
    }
}
