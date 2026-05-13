<?php

use App\Models\Orcamento;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('usuario.dashboard');
    })->name('dashboard');

    Route::view('profile', 'profile')->name('profile');
});

// Rotas do administrador
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('dashboard', 'admin.dashboard')->name('dashboard');
    Route::view('usuarios', 'admin.usuarios')->name('usuarios');
    Route::view('orcamento/novo', 'admin.orcamento.novo')->name('orcamento.novo');
    Route::get('orcamento/{orcamento}/parametrizacao', function (Orcamento $orcamento) {
        if ($orcamento->is_historico) {
            return redirect()
                ->route('admin.loa-historica.consultar')
                ->with('status', 'LOAs anteriores são somente para consulta.');
        }

        return view('admin.orcamento.parametrizacao', ['orcamentoId' => $orcamento->id]);
    })->name('orcamento.parametrizacao');
    Route::get('orcamento/{orcamento}/cortes', function (Orcamento $orcamento) {
        if ($orcamento->is_historico) {
            return redirect()
                ->route('admin.loa-historica.consultar')
                ->with('status', 'LOAs anteriores são somente para consulta.');
        }

        return view('admin.orcamento.cortes', ['orcamentoId' => $orcamento->id]);
    })->name('orcamento.cortes');
    Route::view('relatorios', 'admin.relatorios')->name('relatorios');
    Route::view('loa-historica/importar', 'admin.loa-historica.importar')->name('loa-historica.importar');
    Route::view('loa-historica/consultar', 'admin.loa-historica.consultar')->name('loa-historica.consultar');
});

// Rotas do usuário (secretarias)
Route::middleware(['auth', 'verified', 'role:usuario'])->prefix('usuario')->name('usuario.')->group(function () {
    Route::view('dashboard', 'usuario.dashboard')->name('dashboard');
    Route::view('loa/{orcamento}/preencher', 'usuario.loa.preencher')->name('loa.preencher');
    Route::view('loa/{orcamento}/metas-acoes', 'usuario.loa.metas-acoes')->name('loa.metas-acoes');
    Route::view('loa/{orcamento}/obras', 'usuario.loa.obras')->name('loa.obras');
    Route::view('loa/{orcamento}/enviar', 'usuario.loa.enviar')->name('loa.enviar');
    Route::view('relatorios', 'usuario.relatorios')->name('relatorios');
    Route::view('loa-historica', 'usuario.loa-historica')->name('loa-historica');
});

require __DIR__.'/auth.php';
