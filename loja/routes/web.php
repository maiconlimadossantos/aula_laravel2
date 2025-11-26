<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Define rotas para os métodos index (Painel) e store (Adicionar)
Route::resource('items', ItemController::class)->only(['index', 'store', 'show']);

// Define a rota raiz para o painel de itens
Route::get('/', function () {
    return redirect()->route('items.index');
});
use App\Http\Controllers\UsuarioController;

// Rotas para a gestão de perfil
Route::middleware(['auth'])->group(function () {
    // Rota para a view/formulário (simulando a index do seu HTML)
    Route::get('/profile/config', [UsuarioController::class, 'showProfileForm'])->name('profile.config');

    // Endpoint para carregar os dados via AJAX
    Route::get('/profile/data', [UsuarioController::class, 'getProfileData'])->name('profile.data');

    // Endpoint para salvar os dados via AJAX (simulando o 'setDoc')
    Route::post('/profile/update', [UsuarioController::class, 'updateProfile'])->name('profile.update');
// Endpoint para buscar a lista de usuários (pode exigir autenticação ou permissão específica)
Route::middleware(['auth'])->group(function () { 
    Route::get('/users/list', [UsuarioController::class, 'listUsers'])->name('api.users.list');
});
});

// Certifique-se de ter rotas de login/autenticação definidas para testar!
?>