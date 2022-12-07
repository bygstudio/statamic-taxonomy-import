<?php

namespace Bygstudio\StatamicTaxonomyImport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Bygstudio\StatamicTaxonomyImport\Jobs\ImportJob;
use Spatie\SimpleExcel\SimpleExcelReader;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Fields\Field;
use Statamic\Fieldtypes\Section;

class ImportController
{
    public function index()
    {
        return view('taxonomy-import::index');
    }

    public function targetSelect()
    {


        $collections = Taxonomy::all()->map(function ($collection) {
            return [
                'label' => $collection->title(),
                'value' => $collection->handle(),
            ];
        })->sortBy('label')->values()->toArray();

        $sites = Site::all()->map(function (\Statamic\Sites\Site $site) {
            return [
                'label' => $site->name(),
                'value' => $site->handle(),
            ];
        })->sortBy('label')->values()->toArray();

        return view('taxonomy-import::target', compact('collections', 'sites'));
    }

    public function showData(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file'],
            'delimiter' => ['required'],
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');
        $path = $file->storeAs('taxonomy-import', 'taxonomy-import.csv');
        $path = storage_path('app/' . $path);
        $delimiter = request('delimiter', ',');

        $reader = SimpleExcelReader::create($path)
            ->useDelimiter($delimiter);

        $request->session()->put('taxonomy-import-path', $path);
        $request->session()->put('taxonomy-import-delimiter', $delimiter);

        $keys = array_keys($reader->getRows()->first());

        $request->session()->put('taxonomy-import-keys', $keys);

        return view('taxonomy-import::show', [
            'rowCount' => $reader->getRows()->count(),
            'preview' => $reader->getRows()->take(5)->toArray(),
        ]);
    }

    public function import(Request $request)
    {
        $handle = $request->get('collection');
        $collection = Taxonomy::findByHandle($handle);

        /** @var \Statamic\Fields\Blueprint $blueprint */
        $blueprint = $collection->termBlueprint();
        $fields = $blueprint->fields()
            ->resolveFields()
            ->reject(function (Field $field) {
                return in_array($field->type(), [Section::handle()]);
            })
            ->toArray();

        $request->session()->put('taxonomy-import-collection', $handle);
        $request->session()->put('taxonomy-import-site', request('site'));

        return view('taxonomy-import::import', [
            'keys' => $request->session()->get('taxonomy-import-keys'),
            'fields' => $fields,
        ]);
    }

    public function finalize(Request $request)
    {
        $path = $request->session()->get('taxonomy-import-path');
        $delimiter = $request->session()->get('taxonomy-import-delimiter');
        $arrayDelimiter = $request->get('array_delimiter', '|');
        $mapping = collect($request->get('mapping'))->filter();
        $collection = $request->session()->get('taxonomy-import-collection');
        $site = session()->get('taxonomy-import-site', Site::default()->handle());

        $uuid = Str::uuid()->toString();

        ImportJob::dispatch($uuid, $path, $site, $collection, $mapping, $delimiter, $arrayDelimiter);

        $request->session()->forget('taxonomy-import-path');
        $request->session()->forget('taxonomy-import-keys');
        $request->session()->forget('taxonomy-import-collection');
        $request->session()->forget('taxonomy-import-site');

        return redirect(cp_route('taxonomy-import.show', $uuid));
    }

    public function show(string $uuid)
    {
        return view('taxonomy-import::finalize', [
            'uuid' => $uuid,
        ]);
    }
}
