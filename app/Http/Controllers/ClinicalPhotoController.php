<?php

namespace App\Http\Controllers;

use App\Models\FotografiaClinica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClinicalPhotoController extends Controller
{
    public function show(FotografiaClinica $photo): StreamedResponse
    {
        abort_unless(auth()->check(), 401);

        if (!Storage::disk('private')->exists($photo->ruta)) {
            abort(404, 'Archivo no encontrado');
        }

        $content = Storage::disk('private')->get($photo->ruta);

        return response()->stream(
            function () use ($content) {
                echo $content;
            },
            200,
            [
                'Content-Type' => $photo->tipo_mime,
                'Content-Disposition' => 'inline; filename="' . $photo->nombre_archivo . '"',
                'Cache-Control' => 'private, max-age=86400',
            ]
        );
    }

    public function download(FotografiaClinica $photo): StreamedResponse
    {
        abort_unless(auth()->check(), 401);

        if (!Storage::disk('private')->exists($photo->ruta)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::disk('private')->download(
            $photo->ruta,
            $photo->nombre_archivo,
            [
                'Content-Type' => $photo->tipo_mime,
            ]
        );
    }
}