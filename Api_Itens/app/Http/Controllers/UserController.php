<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(['users' => User::all()], 200);
    }
    public function store(Request $request)
    {
        $request->validate( [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'tipo_usuario' => 'required|in:admin,customer',
        ]);
        $user = User::create($request->all());
        return response()->json(['users'=> $user],200);
    }
    public function show($id){
        try {
            $user = User::findOrFail($id);
            return response()->json(['user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User não encontrado'], 404);
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate( [
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|string|email|unique:users,email,'.$id,
            'password' => 'sometimes|required|string|min:6',
            'tipo_usuario' => 'sometimes|required|in:admin,customer',
        ]);
        $user = update($request->all());
        return response()->json(['user' => $user], 200);
    }
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deletado com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'User não encontrado'], 404);
        }
    }
}
?>