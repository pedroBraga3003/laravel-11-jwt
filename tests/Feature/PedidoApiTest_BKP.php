<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tymon\JWTAuth\Facades\JWTAuth;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pedido;

class PedidoApiTes_OLDt extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_autenticado_pode_ver_pedidos(){
        // Criar um usuario
        $user = User::factory()->create();
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);

        // Fazer uma requisicao autenticada
        $response = $this->actingAs($user, 'api')
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/pedidos');

            // $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            //             ->getJson('/api/pedidos');

        // Verificar se o status da resposta e 200
        $response->assertStatus(200);
    }
    public function test_nao_autenticado_nao_pode_ver_pedidos(){
        // Fazer uma requisicao sem autenticacao
        $response = $this->getJson('/api/pedidos');
        // Verificar se o status da resposta e 401
        $response->assertStatus(401);
    }
    public function test_pode_criar_um_pedido(){
        // Cria um usuário
        $user = User::factory()->create();
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        // Dados do pedido a ser criado
        $dados = [
            'produto' => 'Notebook',
            'quantidade' => 1,
            'status' => 'pendente',
        ];

        // Faz uma requisição POST autenticada para criar o pedido
        $response = $this->actingAs($user, 'api')
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/pedidos', $dados);

        // Verifica se a resposta tem status 201 e contém o fragmento JSON esperado
        // $response->assertStatus(201)
        //         ->assertJsonFragment(['produto' => 'Notebook']);
    }
    
    public function test_usuario_autenticado_jwt_pode_acessar(){
        // Cria um usuário fake
        $user = User::factory()->create();
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        // Faz uma requisição GET para /pedidos com o token no header
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/pedidos');
        // Verifica se o status da resposta é 200
        $response->assertStatus(200);
    }
}
