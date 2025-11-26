<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Perfil de Usuário</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #eef2ff; /* indigo-50 */
        }
        .form-card {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>

<div class="min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-lg bg-white p-8 rounded-xl form-card">
        
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800">
                <i data-lucide="user-cog" class="w-7 h-7 inline-block align-text-bottom mr-2 text-indigo-600"></i>
                Configurações de Usuário
            </h1>
            <div id="authStatus" class="flex items-center space-x-2 p-2 rounded-lg text-xs font-medium bg-gray-100 text-gray-600">
                ID: <span id="currentUserId">...</span>
            </div>
        </div>

        <p class="text-sm text-gray-600 mb-6">
            Preencha seus detalhes. O campo "Tipo de Usuário" reflete o seu nível de acesso no sistema, conforme definido no banco de dados.
        </p>

        <form id="userForm" class="space-y-6">

            <!-- Campo Nome -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                <input type="text" id="name" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border" placeholder="Nome (Schema: string('name'))">
            </div>

            <!-- Campo Email (Unique) -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border" placeholder="Email (Schema: string('email')->unique())">
            </div>

            <!-- Campo Tipo de Usuário (Novo Campo do Schema) -->
            <div>
                <label for="tipo_usuario" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Usuário</label>
                <select id="tipo_usuario" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3 border bg-white">
                    <option value="cliente">Cliente (Padrão)</option>
                    <option value="funcionario">Funcionário</option>
                    <option value="admin">Administrador</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Define o nível de permissão (Schema: string('tipo_usuario')->default('cliente'))
                </p>
            </div>
            
            <!-- Botão de Salvar -->
            <button type="submit" id="saveButton" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 disabled:opacity-50" disabled>
                <span id="buttonText">Aguardando Conexão...</span>
            </button>
        </form>
        
        <div id="saveMessage" class="mt-4 text-center text-sm text-green-600 font-medium hidden p-3 bg-green-50 rounded-lg"></div>

    </div>
</div>

<script type="module">
    // Importações do Firebase
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
    import { getFirestore, doc, setDoc, onSnapshot, serverTimestamp, setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    setLogLevel('Debug');

    const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
    const firebaseConfig = JSON.parse(typeof __firebase_config !== 'undefined' ? __firebase_config : '{}');

    // Inicialização e Referências
    let app, db, auth;
    let userId = null;
    let isAuthReady = false;

    // Elementos DOM
    const currentUserIdSpan = document.getElementById('currentUserId');
    const userForm = document.getElementById('userForm');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const tipoUsuarioInput = document.getElementById('tipo_usuario');
    const saveButton = document.getElementById('saveButton');
    const buttonText = document.getElementById('buttonText');
    const saveMessage = document.getElementById('saveMessage');

    try {
        app = initializeApp(firebaseConfig);
        db = getFirestore(app);
        auth = getAuth(app);
    } catch (e) {
        console.error("Erro ao inicializar Firebase. Verifique __firebase_config.", e);
    }

    // Função de Autenticação (padrão)
    const authenticate = async () => {
        try {
            if (typeof __initial_auth_token !== 'undefined' && __initial_auth_token) {
                await signInWithCustomToken(auth, __initial_auth_token);
            } else {
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
            buttonText.textContent = 'Salvar Perfil';
            saveButton.disabled = false;
            
            // Carrega os dados do perfil após a autenticação
            loadUserProfile(userId);

        } else {
            if (!isAuthReady) {
                authenticate();
            } else {
                buttonText.textContent = 'Desconectado';
                saveButton.disabled = true;
            }
        }
        lucide.createIcons();
    });

    // 2. Carregar Dados do Perfil (Read)
    const loadUserProfile = (uid) => {
        if (!db || !uid) return;

        // Path privado (onde os dados do usuário atual são armazenados)
        const profileDocRef = doc(db, `artifacts/${appId}/users/${uid}/user_profile`, 'details');

        // Listener em tempo real (onSnapshot)
        onSnapshot(profileDocRef, (docSnap) => {
            if (docSnap.exists()) {
                const profileData = docSnap.data();
                nameInput.value = profileData.name || '';
                emailInput.value = profileData.email || '';
                tipoUsuarioInput.value = profileData.tipo_usuario || 'cliente';
                console.log("Dados do perfil carregados:", profileData);
            } else {
                console.log("Nenhum perfil encontrado. Usando valores padrão.");
                emailInput.value = auth.currentUser.email || ''; // Tenta preencher o email se disponível
            }
        }, (error) => {
            console.error("Erro ao buscar dados do perfil:", error);
        });
    };

    // 3. Salvar Dados do Perfil (Update/Set)
    userForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!isAuthReady || !userId || !db) return;

        buttonText.textContent = 'Salvando...';
        saveButton.disabled = true;
        saveMessage.classList.add('hidden');

        try {
            // Path privado: /artifacts/{appId}/users/{userId}/user_profile/details
            const profileDocRef = doc(db, `artifacts/${appId}/users/${userId}/user_profile`, 'details');
            
            const dataToSave = {
                name: nameInput.value,
                email: emailInput.value,
                tipo_usuario: tipoUsuarioInput.value,
                // Campos do schema que seriam preenchidos automaticamente:
                // email_verified_at: null,
                // password: '******', 
                created_at: serverTimestamp(),
                updated_at: serverTimestamp()
            };

            // setDoc com merge para criar ou atualizar o documento
            await setDoc(profileDocRef, dataToSave, { merge: true });

            saveMessage.textContent = `Perfil salvo! Tipo: ${tipoUsuarioInput.value}.`;
            saveMessage.classList.remove('hidden');

            setTimeout(() => {
                saveMessage.classList.add('hidden');
            }, 3000);

        } catch (error) {
            console.error("Erro ao salvar o perfil:", error);
            saveMessage.textContent = "Erro ao salvar o perfil.";
            saveMessage.classList.remove('hidden');
        } finally {
            buttonText.textContent = 'Salvar Perfil';
            saveButton.disabled = false;
        }
    });

    // Tenta autenticar na carga inicial, se necessário
    if (!auth.currentUser) {
        authenticate();
    }
    
</script>

</body>
</html>