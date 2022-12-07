<?php

use Bygstudio\StatamicTaxonomyImport\Http\Controllers\ImportController;

Route::get('taxonomy-import', ['\\'. ImportController::class, 'index'])->name('taxonomy-import.index');
Route::post('taxonomy-import/target', ['\\'. ImportController::class, 'targetSelect'])->name('taxonomy-import.target-select');
Route::get('taxonomy-import/target', ['\\'. ImportController::class, 'targetSelect']);
Route::post('taxonomy-import/show', ['\\'. ImportController::class, 'showData'])->name('taxonomy-import.show-data');
Route::post('taxonomy-import/import', ['\\'. ImportController::class, 'import'])->name('taxonomy-import.import');
Route::post('taxonomy-import/finalize', ['\\'. ImportController::class, 'finalize'])->name('taxonomy-import.finalize');
Route::get('taxonomy-import/{uuid}', ['\\'. ImportController::class, 'show'])->name('taxonomy-import.show');
