<?php

namespace Tests\Feature;

use App\Models\Hairdresser;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminBugfixTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_csv_import_accepts_brazilian_dates_and_amounts(): void
    {
        $csv = "Data;Descrição;Tipo;Valor\n"
            ."14/11/2026;Aluguel;Saída;1.234,56\n"
            ."15/11/2026;Venda pomada;Entrada;45,90\n"
            ."2026-11-16;Produtos;saida;100\n";

        $this->actingAs($this->admin)->post('/financeiro/importar', [
            'csv_file' => UploadedFile::fake()->createWithContent('extrato.csv', $csv),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(3, Transaction::count());

        $rent = Transaction::where('description', 'Aluguel')->first();
        $this->assertSame('expense', $rent->type);
        $this->assertEquals(1234.56, (float) $rent->amount);
        $this->assertSame('2026-11-14', \Carbon\Carbon::parse($rent->transaction_date)->format('Y-m-d'));

        $this->assertSame('income', Transaction::where('description', 'Venda pomada')->value('type'));
        $this->assertSame('expense', Transaction::where('description', 'Produtos')->value('type'));
    }

    public function test_csv_import_skips_invalid_rows_instead_of_crashing(): void
    {
        $csv = "Data;Descrição;Tipo;Valor\n"
            ."data errada;Linha ruim;Entrada;10\n"
            ."16/11/2026;Linha boa;Entrada;20\n";

        $this->actingAs($this->admin)->post('/financeiro/importar', [
            'csv_file' => UploadedFile::fake()->createWithContent('extrato.csv', $csv),
        ])->assertRedirect();

        $this->assertSame(['Linha boa'], Transaction::pluck('description')->all());
    }

    public function test_hairdresser_can_be_created_inactive(): void
    {
        $this->actingAs($this->admin)->post('/hairdressers', [
            'name' => 'Novo Barbeiro',
            'commission_percent' => 40,
            // checkbox "active" desmarcado: o navegador não envia o campo
        ])->assertRedirect();

        $this->assertFalse(Hairdresser::where('name', 'Novo Barbeiro')->firstOrFail()->active);
    }
}
