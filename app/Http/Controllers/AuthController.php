<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class AuthController extends Controller{
    /**
     * Creates a new user with the given information and returns the created user and the token associated with it
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        
        logger()->info('Inicio da criação do usúario');
        
        // Validate the given information
        // $request->validate([
        //     'name' => 'required',
        //     'email' => 'required|email|unique:users',
        //     'password' => 'required|min:6',
        //     'tipo_usuario' => 'required',
        // ]);
        $data = $request->only('name', 'email', 'password','tipo_usuario');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'tipo_usuario' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ],[
            'name.required' => 'O campo name é obrigatório',
            'email.required' => 'O campo Email  obrigatório',
            'tipo_usuario.required' => 'O campo Tipo de usuario  obrigatório',
            'password.required' => 'O campo password  obrigatório'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }
        // Create a new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'tipo_usuario' => $request->tipo_usuario,
            'data_nascimento' => $request->data_nascimento,
            'celular' => $request->celular,
            'password' => bcrypt($request->password),
        ]);
        logger()->info('Usúario criado com sucesso');
        
        // Generate a token for the user
        $token = JWTAuth::fromUser($user);
        logger()->info('Token criado com sucesso');
        
        // Return the user and the generated token
        return response()->json(compact('user', 'token'));
    }
    /**
     * Validate the user credentials and return a token if the credentials are valid
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        // Validate the user credentials
        $credentials = $request->only('email', 'password');
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }
        // Attempt to validate the credentials
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }
        // Return the valid token
        return response()->json(compact('token'));
    }
    /**
     * Remove the token from the blacklist so it can't be used anymore
     * and return a success message
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(){
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
    /**
     * Returns the user that is currently authenticated
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(){
        return response()->json(JWTAuth::parseToken()->authenticate());
    }/**
     * ver Usuario
     *
     * @param Request $request
     * @return void
     */
    public function verUsuario(Request $request){
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['user' => $user]);
        } catch (Illuminate\Auth\AuthenticationException $e) {
            // Usuário não autenticado
            return response()->json(['message' => 'Não autenticado'], 401);
        } catch (Exception $e) {
            // Outras exceções
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
}
