<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pedido extends Model{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'nome_usuario',
        'codigo_pedido',
        'origem',
        'destino',
        'data_ida',
        'data_volta',
        'status',
        'created_at',
        'updated_at'
    ];
    /**
     * Lista paginacao
     *
     * @param array $condicoes
     * @return void
     */
    public function listaPaginacao($condicoes = []){
        $pedidos = [];
        $campos = [];
        $campos = ['pedidos.*','users.tipo_usuario'];
        $pedidos = $this
                        ->select( $campos )
                        ->join('users', 'pedidos.user_id', '=', 'users.id')
                        ->where( $condicoes )
                        ->paginate(20);
        return $pedidos;
    }
    /**
     * Salvar
     *
     * @param [type] $dados
     * @return void
     */
    public function salvar($dados){
        $tabelaOs = DB::select("SHOW TABLE STATUS LIKE 'pedidos'");
        $proximo_id = $tabelaOs[0]->Auto_increment;
        $dados['codigo_pedido'] = date('dmY').str_pad( $proximo_id, '7', '0' , STR_PAD_LEFT);
        $pedido = [];
        $pedido = $this->create($dados);
        return $pedido;
    }
    /**
     * Atualizar
     *
     * @param [type] $dados
     * @return void
     */
    public function atualizar($dados){
        $pedido = [];
        $pedido = $this->update($dados);
        return $pedido;
    }
    /**
     * Buscar Por ID
     *
     * @param [type] $dados
     * @return void
     */
    public function buscarPorId( $id){
        $pedido = [];
        $pedido = $this->select( $this->fillable )
                        ->where([
                            ['pedidos.id','=',  $id]
                        ])->first();
        return $pedido;
    }
    /**
     * Buscar Por ID com usuario
     *
     * @param [type] $dados
     * @return void
     */
    public function buscarPorIdComUsuario( $id){
        $pedido = [];
        $campos = [];
        $campos = ['pedidos.*','users.tipo_usuario','users.name as nome_cliente', 'users.email as email_cliente'];
        $pedido = $this->select( $campos )
                        ->join('users', 'pedidos.user_id', '=', 'users.id')
                        ->where([
                            ['pedidos.id','=',  $id]
                        ])->first();
        return $pedido;
    }
}
