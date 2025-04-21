<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Configuracao;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PedidosController extends Controller{
    protected $config_id = 1;
    /**
     * Listar os pedidos
     *
     * Este método lista os pedidos com base nas condições especificadas.
     * O usuário deve estar autenticado para acessar este recurso.
     *
     * @param Request $request - A solicitação HTTP recebida.
     * @return \Illuminate\Http\Response - A resposta HTTP com os dados dos pedidos ou mensagem de erro.
     */
    public function index(Request $request) {
        try {
            $objPedido = new Pedido();
            $condicoes = [];
            $user = JWTAuth::parseToken()->authenticate(); // Autentica o usuário
            // Se o usuário for do tipo cliente, filtra pedidos por usuário
            if (!empty($user->tipo_usuario) && $user->tipo_usuario == 'C') {
                $condicoes[] = ['pedidos.user_id', '=', $user->id];
            }
            // Filtra pedidos pelo status, se fornecido
            if (!empty($request->status) && in_array($request->status, ['S', 'A', 'C'])) {
                $condicoes[] = ['pedidos.status', '=', $request->status];
            }
            // Filtra pedidos pelo destino, se fornecido
            if (!empty($request->destino)) {
                $condicoes[] = ['pedidos.destino', 'like', '%' . $request->destino . '%'];
            }
            // Filtra pedidos por data de ida ou volta, se fornecidas
            if (!empty($request->data_inicio) && !empty($request->data_fim)) {
                $campo = 'pedidos.data_ida';
                if (!empty($request->tipo_data_busca) && $request->tipo_data_busca == 'volta') {
                    $campo = 'pedidos.data_volta';
                }
                $condicoes[] = [$campo, '>=', $request->data_inicio . ' 00:00:00'];
                $condicoes[] = [$campo, '<=', $request->data_fim . ' 23:59:59'];
            }
            // Lista os pedidos com paginação
            $pedidos = $objPedido->listaPaginacao($condicoes);
            unset($objPedido);
            // Retorna a resposta de sucesso com os dados dos pedidos
            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Pedidos',
                'dados' => $pedidos
            ], Response::HTTP_OK);

        } catch (Illuminate\Auth\AuthenticationException $e) {
            // Retorna resposta de erro se o usuário não estiver autenticado
            return response()->json(['message' => 'Não autenticado'], 401);
        } catch (Exception $e) {
            // Retorna resposta de erro para outras exceções
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
    /**
     * Adicionar a integração
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function adicionar(Request $request){
        try {
            $objPedido = new Pedido();            
            // verificando se o usuario logado eh um cliente
            $user = JWTAuth::parseToken()->authenticate();
            if(!empty($user->tipo_usuario) && $user->tipo_usuario == 'E'){
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Usuario nao autorizado.',
                    'dados' => []
                ], Response::HTTP_BAD_REQUEST);
            }
            $rules = [
                'origem' => 'required',
                'destino' => 'required',
                'data_ida' => 'required',
                'data_volta' => 'required',
            ];
            $mensagens = [
                'origem.required' => 'O parametro "origem" é obrigatorio .',
                'destino.required' => 'O parametro "destino" é obrigatorio .',
                'data_ida.required' => 'O parametro "data da ida" é obrigatorio .',
                'data_volta.required' => 'O parametro "data da volta" é obrigatorio .',
            ];
            // Validacao de campos
            $validacao = Validator::make($request->all(), $rules, $mensagens);
            if($validacao->fails()){
                // Return error response if validation fails
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Erro de validação.',
                    'dados' => $validacao->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            // Add dados do usuario
            $request->request->add(['user_id' => $user->id]);
            $request->request->add(['nome_usuario' => $user->name]);
            // Salvar  pedido
            $pedido = $objPedido->salvar($request->all());
            unset($objPedido);
            // Return success 
            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Pedido adicionado com sucesso.',
                'dados' => $pedido
            ], Response::HTTP_OK);
        } catch (Illuminate\Auth\AuthenticationException $e) {
            // Usuário não autenticado
            return response()->json(['message' => 'Não autenticado'], 401);
        } catch (Exception $e) {
            // Outras exceções
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
    /**
     * editar a integração
     *
     * Este método edita um pedido existente.
     *
     * @param Request $request - A solicitação HTTP recebida.
     * @param int $id - O ID do pedido a ser editado.
     * @return \Illuminate\Http\Response - A resposta HTTP com os dados do pedido editado ou mensagem de erro.
     */
    public function editar(Request $request, $id){
        try {
            // Buscar pedido existente
            $objConfiguracao = new Configuracao();
            $objPedido = new Pedido();
            
            // Verificando se o usuario logado eh um cliente ou a empresa
            $user = JWTAuth::parseToken()->authenticate();
            $pedido = $objPedido->buscarPorId( $id );
            if(empty($pedido['id']) && ( !empty($user->tipo_usuario) && $user->tipo_usuario == 'C' && $pedido['user_id'] != $user->id) ){
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Pedido nao encontrado.',
                    'dados' => []
                ], Response::HTTP_BAD_REQUEST);
            }
            // Regras de validacao
            $rules = [
                'origem' => 'required',
                'destino' => 'required',
                'data_ida' => 'required',
                'data_volta' => 'required'
            ];
            if( (!empty($user->tipo_usuario) && $user->tipo_usuario == 'C') && (!empty($request->status)) ){
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Usuario não autorizado a alterar o status.',
                    'dados' => []
                ], Response::HTTP_BAD_REQUEST);
            }else if(!empty($user->tipo_usuario) && $user->tipo_usuario == 'E'){
                // Se for empresa, adiciona o campo status como obrigatorio
                $rules['status'] = 'required';
            }
            // Mensagens de erro para cada regra de validacao
            $mensagens = [
                'origem.required' => 'O parametro "origem" é obrigatorio .',
                'destino.required' => 'O parametro "destino" é obrigatorio .',
                'data_ida.required' => 'O parametro "data da ida" é obrigatorio .',
                'data_volta.required' => 'O parametro "data da volta" é obrigatorio .',
                'status.required' => 'O parametro "status" é obrigatorio .',
            ];
            // Validacao de campos
            $validacao = Validator::make($request->all(), $rules, $mensagens);
            if($validacao->fails()){
                // Return error response if validation fails
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Erro de validação.',
                    'dados' => $validacao->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            // Verificando se o status do request eh cancelado
            if(!empty($request->status) && $request->status == 'C'){
                // $configuracao = $objConfiguracao->buscarPorId($this->config_id);
                $configuracao = $objConfiguracao->buscar();
                $verifica = \App\Helpers\PedidoHelper::verificaRegraCancelamento($pedido,$configuracao);
                if(!$verifica['sucesso']){
                    return response()->json($verifica, Response::HTTP_BAD_REQUEST);
                }
            }
            $notificacao = [];
            if( (!empty($user->tipo_usuario) && $user->tipo_usuario == 'E') && (!empty($request->status)) ){
                $notificacao = \App\Helpers\PedidoHelper::verificaDisparoNotificacao($pedido,$request->status);
                if(!empty($notificacao['notificacao'])){
                    $pedidoDados = $objPedido->buscarPorIdComUsuario( $id );
                    $email = \App\Helpers\PedidoHelper::disparoNotificacao($pedidoDados,$notificacao);
                }
            }
            
            // Atualizar pedido
            $pedido->atualizar($request->all());
            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Atualizado com sucesso.',
                'dados' => $pedido
            ], Response::HTTP_OK);
        } catch (Illuminate\Auth\AuthenticationException $e) {
            // Usuário não autenticado
            return response()->json(['message' => 'Não autenticado'], 401);
        } catch (Exception $e) {
            // Outras exceções
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
    /**
     * Visualizar Pedido
     *
     * Este método busca e retorna um pedido pelo seu ID.
     *
     * @param Request $request - A solicitação HTTP recebida.
     * @param int $id - O ID do pedido a ser visualizado.
     * @return \Illuminate\Http\Response - A resposta HTTP com os dados do pedido ou mensagem de erro.
     */
    public function visualizar(Request $request, $id) {
        try {
            $objPedido = new Pedido(); // Instancia um novo objeto Pedido
            $user = JWTAuth::parseToken()->authenticate();
            $pedido = $objPedido->buscarPorId( $id );
            if(empty($pedido['id']) && ( !empty($user->tipo_usuario) && $user->tipo_usuario == 'C' && $pedido['user_id'] != $user->id) ){
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Pedido não encontrado.',
                    'dados' => []
                ], Response::HTTP_BAD_REQUEST);
            }
            // Retorna a resposta de sucesso com os dados do pedido
            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Pedido',
                'dados' => $pedido
            ], Response::HTTP_OK);
        } catch (Illuminate\Auth\AuthenticationException $e) {
            // Usuário não autenticado
            return response()->json(['message' => 'Não autenticado'], 401);
        } catch (Exception $e) {
            // Outras exceções
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
    /**
     * Cancelar o pedido
     *
     * @param Request $request - A solicitação HTTP recebida.
     * @param int $id - O ID do pedido a ser cancelado.
     * @return \Illuminate\Http\Response - A resposta HTTP com os dados do pedido ou mensagem de erro.
     */
    public function cancelar(Request $request, $id){
        try {
            $objConfiguracao = new Configuracao();
            $objPedido = new Pedido();
            $user = JWTAuth::parseToken()->authenticate();
            $pedido = $objPedido->buscarPorId( $id );
            if(empty($pedido['id']) && ( !empty($user->tipo_usuario) && $user->tipo_usuario == 'C' && $pedido['user_id'] != $user->id) ){
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Usuário não autorizado a alterar o status.',
                    'dados' => []
                ], Response::HTTP_BAD_REQUEST);
            }
            $pedido = $objPedido->buscarPorId( $id );
            // Verifica se o pedido foi encontrado
            if (empty($pedido['id'])) {
                return response()->json([
                    'sucesso' => false,
                    'mensagem' => 'Pedido não encontrado.',
                    'dados' => []
                ], Response::HTTP_BAD_REQUEST);
            }
            // $configuracao = $objConfiguracao->buscarPorId($this->config_id);
            $configuracao = $objConfiguracao->buscar();
            $verifica = \App\Helpers\PedidoHelper::verificaRegraCancelamento($pedido,$configuracao);
            if(!$verifica['sucesso']){
                return response()->json($verifica, Response::HTTP_BAD_REQUEST);
            }else{
                // Atualizar pedido
                $pedido->atualizar(['status' => 'C']);
                return response()->json($verifica, Response::HTTP_OK);
            }
        } catch (Illuminate\Auth\AuthenticationException $e) {
            // Usuário não autenticado
            return response()->json(['message' => 'Não autenticado'], 401);
        } catch (Exception $e) {
            // Outras exceções
            return response()->json(['message' => 'Erro interno no servidor'], 500);
        }
    }
}
