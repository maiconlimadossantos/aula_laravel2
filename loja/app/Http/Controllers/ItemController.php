<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ItemController extends Controller
{
    /**
     * Exibe o painel (dashboard) com a lista de itens.
     * Mapeia para a rota GET /items
     */
    public function index()
    {
        // 1. Busca todos os itens do banco de dados (o mockData será substituído)
        $items = Item::all();

        // 2. Calcula os KPIs globais (opcionalmente pode ser feito no front-end)
        $totalItems = $items->count();
        $totalStock = $items->sum('quantidade_estoque');
        $totalValue = $items->sum(function($item) {
            return $item->preco * $item->quantidade_estoque;
        });

        // 3. Obtém todas as categorias únicas para o filtro
        $categorias = $items->pluck('categoria')->unique()->sort()->values();

        // 4. Retorna a view, passando os dados
        return view('items.dashboard', [
            'items' => $items,
            'kpis' => [
                'totalItems' => $totalItems,
                'totalStock' => $totalStock,
                'totalValue' => $totalValue,
            ],
            'categorias' => $categorias,
        ]);
    }

    /**
     * Armazena um novo item no banco de dados (CREATE - Adicionar Novo Item).
     * Mapeia para a rota POST /items
     */
    public function store(Request $request)
    {
        // 1. Validação dos dados
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria' => 'required|string|max:100',
            'preco' => 'required|numeric|min:0.01',
            'quantidade_estoque' => 'required|integer|min:0',
            'fornecedor' => 'required|string|max:255',
            'data_validade' => 'required|date|after_or_equal:today',
            'tipo_medida' => 'required|string|in:kilograma,liquido,unidade',
            'imagem_url' => 'nullable|url|max:2048', // URL é opcional
        ], [
            // Mensagens de erro personalizadas (exemplo)
            'data_validade.after_or_equal' => 'A data de validade deve ser hoje ou posterior.',
        ]);

        if ($validator->fails()) {
            // Se falhar, você pode redirecionar com erros ou retornar uma resposta JSON
            return back()->withErrors($validator)->withInput();
        }

        // 2. Criação do item
        $item = Item::create($request->all());

        // 3. Retorno da resposta
        // Em uma aplicação real, você pode retornar para a dashboard com uma mensagem de sucesso
        // Ou, se for uma requisição AJAX (o que parece ser o caso do seu front-end), retorne JSON
        if ($request->expectsJson()) {
             // Retorna o item criado com status 201 (Created)
            return response()->json($item, 201); 
        }

        return redirect()->route('items.index')->with('success', 'Item adicionado com sucesso!');
    }

    // Métodos show, edit, update, destroy (READ/UPDATE/DELETE) devem ser implementados para um CRUD completo.
    // O seu front-end atual usa filtros, mas não métodos de Edição/Exclusão.
    // Vou deixar o 'show' como um placeholder
    
    /**
     * Exibe um item específico (READ).
     * Mapeia para a rota GET /items/{item}
     */
    public function show(Item $item)
    {
        // Retorna uma resposta JSON para simular a obtenção de dados para edição/visualização
        return response()->json($item);
    }
    public function edit(Item $item)
    {
        // Retorna o item como JSON para preencher o formulário de edição no frontend.
        return response()->json($item);
    }

    /**
     * Atualiza o item especificado no banco de dados (UPDATE).
     * Mapeia para a rota PUT/PATCH /items/{item}
     */
    public function update(Request $request, Item $item)
    {
        // 1. Validação dos dados (similar ao 'store')
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'descricao' => 'required|string',
            'categoria' => 'required|string|max:100',
            'preco' => 'required|numeric|min:0.01',
            'quantidade_estoque' => 'required|integer|min:0',
            'fornecedor' => 'required|string|max:255',
            'data_validade' => 'required|date',
            'tipo_medida' => 'required|string|in:kilograma,liquido,unidade',
            'imagem_url' => 'nullable|url|max:2048',
        ]);

        if ($validator->fails()) {
            // Retorna erro 422 (Unprocessable Entity) se for uma requisição AJAX
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 2. Atualiza o item
        $item->update($request->all());

        // 3. Retorna o item atualizado
        return response()->json($item, 200); // Retorna 200 OK
    }

    /**
     * Remove o item especificado do banco de dados (DELETE).
     * Mapeia para a rota DELETE /items/{item}
     */
    public function destroy(Item $item)
    {
        $item->delete();

        // Retorna 204 No Content para indicar sucesso na exclusão
        return response()->json(null, 204);
    }
}
?>