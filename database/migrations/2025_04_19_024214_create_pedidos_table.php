<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *  Um pedido deve incluir o 
     * ID do pedido, 
     * o nome do solicitante, 
     * ORIGEM, 
     * o destino, 
     * a data de ida, 
     * a data de volta e o 
     * status (solicitado, aprovado, cancelado).
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->comment('Id do usuario/cliente.');
            $table->string('nome_usuario',100)->nullable()->comment('Nome do usuario/cliente solicitante.');
            $table->string('codigo_pedido',50)->nullable()->comment('Código do pedido - date(Y-m-d H:i)+00+id');
            $table->text('origem')->nullable()->comment('Origem da viagem'); 
            $table->text('destino')->nullable()->comment('Destino da viagem'); 
            $table->dateTime('data_ida')->comment('Data da ida da viagem'); 
            $table->dateTime('data_volta')->nullable()->comment('Data da volta da viagem'); 
            $table->enum('status',['S','A','C'])->default('S')->comment('S - Solicitado | A - Aprovado | C - Cancelado'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
