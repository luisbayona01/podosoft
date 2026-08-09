<?php

namespace App\Http\Controllers;

use App\Models\DocumentoClinico;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClinicalDocumentController extends Controller
{
    public function show(DocumentoClinico $document): StreamedResponse
    {
        abort_unless(auth()->check(), 401);

        if (!Storage::disk('private')->exists($document->ruta)) {
            abort(404, 'Archivo no encontrado');
        }

        $content = Storage::disk('private')->get($document->ruta);

        return response()->stream(
            function () use ($content) {
                echo $content;
            },
            200,
            [
                'Content-Type' => $document->tipo_mime,
                'Content-Disposition' => 'inline; filename="' . $document->nombre_archivo . '"',
                'Cache-Control' => 'private, max-age=86400',
            ]
        );
    }

    public function download(DocumentoClinico $document): StreamedResponse
    {
        abort_unless(auth()->check(), 401);

        if (!Storage::disk('private')->exists($document->ruta)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::disk('private')->download(
            $document->ruta,
            $document->nombre_archivo,
            [
                'Content-Type' => $document->tipo_mime,
            ]
        );
    }
}