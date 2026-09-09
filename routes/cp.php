<?php

use Estouai\Weave\Http\Controllers\FieldMetaController;
use Estouai\Weave\Http\Controllers\PreviewController;
use Illuminate\Support\Facades\Route;

Route::post('weave/preview', [PreviewController::class, 'render']);
Route::post('weave/field-meta', [FieldMetaController::class, 'render']);
