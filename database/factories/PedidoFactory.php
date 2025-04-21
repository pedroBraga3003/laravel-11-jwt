<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pedido>
 */
class PedidoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'user_id' => 1,
        'nome_usuario' => 'Pedro',
        // 'codigo_pedido' => 'XPTO123',
        'origem' => $this->faker->word,
        'destino'=>  $this->faker->word,
        'data_ida'=> '2025-04-19 00:00:00',
        'data_volta'=> '2025-04-30 00:00:00',
        // 'status' => 'S',
        // 'produto' => $this->faker->word,
        // 'quantidade' => $this->faker->numberBetween(1, 5),
    ];
}
}
