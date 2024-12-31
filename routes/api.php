<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ImportacaoController;

Route::post('/importar-arquivo', [ImportacaoController::class, 'importarArquivo']);
