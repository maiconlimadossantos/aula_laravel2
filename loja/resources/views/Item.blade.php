<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <title>Painel de Itens com CRUD</title>
    <!-- Chosen Palette: Calm Harmony with Indigo Accent (Bege, Cinza, Verde, Indigo) -->
    <!-- Application Structure Plan: Um painel de controle (dashboard) de aba única com um modal para entrada de dados. A estrutura é: 1) Cabeçalho com botão "Adicionar Item", 2) Filtros (Categoria, Busca), 3) KPIs (cards), 4) Conteúdo Principal (Tabela de Itens e Gráfico de Estoque). 5) Um modal (inicialmente oculto) para "Adicionar Novo Item", baseado no formulário 'create.blade.php'. O fluxo do usuário é: visualizar/filtrar dados (padrão) ou clicar em "Adicionar", preencher o modal, e submeter. A submissão atualiza o array de dados do lado do cliente e re-renderiza a UI (KPIs, tabela, gráfico). Esta estrutura foi escolhida para integrar a visualização de dados e a entrada de dados em uma única SPA coesa, mantendo a tela principal limpa e focada na exploração. -->
    <!-- Visualization & Content Choices: 
        - Relatório Fonte: Dados da tabela 'items' (nome, preço, categoria, estoque).
        - Objetivo: Informar, Comparar, Organizar.
        - KPIs (Total de Itens, Valor Total em Estoque, Itens por Categoria): Informar (HTML/JS) -> Apresenta estatísticas chave de forma imediata.
        - Tabela de Itens: Organizar/Informar (HTML/JS) -> Permite consulta detalhada. Interação: Filtro por Categoria/Busca. Justificativa: Essencial para ver os dados brutos.
        - Gráfico de Rosca (Estoque por Categoria): Comparar (Chart.js/Canvas) -> Mostra a proporção do estoque entre categorias. Interação: Atualizado por filtros e adição de dados. Justificativa: Visualização ideal para proporções.
        - Relatório Fonte: Formulário 'create.blade.php'. Objetivo: Organizar/Coletar. Apresentação: Modal HTML/Tailwind. Interação: Submissão de formulário. Justificação: Permite a adição interativa de dados ao conjunto de dados do lado do cliente, simulando a funcionalidade completa do 'Source Report' (a aplicação CRUD) em um único arquivo. -> Método: HTML/JS.
    -->
    <!-- CONFIRMATION: NO SVG graphics used. NO Mermaid JS used. -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        .chart-container {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            height: 300px;
            max-height: 400px;
        }
        @media (min-width: 768px) {
            .chart-container {
                height: 350px;
            }
        }

        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="bg-beige-50 text-gray-800 p-4 sm:p-8">

    <div class="max-w-7xl mx-auto">
        
        <header class="mb-6 pb-4 border-b border-gray-300 flex justify-between items-center">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Painel Interativo de Itens</h1>
                <p class="text-lg text-gray-600 mt-1">Explore, filtre e adicione itens ao catálogo.</p>
            </div>
            <title>Login</title>
                <form action='route{{"usuario.index"}}' method="post"   >

                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" id="nome" placeholder="Digite o nome do usuario" >
                    <br>
                    <label for="email">Email:</label>
                    <input type="text" value="email" id="email" placeholder="Digite o email do usuario ">
                    <br>
                    <label for="senha">Senha</label>
                    <input type="password" value="senha" id="senha" placeholder="Digite a senha do usuario">
                <button type="submit">Cofirmar</button>
                </form>
            <button id="openModalBtn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
                Adicionar Novo Item
            </button>
        </header>

        <section id="filters" class="mb-6 p-4 bg-white rounded-lg shadow-sm flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="categoryFilter" class="block text-sm font-medium text-gray-700 mb-1">Filtrar por Categoria:</label>
                <select id="categoryFilter" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">Todas as Categorias</option>
                </select>
            </div>
            <div class="flex-1">
                <label for="searchBox" class="block text-sm font-medium text-gray-700 mb-1">Buscar por Nome:</label>
                <input type="text" id="searchBox" placeholder="Digite o nome do item..." class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </section>

        <section id="kpi-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-5 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 uppercase">Itens Únicos</h3>
                <p id="kpi-total-items" class="text-3xl font-semibold text-gray-900 mt-1">0</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 uppercase">Valor Total em Estoque</h3>
                <p id="kpi-total-value" class="text-3xl font-semibold text-gray-900 mt-1">R$ 0,00</p>
            </div>
            <div class="bg-white p-5 rounded-lg shadow">
                <h3 class="text-sm font-medium text-gray-500 uppercase">Estoque Total (Unidades)</h3>
                <p id="kpi-total-stock" class="text-3xl font-semibold text-gray-900 mt-1">0</p>
            </div>
        </section>

        <main class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-semibold mb-4">Lista de Itens</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">Carregando dados...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-2xl font-semibold mb-4">Estoque por Categoria</h2>
                <p class="text-sm text-gray-600 mb-4">Esta visualização mostra a distribuição percentual do número total de unidades em estoque por categoria.</p>
                <div class="chart-container">
                    <canvas id="categoryStockChart"></canvas>
                </div>
            </div>

        </main>
    </div>

    <!-- Modal de Adicionar Item -->
    <div id="addItemModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 modal-backdrop" aria-hidden="true"></div>
            
            <div class="relative max-w-xl w-full mx-auto bg-white p-6 sm:p-8 rounded-xl shadow-2xl z-10">
                <h1 class="text-3xl font-extrabold text-indigo-700 mb-6 border-b pb-3">Adicionar Novo Item</h1>
                
                <form id="addItemForm" class="space-y-6 max-h-[70vh] overflow-y-auto pr-2">
                    
                    <div>
                        <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome do Item</label>
                        <input type="text" name="nome" id="nome" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="preco" class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário (R$)</label>
                            <input type="number" step="0.01" min="0" name="preco" id="preco" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label for="tipo_medida" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                            <select name="tipo_medida" id="tipo_medida" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="kilograma">Quilograma (Kg)</option>
                                <option value="liquido">Líquido (L)</option>
                            </select>
                        </div>

                        <div>
                            <label for="quantidade_estoque" class="block text-sm font-medium text-gray-700 mb-1">Qtd. em Estoque</label>
                            <input type="number" min="0" name="quantidade_estoque" id="quantidade_estoque" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                            <input type="text" name="categoria" id="categoria" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div>
                            <label for="imagem_url" class="block text-sm font-medium text-gray-700 mb-1">URL da Imagem</label>
                            <input type="url" name="imagem_url" id="imagem_url" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="https://...">
                        </div>
                        
                        <div class="sm:col-span-2">
                            <label for="data_validade" class="block text-sm font-medium text-gray-700 mb-1">Data de Validade</label>
                            <input type="date" name="data_validade" id="data_validade" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        
                        <div class="sm:col-span-3">
                            <label for="fornecedor" class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                            <input type="text" name="fornecedor" id="fornecedor" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" id="closeModalBtn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
                            Salvar Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script>
        let mockData = [
            { id: 1, nome: "Maçã Fuji", descricao: "Maçã fresca Fuji", categoria: "Frutas", imagem_url: "https://.../maca.jpg", preco: 5.50, quantidade_estoque: 150, fornecedor: "Fornecedor A", data_validade: "2025-12-01", tipo_medida: "kilograma" },
            { id: 2, nome: "Leite Integral", descricao: "Leite UHT Integral 1L", categoria: "Laticínios", imagem_url: "https://.../leite.jpg", preco: 4.20, quantidade_estoque: 200, fornecedor: "Fornecedor B", data_validade: "2025-11-20", tipo_medida: "liquido" },
            { id: 3, nome: "Pão de Forma", descricao: "Pão de forma tradicional", categoria: "Padaria", imagem_url: "https://.../pao.jpg", preco: 7.00, quantidade_estoque: 80, fornecedor: "Fornecedor C", data_validade: "2025-10-30", tipo_medida: "kilograma" },
            { id: 4, nome: "Suco de Laranja", descricao: "Suco de laranja 1L", categoria: "Bebidas", imagem_url: "https://.../suco.jpg", preco: 9.80, quantidade_estoque: 120, fornecedor: "Fornecedor A", data_validade: "2026-01-10", tipo_medida: "liquido" },
            { id: 5, nome: "Queijo Minas", descricao: "Queijo minas frescal 500g", categoria: "Laticínios", imagem_url: "https://.../queijo.jpg", preco: 15.00, quantidade_estoque: 60, fornecedor: "Fornecedor B", data_validade: "2025-11-15", tipo_medida: "kilograma" },
            { id: 6, nome: "Banana Prata", descricao: "Banana prata de alta qualidade", categoria: "Frutas", imagem_url: "https://.../banana.jpg", preco: 3.90, quantidade_estoque: 300, fornecedor: "Fornecedor A", data_validade: "2025-10-28", tipo_medida: "kilograma" },
            { id: 7, nome: "Refrigerante Cola", descricao: "Refrigerante Cola 2L", categoria: "Bebidas", imagem_url: "https://.../refri.jpg", preco: 8.50, quantidade_estoque: 180, fornecedor: "Fornecedor D", data_validade: "2026-05-01", tipo_medida: "liquido" },
            { id: 8, nome: "Iogurte Natural", descricao: "Iogurte natural 200g", categoria: "Laticínios", imagem_url: "https://.../iogurte.jpg", preco: 3.00, quantidade_estoque: 250, fornecedor: "Fornecedor B", data_validade: "2025-11-10", tipo_medida: "kilograma" }
        ];

        let categoryChart = null;
        const modal = document.getElementById('addItemModal');
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const addItemForm = document.getElementById('addItemForm');

        document.addEventListener('DOMContentLoaded', () => {
            populateCategoryFilter(mockData);
            renderAll(mockData);

            document.getElementById('categoryFilter').addEventListener('change', handleFilters);
            document.getElementById('searchBox').addEventListener('input', handleFilters);
            
            openModalBtn.addEventListener('click', () => modal.classList.remove('hidden'));
            closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));
            addItemForm.addEventListener('submit', handleFormSubmit);
        });

        function handleFilters() {
            const category = document.getElementById('categoryFilter').value;
            const search = document.getElementById('searchBox').value.toLowerCase();

            let filteredData = mockData;

            if (category !== 'all') {
                filteredData = filteredData.filter(item => item.categoria === category);
            }

            if (search) {
                filteredData = filteredData.filter(item => item.nome.toLowerCase().includes(search));
            }

            renderAllUI(filteredData);
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const newItem = {
                id: mockData.length + 1,
                nome: formData.get('nome'),
                descricao: formData.get('descricao'),
                categoria: formData.get('categoria'),
                imagem_url: formData.get('imagem_url'),
                preco: parseFloat(formData.get('preco')),
                quantidade_estoque: parseInt(formData.get('quantidade_estoque'), 10),
                fornecedor: formData.get('fornecedor'),
                data_validade: formData.get('data_validade'),
                tipo_medida: formData.get('tipo_medida')
            };

            mockData.push(newItem);
            
            event.target.reset();
            modal.classList.add('hidden');
            
            populateCategoryFilter(mockData);
            handleFilters();
        }

        function populateCategoryFilter(data) {
            const categories = [...new Set(data.map(item => item.categoria))].sort();
            const filterSelect = document.getElementById('categoryFilter');
            
            const currentVal = filterSelect.value;
            
            while (filterSelect.options.length > 1) {
                filterSelect.remove(1);
            }

            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                filterSelect.appendChild(option);
            });
            
            filterSelect.value = currentVal;
        }

        function renderAllUI(data) {
            renderTable(data);
            renderKPIs(data);
            renderCategoryChart(data);
        }

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
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
        }
        
        function renderKPIs(data) {
            const totalItems = data.length;
            const totalValue = data.reduce((sum, item) => sum + (item.preco * item.quantidade_estoque), 0);
            const totalStock = data.reduce((sum, item) => sum + item.quantidade_estoque, 0);

            document.getElementById('kpi-total-items').textContent = totalItems;
            document.getElementById('kpi-total-value').textContent = `R$ ${totalValue.toFixed(2).replace('.', ',')}`;
            document.getElementById('kpi-total-stock').textContent = totalStock;
        }

        function renderCategoryChart(data) {
            const ctx = document.getElementById('categoryStockChart').getContext('2d');
            
            const stockByCategory = data.reduce((acc, item) => {
                acc[item.categoria] = (acc[item.categoria] || 0) + item.quantidade_estoque;
                return acc;
            }, {});

            const labels = Object.keys(stockByCategory);
            const chartData = Object.values(stockByCategory);

            const chartColors = [
                '#34A853',
                '#F9AB00',
                '#4285F4',
                '#EA4335',
                '#9333ea',
                '#f472b6',
                '#06b6d4',
                '#f97316'
            ];

            if (categoryChart) {
                categoryChart.destroy();
            }

            categoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Estoque por Categoria',
                        data: chartData,
                        backgroundColor: chartColors.slice(0, labels.length),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                boxWidth: 12,
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed + ' unidades';
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'beige-50': '#FDFBF5',
                        'indigo-600': '#4f46e5',
                        'indigo-700': '#4338ca',
                    }
                }
            }
        }
    </script>
</body>
</html>