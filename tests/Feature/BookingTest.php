<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Helpers\JWTHelper;
use Tests\TestCase;

class BookingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $body = ['field_id'=>1,
                 'user_id'=>1,
                 'booking_start'=>'2024-02-29 08:45:00',
                 'booking_end'=>'2024-02-29 09:45:00'];
        $userPlayer = User::where('email', 'marktwain@sportsod.com')->first();
        $token = JWTHelper::generate($userPlayer, env('APP_KEY', 'secret'));
        
        $header = ['Authorization' => "Bearer ".$token];

        $response = $this->withHeaders($header)
        ->post('/booking/create', $body);
        //dd($response);
        $response->assertStatus(201);
        //$this->assertDatabaseHas('bookings', ['field_id'=>1,'user_id'=>$userPlayer->id]);
    }
}
