<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Tests\TestCase;
use App\Helpers\JWTHelper;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $userAdmin = User::where('email', 'juanperez@sportsod.com')->first();
        //dd($userAdmin);
        $body = ['name'=>'Man Ming',
                 'email'=>'manming@sportsod.com',
                 'password'=>'1234'];
        $token = JWTHelper::generate($userAdmin, env('APP_KEY', 'secret'));
        //dd($token);
        $header = ['Authorization' => "Bearer ".$token];

        $response = $this->withHeaders($header)
        ->post('/user/create', $body);
        
        //dd($response);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email'=>'manming@sportsod.com']);
    }
}
