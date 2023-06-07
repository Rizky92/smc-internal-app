<?php

namespace Tests\Feature;

use App\Models\Aplikasi\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * @test
     */
    public function test_user_must_login_to_access_admin()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /** 
     * @test
     */
    public function test_user_can_login()
    {
        $user = User::findByNRP('88888888');

        $this->post('/login', [
            'username' => '88888888',
            'pass' => '8888'
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertSee($user->nama);
    }
}
