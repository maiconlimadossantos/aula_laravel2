<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuários</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc; /* slate-50 */
        }
        .list-card {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
    </style>
</head>
<body>

<div class="min-h-screen p-4 sm:p-8">
    
    <div class="w-full max-w-6xl mx-auto">
        
        <!-- Header e Filtro -->
        <header class="mb-8 pb-4 border-b border-gray-200">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-2">
                <i data-lucide="users" class="w-8 h-8 inline-block align-text-bottom mr-3 text-indigo-600"></i>
                Lista de Usuários Registrados
            </h1>
            <p class="text-gray-500">Visualização em tempo real dos perfis salvos na coleção pública.</p>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-center justify-between space-y-3 sm:space-y-0">
                <div id="authStatus" class="flex items-center space-x-2 text-sm font-medium text-gray-600">
                    Sessão ID: <span id="currentUserId" class="ml-1 font-mono text-xs">...</span>
                </div>

                <!-- Seletor de Filtro -->
                <div class="flex items-center space-x-3">
                    <label for="filterType" class="text-sm font-medium text-gray-700">Filtrar por Tipo:</label>
                    <select id="filterType" class="p-2 border border-gray-300 rounded-lg shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="todos">Todos os Tipos</option>
                        <option value="admin">Administrador</option>
                        <option value="funcionario">Funcionário</option>
                        <option value="cliente">Cliente</option>
                    </select>
                </div>
            </div>
        </header>

        <!-- Container da Lista de Usuários -->
        <div id="userListContainer" class="space-y-4">
            <!-- Usuários serão injetados aqui -->
            <div id="loadingMessage" class="text-center p-10 text-gray-500">
                <i data-lucide="loader" class="w-6 h-6 animate-spin mx-auto mb-2"></i>
                Conectando ao banco de dados e carregando perfis...
            </div>
        </div>
        
        <div id="emptyMessage" class="hidden text-center p-10 text-gray-500 border-2 border-dashed border-gray-300 rounded-xl mt-8">
            <i data-lucide="search" class="w-8 h-8 mx-auto mb-3"></i>
            <p class="font-medium">Nenhum perfil encontrado com os filtros atuais.</p>
        </div>

    </div>
</div>

<script type="module">
    // Importações do Firebase
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
    import { getFirestore, doc, onSnapshot, collection, setLogLevel, query, getDocs } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    setLogLevel('Debug');

    const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
    const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');

    // Inicialização e Referências
    let app, db, auth;
    let userId = null;
    let isAuthReady = false;
    let allUsersData = []; // Cache para todos os usuários

    // Elementos DOM
    const currentUserIdSpan = document.getElementById('currentUserId');
    const userListContainer = document.getElementById('userListContainer');
    const loadingMessage = document.getElementById('loadingMessage');
    const emptyMessage = document.getElementById('emptyMessage');
    const filterTypeSelect = document.getElementById('filterType');

    try {
        app = initializeApp(firebaseConfig);
        db = getFirestore(app);
        auth = getAuth(app);
    } catch (e) {
        console.error("Erro ao inicializar Firebase. Verifique __firebase_config.", e);
        loadingMessage.textContent = "Erro ao carregar o Firebase.";
    }

    // Função de Autenticação (padrão)
    const authenticate = async () => {
        try {
            if (typeof __initial_auth_token !== 'undefined' && __initial_auth_token) {
                await signInWithCustomToken(auth, __initial_auth_token);
            } else {
                // Necessário para permitir acesso ao Firestore
                await signInAnonymously(auth);
            }
        } catch (error) {
            console.error("Erro durante a autenticação:", error);
        }
    };

    // 1. Ouvinte de Estado de Autenticação
    onAuthStateChanged(auth, (user) => {
        if (user) {
            userId = user.uid;
            isAuthReady = true;
            currentUserIdSpan.textContent = userId;
            
            // Carrega a lista de usuários após a autenticação
            loadPublicProfiles();

        } else {
            if (!isAuthReady) {
                authenticate();
            } else {
                // Usuário deslogado
                loadingMessage.textContent = "Sessão encerrada. Por favor, recarregue.";
            }
        }
        lucide.createIcons();
    });

    // Função Auxiliar para obter ícone e cor
    const getUserTypeDetails = (type) => {
        switch (type) {
            case 'admin':
                return { icon: 'crown', color: 'text-red-500', bg: 'bg-red-100', label: 'Administrador' };
            case 'funcionario':
                return { icon: 'briefcase', color: 'text-green-500', bg: 'bg-green-100', label: 'Funcionário' };
            case 'cliente':
            default:
                return { icon: 'user-check', color: 'text-indigo-500', bg: 'bg-indigo-100', label: 'Cliente' };
        }
    };

    // 2. Função de Renderização
    const renderUserList = (users) => {
        userListContainer.innerHTML = ''; // Limpa a lista atual

        if (users.length === 0) {
            emptyMessage.classList.remove('hidden');
            return;
        }

        emptyMessage.classList.add('hidden');

        // Renderiza cada usuário
        users.forEach(user => {
            const { icon, color, bg, label } = getUserTypeDetails(user.tipo_usuario);

            const userCard = `
                <div class="flex items-center bg-white p-4 sm:p-6 rounded-xl list-card transition duration-200 hover:shadow-lg hover:border-indigo-200 border border-gray-100">
                    
                    <!-- Ícone do Tipo de Usuário -->
                    <div class="p-3 rounded-full ${bg} mr-4">
                        <i data-lucide="${icon}" class="w-6 h-6 ${color}"></i>
                    </div>

                    <!-- Detalhes -->
                    <div class="flex-grow min-w-0">
                        <p class="text-lg font-semibold text-gray-800 truncate">${user.name || 'Nome Não Fornecido'}</p>
                        <p class="text-sm text-gray-500 truncate">${user.email || 'Email Não Fornecido'}</p>
                    </div>

                    <!-- Tipo e ID -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 ml-4 flex-shrink-0">
                        <span class="px-3 py-1 text-xs font-semibold rounded-full ${bg} ${color}">${label}</span>
                        <div class="hidden sm:block text-xs text-gray-400 mt-1 sm:mt-0">
                            ID: <span class="font-mono">${user.id.substring(0, 8)}...</span>
                        </div>
                    </div>
                </div>
            `;
            userListContainer.innerHTML += userCard;
        });
        // Atualiza os ícones Lucide para os novos elementos injetados
        lucide.createIcons();
    };
    
    // Função de Filtro
    const filterAndRenderUsers = () => {
        const selectedType = filterTypeSelect.value;

        let filteredUsers = allUsersData;

        if (selectedType !== 'todos') {
            filteredUsers = allUsersData.filter(user => user.tipo_usuario === selectedType);
        }

        renderUserList(filteredUsers);
    }
    
    filterTypeSelect.addEventListener('change', filterAndRenderUsers);


    // 3. Carregar Lista de Perfis Públicos (Public Read)
    const loadPublicProfiles = () => {
        if (!db || !userId) return;

        // Path Público: /artifacts/{appId}/public/data/user_profiles
        // Nota: Cada usuário salvará seus dados usando seu UID como ID do documento
        const profileCollectionRef = collection(db, `artifacts/${appId}/public/data/user_profiles`);
        
        loadingMessage.classList.remove('hidden');

        // Ouve a coleção completa em tempo real
        onSnapshot(profileCollectionRef, (snapshot) => {
            allUsersData = [];
            snapshot.forEach(docSnap => {
                const data = docSnap.data();
                // Adiciona o ID do documento ao objeto para referência
                allUsersData.push({ id: docSnap.id, ...data }); 
            });

            // Ordena por tipo e nome antes de filtrar/renderizar
            allUsersData.sort((a, b) => {
                if (a.tipo_usuario < b.tipo_usuario) return -1;
                if (a.tipo_usuario > b.tipo_usuario) return 1;
                return (a.name || '').localeCompare(b.name || '');
            });

            filterAndRenderUsers(); // Filtra e renderiza os dados
            loadingMessage.classList.add('hidden');

        }, (error) => {
            console.error("Erro ao buscar perfis públicos:", error);
            loadingMessage.textContent = "Erro ao carregar dados. Verifique as regras de segurança do Firestore.";
        });
    };

    // Tenta autenticar na carga inicial, se necessário
    if (!auth.currentUser) {
        authenticate();
    }
    
</script>

</body>
</html>