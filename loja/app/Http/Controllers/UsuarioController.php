<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class UsuarioController extends Controller
{
    /**
     * Opcional: Construtor para garantir que apenas usuários autenticados acessem.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Retorna a view do painel de configuração (o HTML que você forneceu).
     * Mapeia para a rota GET /profile/config
     */
    public function showProfileForm()
    {
        // Obtém o usuário autenticado
        $user = Auth::user();
        
        // Retorna a view (que seria o seu código HTML adaptado para Blade)
        // Passamos o objeto $user para a view.
        return view('user.profile_config', compact('user'));
    }

    /**
     * Obtém os dados do perfil do usuário logado (Usado em requisições AJAX).
     * Mapeia para a rota GET /profile/data
     */
    public function getProfileData()
    {
        $user = Auth::user();

        // Retorna apenas os campos necessários, simulando a estrutura de dados do Firebase/Firestore
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'tipo_usuario' => $user->tipo_usuario,
            // Não expomos senhas, é claro.
        ]);
    }

    /**
     * Atualiza o perfil do usuário logado (Mapeia para a submissão do formulário).
     * Mapeia para a rota POST /profile/update
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 1. Validação
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Regra para garantir que o email seja único, mas exceto o email atual do usuário.
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)], 
            'tipo_usuario' => ['required', 'string', Rule::in(['cliente', 'funcionario', 'admin'])],
        ]);
        
        // **IMPORTANTE**: No Laravel, o campo 'tipo_usuario' geralmente NÃO DEVE
        // ser atualizável pelo próprio usuário final, pois define permissões.
        // Mantenha essa lógica apenas para fins de demonstração do formulário.
        
        // 2. Atualização dos Dados
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->tipo_usuario = $request->input('tipo_usuario');
        $user->save();

        // 3. Retorno da resposta AJAX
        return response()->json([
            'message' => 'Perfil atualizado com sucesso!',
            'user' => $user
        ], 200);
    }





    /**
     * Mapeia para a rota GET /users
     * Exibe a view com a lista de usuários.
     */
    public function index()
    {
        // Garante que o usuário logado está disponível na view (opcional)
        $currentUser = Auth::user(); 

        // Retorna a view que contém seu HTML adaptado para Blade
        return view('user.user_list', compact('currentUser'));
    }

    /**
     * Endpoint AJAX para buscar a lista de usuários (Substitui o onSnapshot do Firestore).
     * Mapeia para a rota GET /api/users/list
     */
    public function listUsers(Request $request)
    {
        // 1. Inicia a query
        $query = User::query();
        
        // 2. Aplica Filtro (se houver)
        $filterType = $request->get('filter_type');
        
        if ($filterType && $filterType !== 'todos') {
            $query->where('tipo_usuario', $filterType);
        }

        // 3. Aplica Ordenação
        // Ordena por tipo e depois por nome, simulando a lógica do front-end
        $users = $query->orderBy('tipo_usuario')
                      ->orderBy('name')
                      ->get();

        // 4. Mapeia os dados para uma estrutura similar à do front-end/Firestore
        // Ocultamos dados sensíveis como 'password' e 'email_verified_at' por padrão no Model User.
        $userList = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tipo_usuario' => $user->tipo_usuario,
                'created_at' => $user->created_at->toDateTimeString(),
            ];
        });

        // Retorna a lista de usuários como JSON
        return response()->json($userList);
    }
}

?>