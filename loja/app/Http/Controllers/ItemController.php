<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $renpose =Http::get(this->api_url . '/items');
        if($renpose->successful()){
            $itens=$renpose->json()['data'];
            return view('items.index',compact('itens'));
        }
        return view('items.index')->with('error','Não foi possível carregar os itens.');
    }
    public function create()
    {
        return view('items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $renpose =Http::post(this->api_url . '/items', $request->all());
        if($renpose->successful()){
            return redirect()->route('items.index')->with('success','Item criado com sucesso.');
        }
        return redirect()->route('items.index')->with('error','Não foi possível criar o item.')->withInput();
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $renpose=Http::get(this->api_url . '/items/' . $item->id);
        if($renpose->successful()){
            $item=$renpose->json()['data']??null;
        if($item){
            return view('items.show',compact('item'));
        }
        }
        return redirect()->route('items.index')->with('error','Item não encontrado.');
    }
    public function edit(Item $item)
    {
        $renpose=Http::get(this->api_url . '/items/' . $item->id);
        $item=$renpose->json()['data']??null;
        if($item){
            return view('items.edit',compact('item'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $renpose=Http::put(this->api_url . '/items/' . $item->id, $request->all());
        if($renpose->successful()){
            return redirect()->route('items.index')->with('success','Item atualizado com sucesso.');
        }
        return redirect()->route('items.index')->with('error','Não foi possível atualizar o item.')->withInput();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $renpose=Http::delete(this->api_url . '/items/' . $item->id);
        if($renpose->successful()){
            return redirect()->route('items.index')->with('success','Item deletado com sucesso.');
        }
        return redirect()->route('items.index')->with('error','Não foi possível deletar o item.');
    }
}
