<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Configuracao extends Model{   
    use HasFactory;
    protected $table = 'configuracoes';
    protected $fillable = [
        'id',
        'limite_dias_cancelamento',
        'created_at',
        'updated_at'
    ];
    /**
     * Buscar 
     *
     * @param [type] $id
     * @return void
     */
    public function buscar(){
        $pedido = [];
        $pedido = $this
                    ->select($this->fillable)
                    ->orderBy( 'id','asc' )
                    ->first();
        return $pedido;
    }
    /**
     * Buscar Configuracao
     *
     * @param [type] $id
     * @return void
     */
    public function buscarPorId( $id){
        $pedido = [];
        $pedido = $this->select($this->fillable)
                        ->where([
                            ['id','=',  $id]
                        ])->first();
        return $pedido;
    }
}
