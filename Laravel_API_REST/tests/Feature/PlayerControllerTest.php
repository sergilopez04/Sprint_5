<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlayerControllerTest extends TestCase
{
    use RefreshDatabase;

    use RefreshDatabase, WithFaker;

    protected $player;
    protected $playerToken;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:client --name=<client-name> --no-interaction --personal');

         $playerRole = Role::firstOrCreate(['name' => 'player']);

        $this->player = User::factory()->create([
            'name' => 'Test Player',
            'email' => 'player@test.com',
            'password' => bcrypt('123456789'),
            'role' => 'player'
        ]);
        $this->player->assignRole($playerRole);
        $this->playerToken = $this->player->createToken('PlayerToken')->accessToken;

    }
    /** @test */
    public function user_can_update_own_name()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->playerToken,
        ])->json('PUT', "/api/players/{$this->player->id}", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'New name is New Name, change completed.']);
                 
        $this->assertDatabaseHas('users', [
            'id' => $this->player->id,
            'name' => 'New Name'
        ]);
    }

    /** @test */
    public function user_cannot_update_to_an_existing_name()
    {
        $user1 = User::factory()->create(['name' => 'User1']);
        $user2 = User::factory()->create(['name' => 'Existing Name']);
        $token = $user1->createToken('authToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('PUT', "/api/players/{$user1->id}", [
            'name' => 'Existing Name'
        ]);

        $response->assertStatus(400)
                 ->assertJson(['message' => 'The name is already in use. Please choose another one.']);
    }

    /** @test */
    public function user_cannot_update_another_users_name()
    {
        $user1 = User::factory()->create(['name' => 'User1']);
        $user2 = User::factory()->create(['name' => 'User2']);
        $token = $user1->createToken('authToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('PUT', "/api/players/{$user2->id}", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'You cannot modify another user\'s name.']);
    }

    /** @test */
    public function update_name_returns_404_if_user_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('authToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->json('PUT', "/api/players/9999", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(404)
                 ->assertJson(['message' => 'User not found.']);
    }

}
