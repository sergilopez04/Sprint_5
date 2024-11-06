<?php

namespace Tests\Feature;

use App\Models\Rolls;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RollControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $player;
    protected $playerToken;
    protected $player2;
    protected $game;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:client --name=<client-name> --no-interaction --personal');

        $playerRole = Role::firstOrCreate(['name' => 'player']);

        $this->player = User::factory()->create();
        $this->playerToken = $this->player->createToken('PlayerToken')->accessToken;
        $this->player2 = User::factory()->create();
    }

    #[Test]
    public function test_player_can_play(){
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->postJson("api/players/{$this->player->id}/games");

        $response->assertStatus(200)
        ->assertJsonStructure([
                'message',
                'Die 1',
                'Die 2',
                'Total',
            ]);
    }

    public function test_player_showGames()
    {
    $this->withHeaders([
        'Authorization' => "Bearer $this->playerToken",
    ])->postJson("api/players/{$this->player->id}/games");

    $response = $this->withHeaders([
        'Authorization' => "Bearer $this->playerToken",
    ])->getJson("api/players/{$this->player->id}/games");

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'Win percentage',
                 'Games played' => [
                     '*' => ['Game number', 'Die 1', 'Die 2', 'Result']
                 ]
                 ]);
    }

    public function test_player_can_delete_games()
    {
        $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->postJson("api/players/{$this->player->id}/games");

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->deleteJson("api/players/{$this->player->id}/games");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message'
                 ]);
    }

    public function test_player_cannot_delete_other_player_game(){
        $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->postJson("api/players/{$this->player->id}/games");

        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->deleteJson("api/players/{$this->player2->id}/games");

        $response->assertStatus(403)
                 ->assertJsonStructure([
                     'message'
                 ]);
    
    }
    public function test_player_cannot_play_other_player_game(){
        $response = $this->withHeaders([
            'Authorization' => "Bearer $this->playerToken",
        ])->postJson("api/players/{$this->player2->id}/games");

        $response->assertStatus(403)
                 ->assertJsonStructure([
                     'message'
                 ]);
    }
}