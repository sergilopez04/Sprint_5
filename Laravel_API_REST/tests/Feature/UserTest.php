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

class UserTest extends TestCase
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
         $playerRole = Role::firstOrCreate(['name' => 'player']);

        $this->user = User::factory()->create([
            'name' => 'Test Player',
            'email' => 'player@test.com',
            'password' => bcrypt('123456789'),
            'role' => 'player'
        ]);
        $this->user->assignRole($playerRole);

        $this->admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456789'),
            'role' => 'admin'
        ]);
        $this->admin->assignRole($adminRole);
        $this->adminToken = $this->admin->createToken('AdminToken')->accessToken;

        //$this->artisan('db:seed', ['--class' => DatabaseSeeder::class]);
    }


    #[Test]
    public function test_user_can_be_created()
    {
        $response = $this->postJson('/api/player', [
            'name' => 'Player1',
            'email' => 'player1@player.com',
            'password' => '123456789',
        ]);
        $response->assertStatus(201)
         ->assertJson([
        'message' => 'Player registered successfully',
        'token' => true,
        'player' => [
            'name' => 'Player1',
            'nickname' => 'Anonymous'
        ],
        ]);
        $this->assertCount(3, User::all());

        $user = User::where('email', 'player1@player.com')->first();

        $this->assertEquals($user->email, 'player1@player.com');
        $this->assertEquals($user->name, 'Player1');
        $this->assertTrue(Hash::check('123456789', $user->password));
        $this->assertEquals($user->role, 'player');
        $this->assertEquals($user->nickname, 'Anonymous');
        // $this->assertNotNull($user->api_token);


    }

    #[Test]
    public function test_user_can_login(){

        $user = User::factory()->create([
            'email' => 'player2@player.com',
            'password' => bcrypt('123456789'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => '123456789',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'token',
        ]);
        $this->assertNotEmpty($response->json('token'));
    }
}
