<?php

use App\Http\Controllers\AiCorrectorController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GuestBookingController;
use App\Http\Controllers\HairdresserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Site público
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/manifest.webmanifest', [HomeController::class, 'manifest'])->name('manifest');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/privacidade', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/galeria', [GalleryController::class, 'index'])->name('gallery.index');

// Agendamento online
Route::get('/agendar', [GuestBookingController::class, 'index'])->name('booking.index');
Route::get('/agendar/disponibilidade', [GuestBookingController::class, 'checkAvailability'])
    ->middleware('throttle:60,1')->name('booking.availability');
Route::post('/agendar/processar', [GuestBookingController::class, 'process'])
    ->middleware('throttle:10,1')->name('booking.process');
Route::get('/agendar/sucesso/{id}', [GuestBookingController::class, 'legacySuccess'])->whereNumber('id')->name('booking.success');
Route::get('/meu-horario/{token}', [GuestBookingController::class, 'show'])->name('booking.show');
Route::get('/meu-horario/{token}/status', [GuestBookingController::class, 'status'])
    ->middleware('throttle:30,1')->name('booking.status');
Route::post('/meu-horario/{token}/cancelar', [GuestBookingController::class, 'cancel'])
    ->middleware('throttle:5,1')->name('booking.cancel');

// Atalho antigo do manifest
Route::redirect('/booking', '/agendar');

// Webhooks
Route::post('/webhooks/mercadopago', [WebhookController::class, 'handleMercadoPago'])->name('webhooks.mercadopago');

/*
|--------------------------------------------------------------------------
| Painel (equipe)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AppointmentController::class, 'index'])->name('dashboard');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::get('/painel/horarios', [AppointmentController::class, 'slots'])->name('appointments.slots');

    Route::get('/financeiro', [FinanceController::class, 'index'])->name('finance.index');
    Route::post('/financeiro', [FinanceController::class, 'store'])->name('finance.store');
    Route::post('/financeiro/importar', [FinanceController::class, 'importCsv'])->name('finance.import');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('admin')->group(function () {
        Route::resource('hairdressers', HairdresserController::class)->except('show');
        Route::resource('users', UserController::class)->only(['index', 'store', 'destroy']);
        Route::resource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/configuracoes', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/configuracoes', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/financeiro/pagar-comissao/{hairdresser}', [FinanceController::class, 'payCommissions'])->name('finance.pay-commissions');

        Route::get('/admin/galeria/nova', [GalleryController::class, 'create'])->name('gallery.create');
        Route::post('/admin/galeria', [GalleryController::class, 'store'])->name('gallery.store');
        Route::delete('/admin/galeria/{image}', [GalleryController::class, 'destroy'])->name('gallery.destroy');

        // AI Corrector: altera arquivos do servidor, por isso só admin e só quando habilitado
        Route::middleware('ai-corrector')->prefix('ai-corrector')->name('ai-corrector.')->group(function () {
            Route::get('/', [AiCorrectorController::class, 'index'])->name('index');
            Route::post('/analyze', [AiCorrectorController::class, 'analyze'])->name('analyze');
            Route::post('/apply', [AiCorrectorController::class, 'apply'])->name('apply');
            Route::post('/update-key', [AiCorrectorController::class, 'updateKey'])->name('update-key');
            Route::post('/brainstorm', [AiCorrectorController::class, 'brainstorm'])->name('brainstorm');
            Route::post('/create-module', [AiCorrectorController::class, 'createModule'])->name('create-module');
        });
    });
});

require __DIR__.'/auth.php';
