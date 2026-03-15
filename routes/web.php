<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    
    Route::get('/financeiro', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/financeiro', [FinanceController::class, 'store'])->name('finance.store');
    Route::post('/financeiro/importar', [FinanceController::class, 'importCsv'])->name('finance.import');
    Route::post('/financeiro/pagar-comissao/{hairdresser}', [FinanceController::class, 'payCommissions'])->name('finance.pay-commissions');

    Route::middleware('admin')->group(function () {
        Route::resource('hairdressers', \App\Http\Controllers\HairdresserController::class);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// AI Corrector (Público)
Route::get('/ai-corrector', [\App\Http\Controllers\AiCorrectorController::class, 'index'])->name('ai-corrector.index');
Route::post('/ai-corrector/analyze', [\App\Http\Controllers\AiCorrectorController::class, 'analyze'])->name('ai-corrector.analyze');
Route::post('/ai-corrector/apply', [\App\Http\Controllers\AiCorrectorController::class, 'apply'])->name('ai-corrector.apply');
Route::post('/ai-corrector/update-key', [\App\Http\Controllers\AiCorrectorController::class, 'updateKey'])->name('ai-corrector.update-key');
Route::post('/ai-corrector/brainstorm', [\App\Http\Controllers\AiCorrectorController::class, 'brainstorm'])->name('ai-corrector.brainstorm');
Route::post('/ai-corrector/create-module', [\App\Http\Controllers\AiCorrectorController::class, 'createModule'])->name('ai-corrector.create-module');

// Rotas Públicas de Agendamento
Route::get('/agendar', [\App\Http\Controllers\GuestBookingController::class, 'index'])->name('booking.index');
Route::get('/agendar/disponibilidade', [\App\Http\Controllers\GuestBookingController::class, 'checkAvailability'])->name('booking.availability');
Route::post('/agendar/processar', [\App\Http\Controllers\GuestBookingController::class, 'process'])->name('booking.process');
Route::get('/agendar/sucesso/{id}', [\App\Http\Controllers\GuestBookingController::class, 'success'])->name('booking.success');

// Webhooks
Route::post('/webhooks/mercadopago', [\App\Http\Controllers\WebhookController::class, 'handleMercadoPago']);

require __DIR__.'/auth.php';
