<?php

namespace App\Http\Controllers;

use App\Models\Contenu;
use App\Models\Langue;
use App\Models\Region;
use App\Models\TypeContenu;
use Illuminate\Http\Request;

class ContenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $contenus = Contenu::all();
        return view('contenus.index', compact('contenus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $regions = Region::all();
        $langues = Langue::all();
        $typeContentus = TypeContenu::all();
        return view('contenus.create', compact('regions', 'langues', 'typeContentus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Contenu::create($request->all());
        return redirect()->route('contenus.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Contenu $contenu)
    {
        return view('contenus.show', compact('contenu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Contenu $contenu)
    {
        $regions = Region::all();
        $langues = Langue::all();
        $typeContentus = TypeContenu::all();
        return view('contenus.edit', compact('contenu', 'regions', 'langues', 'typeContentus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contenu $contenu)
    {
        $contenu->update($request->all());
        return redirect()->route('contenus.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contenu $contenu)
    {
        $contenu->delete();
        return redirect()->route('contenus.index');
    }
}
