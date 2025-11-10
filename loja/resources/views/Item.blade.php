<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Novo Item | Loja API Consumidora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f7f7f7; }
    </style>
</head>
<body class="p-8">

    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-2xl">
        <h1 class="text-3xl font-extrabold text-indigo-700 mb-6 border-b pb-3">Adicionar Novo Item</h1>
        
        {{-- Formulário de Criação --}}
        <form action="{{ route('itens.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Campo Nome --}}
            <div>
                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Item</label>
                <input type="text" name="nome" id="nome" required 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                       value="{{ old('nome') }}">
            </div>

            {{-- Campo Descrição --}}
            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea name="descricao" id="descricao" rows="3" 
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('descricao') }}</textarea>
            </div>

            {{-- Preço e Medida em linha --}}
            <div class="grid grid-cols-3 gap-4">
                {{-- Preço Unitário --}}
                <div class="col-span-1">
                    <label for="preco_unitario" class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário (R$)</label>
                    <input type="number" step="0.01" min="0" name="preco_unitario" id="preco_unitario" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                           value="{{ old('preco_unitario') }}">
                </div>
                
                {{-- Tipo de Medida --}}
                <div class="col-span-1">
                    <label for="tipo_medida" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo_medida" id="tipo_medida" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="kilograma" {{ old('tipo_medida') == 'kilograma' ? 'selected' : '' }}>Quilograma (Kg)</option>
                        <option value="liquido" {{ old('tipo_medida') == 'liquido' ? 'selected' : '' }}>Líquido (L)</option>
                    </select>
                </div>

                {{-- Quantidade de Medida --}}
                <div class="col-span-1">
                    <label for="quantidade_medida" class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                    <input type="number" step="0.01" min="0" name="quantidade_medida" id="quantidade_medida" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" 
                           value="{{ old('quantidade_medida') }}">
                </div>
            </div>

            {{-- Botões de Ação --}}
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('itens.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
                    Salvar Item
                </button>
            </div>
        </form>
    </div>
</body>
</html>