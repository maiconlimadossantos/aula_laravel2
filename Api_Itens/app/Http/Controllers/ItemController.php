<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['items.index' => Item::all()], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $request->validate([
            'nome' => 'required|string',
            'descricao' => 'required|string',
            'categoria' => 'required|string',
            'imagem_url' => 'required|string',
            'preco' => 'required|string',
            'quantidade_estoque' => 'required|string',
            'fornecedor' => 'required|string',
            'data_validade' => 'required|string',
            'tipo_medida' => 'required|in:liquido,kilograma',
        ]);

        $item = Item::create($request->all());

        return response()->json(['item.create' => $item], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        try {
            return response()->json(['item.show' => $item], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Item não encontrado'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'nome' => 'sometimes|required|string',
            'descricao' => 'sometimes|required|string',
            'categoria' => 'sometimes|required|string',
            'imagem_url' => 'sometimes|required|string',
            'preco' => 'sometimes|required|string',
            'quantidade_estoque' => 'sometimes|required|string',
            'fornecedor' => 'sometimes|required|string',
            'data_validade' => 'sometimes|required|string',
            'tipo_medida' => 'sometimes|required|in:liquido,kilograma',
        ]);
        $item->update($request->all());
        return response()->json(['item.index' => $item], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $item->delete();
        return response()->json(['item removido com sucesso'], 204);
    }
}
