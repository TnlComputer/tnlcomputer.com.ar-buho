<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Guarda las imágenes en public/uploads/{carpeta} (no en storage/) para no depender
 * de `storage:link`, que en algunos hostings compartidos no se puede crear.
 */
trait StoresUploads
{
    protected function storeUpload(?UploadedFile $file, string $folder, ?string $previous = null): ?string
    {
        if (! $file) {
            return $previous;
        }

        $dir = public_path("uploads/{$folder}");
        File::ensureDirectoryExists($dir);

        $name = Str::random(20).'.'.$file->extension();
        $file->move($dir, $name);

        $this->deleteUpload($previous);

        return "uploads/{$folder}/{$name}";
    }

    protected function deleteUpload(?string $path): void
    {
        // Solo borra lo subido desde el panel; nunca las imágenes migradas de img/.
        if ($path && str_starts_with($path, 'uploads/')) {
            File::delete(public_path($path));
        }
    }
}
