<?php

namespace App\Http\Controllers;

use App\Http\Resources\TerritorioCollection;
use App\Http\Resources\TerritorioResumenResource;
use App\Http\Resources\TerritorioResource;
use App\Models\Municipio;
use App\Models\Territorio;
use App\Services\AfectacionesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TerritorioController extends Controller
{
    public function __construct(private AfectacionesService $afectacionesService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $territorios = Territorio::where('id', '!=', 0)->get();
        return new TerritorioCollection($territorios);
    }

    /**
     * Store a newly created resource in storage.
     
    public function store(Request $request)
    {
        //
    }
    */

    /**
     * Display the specified resource.
     */
    public function show(Territorio $territorio)
    {
        //
        return new TerritorioResource($territorio);
    }

    /**
     * Update the specified resource in storage.
     
    public function update(Request $request, string $id)
    {
        //
    }
    **/

    /**
     * Devuelve resumen de toda la informacion de un territorio
     */

    public function resumen(Territorio $territorio)
    {
        $data = Cache::remember("territorio.{$territorio->id}.resumen", now()->addHours(24), function () use ($territorio) {
            $territorio->load('poblacion', 'comunidades', 'conflictos');

            return [
                'territorio'   => $territorio,
                'afectaciones' => $this->afectacionesService->getAfectaciones('territorio_id', $territorio->id),
            ];
        });

        return new TerritorioResumenResource($data);
    }

    /**
     * Devuelve todos los casos relacionados con un territorio específico.
     */
    public function casos($territorio_id)
    {
        $territorio = Territorio::with('casos')->findOrFail($territorio_id);
        return response()->json($territorio->casos);
    }


    /**
     * Remove the specified resource from storage.
     */
    /*public function destroy(string $id)
    {
        //
    }*/
}
