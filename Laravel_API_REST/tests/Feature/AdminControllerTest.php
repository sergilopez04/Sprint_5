<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Rolls;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $admin;
    protected $adminToken;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:client --name=<client-name> --no-interaction --personal');

         $adminRole = Role::firstOrCreate(['name' => 'admin']);

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456789'),
            'role' => 'admin'
        ]);
        $this->admin->assignRole($adminRole);
        $this->adminToken = $this->admin->createToken('AdminToken')->accessToken;

        //$this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);

        User::factory()->count(5)->create()->each(function ($player) {
            for ($i = 0; $i < 3; $i++) { // Cambia 3 por el número de juegos que deseas crear
                $this->playGameForUser($player->id);
            }
        });
    }

    #[Test]
    public function test_admin_can_see_all_users()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->json('GET', '/api/players');

        $response->assertStatus(200)
            ->assertJsonStructure([
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                        'updated_at',
                    ],
            ]);
    }
    #[Test]
    public function test_admin_can_see_average_ranking()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->json('GET', 'api/players/ranking');

        $this->assertTrue(User::has('games')->exists());
        $response->assertStatus(200);
    }

    #[Test]
    public function test_admin_can_see_best_player(){

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->json('GET', 'api/players/ranking/winner');

        $this->assertTrue(User::has('games')->exists());
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'winner' => [
                         'player' => [
                             'id', 'name'
                         ],
                         'winPercentage'
                     ]
                 ]);

        $winner = $response->json('winner');
        // $this->assertGreaterThan(0, $winner['winPercentage']);
    }

    #[Test]
    public function test_admin_can_see_worst_player(){

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->json('GET', 'api/players/ranking/loser');

        $this->assertTrue(User::has('games')->exists());
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'loser' => [
                         'player' => [
                             'id', 'name' 
                         ],
                         'winPercentage'
                     ]
                 ]);

        $winner = $response->json('winner');
    }


    private function playGameForUser($playerId)
    {
    $die1 = random_int(1, 6);
    $die2 = random_int(1, 6);
    $sum = $die1 + $die2;
    $result = ($sum === 7) ? 'Won' : 'Lost';
    $rollDate = now();

    Rolls::create([
        'player_id' => $playerId,
        'die1_value' => $die1,
        'die2_value' => $die2,
        'result' => $result,
        'roll_date' => $rollDate,
    ]);
    }
}
