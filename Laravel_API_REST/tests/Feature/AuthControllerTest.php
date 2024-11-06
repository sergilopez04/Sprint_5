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

class AuthControllerTest extends TestCase
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

    #[Test]
    public function test_user_can_logout(){

        $user = User::factory()->create([
            'email' => 'player2@player.com',
            'password' => bcrypt('123456789'),
        ]);

        $token = $user->createToken('PlayerToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logged out successfully',
        ]);
    }

    #[Test]
    public function test_user_can_update_name(){
    $user = User::factory()->create([
        'email' => 'player2@player.com',
        'password' => bcrypt('123456789'),
    ]);

    $token = $user->createToken('PlayerToken')->accessToken;

    $newName = 'User2';

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->putJson('/api/players/' . $user->id, [
        'name' => $newName,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => sprintf('New name is %s, change completed.', $newName),
    ]);
    }

    #[Test]
    public function test_user_login_with_wrong_password(){
        $user = User::factory()->create([
            'email' => 'wrong@password.com',
            'password' => bcrypt('123456789'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'error' => 'Unauthorized',
        ]);
    }
    #[Test]
    public function test_user_cannot_have_duplicated_nickname(){
        $user = User::factory()->create([
            'nickname' => 'Player2',
            'email' => 'origina@nickname.com',
            'password' => bcrypt('123456789'),
        ]);

        $response = $this->postJson('/api/player', [
            'nickname' => 'Player2',
            'email' => 'notoriginal@nickname.com',
            'password' => bcrypt('123456789'),
        ]);

        $response->assertStatus(422)
        ->assertJson([
            "error" => "Validation Error.",
            "details"=> [
                "nickname"=> ["The nickname has already been taken."]]        
            
        ]);}

        #[Test]
        public function test_user_without_nickname_equals_to_anonymous(){
            $user = User::factory()->create([
                'nickname' => 'hi',
            ]);
            $token = $user->createToken('UserToken')->accessToken;
            $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->putJson('/api/players/' . $user->id, [
            'nickname' => '',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Anonymous', $user->fresh()->name);

        }
}

