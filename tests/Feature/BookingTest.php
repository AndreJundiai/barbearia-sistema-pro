<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Hairdresser;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;
    private Hairdresser $barber;

    protected function setUp(): void
    {
        parent::setUp();

        // Uma terça-feira às 08:00 (barbearia abre às 09:00, fecha domingo)
        Carbon::setTestNow(Carbon::parse('2026-11-17 08:00', 'America/Sao_Paulo'));
        config(['services.mercadopago.access_token' => null]);

        $this->service = Service::create(['name' => 'Corte', 'duration_minutes' => 45, 'price' => 50]);
        $this->barber = Hairdresser::create(['name' => 'João Silva', 'commission_percent' => 40]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function book(array $overrides = [])
    {
        return $this->postJson('/agendar/processar', array_merge([
            'client_name' => 'Carlos Souza',
            'client_phone' => '(11) 98888-7777',
            'service_id' => $this->service->id,
            'hairdresser_id' => $this->barber->id,
            'scheduled_at' => '2026-11-17 10:00:00',
            'payment_method' => 'pay_later',
        ], $overrides));
    }

    public function test_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Corte')->assertSee('João Silva');
        $this->get('/agendar')->assertOk();
        $this->get('/agendar?servico='.$this->service->id)->assertOk();
        $this->get('/galeria')->assertOk();
        $this->get('/privacidade')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/booking')->assertRedirect('/agendar');
    }

    public function test_slots_respect_hours_lunch_and_duration(): void
    {
        $slots = collect($this->getJson('/agendar/disponibilidade?'.http_build_query([
            'date' => '2026-11-17', 'hairdresser_id' => $this->barber->id, 'service_id' => $this->service->id,
        ]))->assertOk()->json())->pluck('time');

        $this->assertEquals('09:00', $slots->first());
        $this->assertNotContains('11:30', $slots, '11:30 + 45min invade o almoço');
        $this->assertNotContains('12:30', $slots);
        $this->assertContains('14:00', $slots);
        $this->assertEquals('18:30', $slots->last(), 'último horário que termina até 19:30');
    }

    public function test_closed_day_has_no_slots(): void
    {
        $this->getJson('/agendar/disponibilidade?'.http_build_query([
            'date' => '2026-11-22', 'hairdresser_id' => $this->barber->id, 'service_id' => $this->service->id,
        ]))->assertOk()->assertExactJson([]);
    }

    public function test_guest_can_book_and_manage_appointment(): void
    {
        $response = $this->book(['scheduled_at' => '2026-11-18 10:00:00'])->assertOk()->assertJson(['success' => true]);

        $appointment = Appointment::firstOrFail();
        $this->assertSame('scheduled', $appointment->status);
        $this->assertSame('11988887777', $appointment->customer->phone);
        $response->assertJson(['redirect' => route('booking.show', $appointment->token)]);

        $this->get(route('booking.show', $appointment->token))->assertOk()->assertSee('Horário confirmado');

        $this->post(route('booking.cancel', $appointment->token))->assertRedirect();
        $this->assertSame('cancelled', $appointment->fresh()->status);
    }

    public function test_overlapping_booking_is_rejected(): void
    {
        $this->book()->assertOk();
        // 10:30 sobrepõe o corte de 45 min das 10:00
        $this->book(['client_phone' => '11977776666', 'scheduled_at' => '2026-11-17 10:30:00'])->assertStatus(409);
        $this->book(['client_phone' => '11977776666', 'scheduled_at' => '2026-11-17 11:00:00'])->assertOk();
        $this->assertSame(2, Appointment::count());
    }

    public function test_past_time_is_rejected(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 15:00', 'America/Sao_Paulo'));
        $this->book(['scheduled_at' => '2026-11-17 10:00:00'])->assertStatus(409);
    }

    public function test_online_payment_requires_mercado_pago(): void
    {
        $this->book(['payment_method' => 'pix'])->assertStatus(422);
    }

    public function test_returning_customer_is_reused(): void
    {
        $this->book()->assertOk();
        $this->book(['client_phone' => '11988887777', 'scheduled_at' => '2026-11-18 10:00:00'])->assertOk();
        $this->assertSame(1, Customer::count());
    }

    public function test_completing_appointment_records_revenue_and_loyalty(): void
    {
        $this->book()->assertOk();
        $appointment = Appointment::firstOrFail();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->patch(route('appointments.status', $appointment), ['action' => 'complete'])->assertRedirect();

        $appointment->refresh();
        $this->assertSame('completed', $appointment->status);
        $this->assertSame('paid', $appointment->payment_status);
        $this->assertSame(1, $appointment->customer->loyalty_points);
        $this->assertEquals(50, Transaction::sum('amount'));

        // Concluir de novo não duplica a receita
        $this->actingAs($admin)->patch(route('appointments.status', $appointment), ['action' => 'complete']);
        $this->assertSame(1, Transaction::count());
    }

    public function test_admin_pages_render(): void
    {
        $this->book()->assertOk();
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['/dashboard', '/dashboard?data=2026-11-17', '/financeiro', '/services', '/hairdressers', '/users', '/configuracoes', '/profile', '/admin/galeria/nova'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_barber_cannot_access_admin_areas(): void
    {
        $barber = User::factory()->create(['role' => 'barber']);
        $this->actingAs($barber)->get('/dashboard')->assertOk();
        $this->actingAs($barber)->get('/configuracoes')->assertForbidden();
        $this->actingAs($barber)->get('/services')->assertForbidden();
    }

    public function test_ai_corrector_is_not_public(): void
    {
        $this->get('/ai-corrector')->assertRedirect('/painel-login');
        $this->post('/ai-corrector/create-module', ['files' => [['path' => 'hacked.php', 'content' => 'x']]])->assertRedirect('/painel-login');
        $this->assertFileDoesNotExist(base_path('hacked.php'));

        // Nem um barbeiro logado consegue usar
        $this->actingAs(User::factory()->create(['role' => 'barber']))->get('/ai-corrector')->assertForbidden();
    }

    public function test_settings_update_changes_site(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put('/configuracoes', [
            'shop_name' => 'Navalha de Ouro',
            'whatsapp' => '(11) 91234-5678',
            'open_time' => '10:00',
            'close_time' => '18:00',
            'lunch_start' => '',
            'lunch_end' => '',
            'slot_interval' => 60,
            'closed_days' => ['0', '1'],
            'booking_days_ahead' => 15,
            'min_notice_minutes' => 0,
            'cancel_notice_hours' => 2,
            'loyalty_target' => 8,
        ])->assertRedirect();

        $this->assertSame('11912345678', Setting::get('whatsapp'));
        $this->get('/')->assertSee('Navalha de Ouro')->assertSee('wa.me/5511912345678');

        $slots = collect($this->getJson('/agendar/disponibilidade?'.http_build_query([
            'date' => '2026-11-17', 'hairdresser_id' => $this->barber->id, 'service_id' => $this->service->id,
        ]))->json())->pluck('time');
        $this->assertEquals(['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'], $slots->all());
    }

    public function test_seeder_is_idempotent_and_keeps_admin_password(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->first();
        $admin->update(['password' => bcrypt('nova-senha')]);
        $count = Service::count();

        $this->seed();

        $this->assertSame($count, Service::count());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('nova-senha', $admin->fresh()->password));
    }
}
