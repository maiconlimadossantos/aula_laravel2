<div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
    <h2 class="text-2xl font-semibold mb-4">Lista de Itens</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagem</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Validade</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade em Estoque</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Medida</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th> 
                    <
                </tr>
            </thead>
            <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                <!-- O conteúdo é inserido aqui pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
    // Assegura que o script seja executado após o carregamento da DOM.
    document.addEventListener('DOMContentLoaded', () => {
        // Assume-se que 'mockData' e 'handleFilters' estão definidos no arquivo principal.
        // A tabela é renderizada inicialmente aqui:
        // renderAll(mockData); 
    });

    // Função JavaScript que gera o conteúdo da tabela
    function renderTable(data) {
        const tableBody = document.getElementById('itemsTableBody');
        tableBody.innerHTML = '';

        if (data.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum item encontrado com os filtros aplicados.</td></tr>`;
            return;
        }

        data.forEach(item => {
            const row = `
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.nome}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${item.categoria}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">R$ ${item.preco.toFixed(2).replace('.', ',')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${item.quantidade_estoque} (${item.tipo_medida})</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${item.descricao}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><img src="${item.imagem_url}" alt="${item.nome}" class="h-16 w-16 object-cover rounded"></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${new Date(item.data_validade).toLocaleDateString('pt-BR')}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${item.fornecedor}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                        <button onclick="editItem(${item.id})" class="text-indigo-600 hover:text-indigo-900 mr-4">Editar</button>
                        <button onclick="deleteItem(${item.id})" class="text-red-600 hover:text-red-900">Excluir</button>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    }

    // Nota: Esta função faz parte do script maior do arquivo 'interactive_item_dashboard_with_crud.html'.
</script>