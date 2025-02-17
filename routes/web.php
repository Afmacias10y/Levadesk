<?php

use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Rutas públicas
Route::get('error', function(){
    abort('404');
})->name('page_404');

Route::get('error', function(){
    abort('403');
})->name('page_403');

Route::get('error', function(){
    abort('500');
})->name('page_500');

Route::get('error', function(){
    abort('419');
})->name('page_419');

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes(['verify' => true]);


Route::get('/email/verify', function () {
    return view('auth.verify');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

Route::get('storage-link', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:cache');
    Artisan::call('storage:link');
});

Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::get('/home', [HomeController::class, 'index'])->middleware('can:home')->name('home');

    Route::group(['middleware' => 'can:sociedad'], function () {
        Route::view('sociedad', 'admin.sociedad.index')->name('sociedad');
        Route::view('tipo-solicitud', 'admin.solicitud.index')->name('solicitud');
        Route::view('categorias', 'admin.categoria.index')->name('categoria');
        Route::view('subcategorias', 'admin.subcategoria.index')->name('subcategoria');
        Route::view('ans', 'admin.ans.index')->name('ans');
        Route::view('estados', 'admin.estado.index')->name('estado');
        Route::view('cargos', 'admin.cargo.index')->name('cargo');
        Route::view('grupo', 'admin.grupo.index')->name('grupo');
        Route::view('urgencia', 'admin.urgencia.index')->name('urgencia');
        Route::view('impacto', 'admin.impacto.index')->name('impacto');
        Route::view('usuarios', 'admin.usuarios.index')->name('usuarios');
        Route::view('sociedades', 'admin.sociedad.aplicaciones')->name('sociedades');
        Route::view('relacion', 'admin.relacion.index')->name('relacion');
        Route::view('roles', 'admin.roles.index')->name('roles');
    });

    Route::group(['middleware' => 'can:gestion'], function () {
        Route::view('gestion', 'admin.gestion.index')->name('gestion');
        Route::view('gestionar', 'admin.gestionar.index')->name('gestionar');
        Route::view('estadisticas', 'admin.gestion.estadisticas')->name('estadisticas');
    });

    Route::group(['middleware' => 'can:ticket'], function () {
        Route::view('ticket', 'admin.ticket.index')->name('ticket');
        Route::view('verTicket', 'admin.ticket.verTicket')->name('verTicket');
    });

    Route::group(['middleware' => 'can:aprobacion'], function () {
        Route::view('aprobacion', 'admin.aprobacion.index')->name('aprobacion');
        Route::view('aprobar', 'admin.aprobacion.aprobar')->name('aprobar');
        Route::view('cambios', 'admin.cambios.index')->name('cambios');
        Route::view('cambio', 'admin.cambios.cambios')->name('cambio');
        Route::view('buscador', 'admin.buscador.buscador')->name('buscador');
    });

    Route::group(['middleware' => 'can:dashboard'], function () {
        Route::view('dashboard', 'admin.dashboard.dashboard')->name('dashboard');
    });

    Route::view('perfil', 'admin.perfil.perfil')->name('perfil');
});
