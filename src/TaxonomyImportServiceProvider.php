<?php

namespace Bygstudio\StatamicTaxonomyImport;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class TaxonomyImportServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../resources/dist/js/cp.js',
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function boot()
    {
        parent::boot();

        Statamic::booted(function () {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'taxonomy-import');

            Nav::extend(function (\Statamic\CP\Navigation\Nav $nav) {
                $nav->tools('Taxonomy Import')
                    ->route('taxonomy-import.index')
                    ->icon('upload')
                    ->can('use taxonomy import')
                    ->active('taxonomy-import');
            });

            Permission::group('taxonomy-import', 'Taxonomy Import', function () {
                Permission::register('use taxonomy import');
            });
        });
    }
}
