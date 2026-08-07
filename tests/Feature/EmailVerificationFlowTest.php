<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationFlowTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Force MySQL since the project migrations use MySQL-specific types
     * (enum, json indexes, etc.) that sqlite cannot run. phpunit.xml forces
     * DB_CONNECTION=sqlite and DB_DATABASE=:memory:, so we must restore the
     * real MySQL database from .env BEFORE the app boots in parent::setUp().
     * DatabaseTransactions then wraps every test against the real DB safely.
     */
    protected function setUp(): void
    {
        // Read the .env DB_DATABASE before the Laravel app boots
        $envFile = @file_get_contents(getcwd() . '/.env') ?: '';
        preg_match('/^DB_DATABASE=(.*)$/m', $envFile, $matches);
        $databaseName = trim($matches[1] ?? 'appvariations');

        $_ENV['DB_CONNECTION'] = 'mysql';
        $_SERVER['DB_CONNECTION'] = 'mysql';
        putenv('DB_CONNECTION=mysql');

        $_ENV['DB_DATABASE'] = $databaseName;
        $_SERVER['DB_DATABASE'] = $databaseName;
        putenv('DB_DATABASE=' . $databaseName);

        parent::setUp();
    }

    /**
     * The complete registration flow:
     * User registers, account created unverified, verification email sent, auto-login, home page shows banner.
     */
    public function test_registration_creates_unverified_user_sends_verification_email_and_registers_flow(): void
    {
        Notification::fake();

        $unique = uniqid();

        $response = $this->post('/register', [
            'name' => 'Test Shopper',
            'email' => "shopper.{$unique}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'country_code' => '+91',
            'mobile_number' => "98{$unique}",
            'terms' => '1',
        ]);

        $response->assertRedirect('/home');

        // User created, unverified
        $user = User::where('email', "shopper.{$unique}@example.com")->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->hasVerifiedEmail(), 'New user must be unverified');

        // Verification email was sent through the default notification system
        Notification::assertSentTo($user, VerifyEmail::class);

        // User is auto-logged-in after registration
        $this->assertAuthenticatedAs($user);
    }

    /**
     * An unverified (but logged-in) user can keep shopping and sees the yellow verification banner.
     */
    public function test_unverified_user_sees_banner_and_can_continue_shopping(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // Banner is rendered on the home / dashboard page
        $home = $this->get('/home');
        $home->assertStatus(200);
        $home->assertSee('Verify your email to unlock all features.');
        $home->assertSee('Resend Email');

        // Shopping stays accessible - user is NOT blocked
        $products = $this->get('/products');
        $products->assertStatus(200);
    }

    /**
     * Clicking the verification link marks the email verified; the banner then disappears.
     */
    public function test_clicking_verification_link_marks_verified_and_banner_disappears(): void
    {
        Event::fake([Verified::class]);

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->actingAs($user)
            ->get($verificationUrl)
            ->assertRedirect('/home');

        // Email is now verified
        $this->assertNotNull($user->fresh()->email_verified_at);
        Event::assertDispatched(Verified::class);

        // Banner disappears on subsequent loads
        $home = $this->get('/home');
        $home->assertStatus(200);
        $home->assertDontSee('Verify your email to unlock all features.');
    }

    /**
     * Resending the verification email works from the banner.
     */
    public function test_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->post('/email/resend')
            ->assertRedirect();

        $this->assertTrue(session()->has('resent'));

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * Admin side: the data endpoint reports Verified / Not Verified correctly.
     */
    public function test_admin_user_list_reports_verification_status(): void
    {
        $verified = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $unverified = User::factory()->create([
            'email_verified_at' => null,
        ]);
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/users/data', [
            'draw' => 1,
            'start' => 0,
            'length' => 100,
            'status' => '',
            'role' => '',
            'verified' => '',
        ]);

        $response->assertOk();
        $data = $response->json('data');

        $verifiedRow = collect($data)->firstWhere('id', $verified->id);
        $unverifiedRow = collect($data)->firstWhere('id', $unverified->id);

        $this->assertNotNull($verifiedRow, 'Verified user row missing from admin list');
        $this->assertTrue($verifiedRow['verified'], 'Verified user should report verified=true');

        $this->assertNotNull($unverifiedRow, 'Unverified user row missing from admin list');
        $this->assertFalse($unverifiedRow['verified'], 'Unverified user should report verified=false');
        // Verified/Not Verified badge itself is rendered client-side (admin/users/index.blade.php) from this boolean field.
    }
}
