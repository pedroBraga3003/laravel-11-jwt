<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pedido;
use App\Models\Configuracao;

class PedidoApiTest extends TestCase{
    use RefreshDatabase;

    public function test_usuario_autenticado_pode_listar_pedidos(){
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'M9MfI'.rand(0,9999999).'@example.com',
            'tipo_usuario' => 'E'
        ]);
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        // $pedido = Pedido::factory()->create();
        $pedido = Pedido::factory()->create(['status' => 'S']);
        $response = $this->actingAs($user, 'api')
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/pedidos');
        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $pedido->id]);
    }

    public function test_usuario_nao_autenticado_recebe_erro_401(){
        $response = $this->getJson('/api/pedidos');

        $response->assertStatus(401);
    }

    public function test_usuario_pode_criar_pedido(){
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'M9MfI'.rand(0,9999999).'@example.com',
            'tipo_usuario' => 'C'
        ]);
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        $dados = [
            'origem' => 'BH/MG',
            'destino'=>  'DIVI/MG',
            'data_ida'=> '2025-04-19',
            'data_volta'=> '2025-04-30',
        ];
        $response = $this->actingAs($user, 'api')
                    ->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/pedidos', $dados);
        $response->assertStatus(200)
                ->assertJsonFragment($dados);
    }
    public function test_usuario_pode_atualizar_pedido(){
        $user = User::factory()->create();
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        $pedido = Pedido::factory()->create([
            'user_id' =>  $user->id,
            'nome_usuario' => $user->name,
            'codigo_pedido' => 'XPTO123',
            'origem' => 'BH/MG',
            'destino'=>  'DIVI/MG',
            'data_ida'=> '2025-04-19',
            'data_volta'=> '2025-04-30',
            'status' => 'S'
        ]);
        $novosDados = [
                'origem' => 'BH/MG',
                'destino'=>  'DIVI/MG',
                'data_ida'=> '2025-04-19',
                'data_volta'=> '2025-04-30',
                'status' => 'A'
        ];
        $response = $this->actingAs($user, 'api')
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->putJson("/api/pedidos/{$pedido->id}", $novosDados);
        $response->assertStatus(200)
                ->assertJsonFragment($response->json());
    }
    public function test_usuario_pode_atualizar_pedido_fora_do_prazo(){
        $user = User::factory()->create();
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        $pedido = Pedido::factory()->create([
            'user_id' =>  $user->id,
            'nome_usuario' => $user->name,
            'codigo_pedido' => 'XPTO123',
            'origem' => 'BH/MG',
            'destino'=>  'DIVI/MG',
            'data_ida'=> '2025-04-01',
            'data_volta'=> '2025-04-30',
            'status' => 'S'
        ]);
        $novosDados = [
                'origem' => 'BH/MG',
                'destino'=>  'DIVI/MG',
                'data_ida'=> '2025-04-01',
                'data_volta'=> '2025-04-30',
                'status' => 'A'
        ];
        $response = $this->actingAs($user, 'api')
                        ->withHeader('Authorization', 'Bearer ' . $token)
                        ->putJson("/api/pedidos/{$pedido->id}", $novosDados);
        $response->assertStatus(200)
                ->assertJsonFragment($response->json());
    }
    public function test_usuario_pode_cancelar_pedido(){
        $configuracao = Configuracao::factory()->create();
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'M9MfI'.rand(0,9999999).'@example.com',
            'tipo_usuario' => 'E'
        ]);
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        $pedido = Pedido::factory()->create([
            'user_id' =>  $user->id,
            'nome_usuario' => $user->name,
            'codigo_pedido' => 'XPTO123',
            'origem' => 'BH/MG',
            'destino'=>  'DIVI/MG',
            'data_ida'=> '2025-04-28',
            'data_volta'=> '2025-04-30',
            'status' => 'S'
        ]);
        $response = $this->actingAs($user, 'api')
                    ->withHeader('Authorization', 'Bearer ' . $token)
                    ->putJson("/api/pedidos/cancelar/{$pedido->id}");
        $response->assertStatus(200)
                ->assertJson($response->json());
    }
    public function test_usuario_pode_cancelar_pedido_fora_do_prazo(){
        $configuracao = Configuracao::factory()->create(['limite_dias_cancelamento' =>3]);
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'M9MfI'.rand(0,9999999).'@example.com',
            'tipo_usuario' => 'E'
        ]);
        // Gera um token JWT para o usuário
        $token = JWTAuth::fromUser($user);
        $pedido = Pedido::factory()->create([
            'user_id' =>  $user->id,
            'nome_usuario' => $user->name,
            'codigo_pedido' => 'XPTO123',
            'origem' => 'BH/MG',
            'destino'=>  'DIVI/MG',
            'data_ida'=>  '2025-04-20',
            'data_volta'=> '2025-04-30',
            'status' => 'S'
        ]);
        $response = $this->actingAs($user, 'api')
                    ->withHeader('Authorization', 'Bearer ' . $token)
                    ->putJson("/api/pedidos/cancelar/{$pedido->id}");
        $response->assertStatus(400)
                ->assertJson($response->json());
    }
}
