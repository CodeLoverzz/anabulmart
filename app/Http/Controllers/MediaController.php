<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Stream file media dari folder storage tanpa terhalang 403 XAMPP.
     *
     * @param string $path
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     */
    public function show(string $path)
    {
        // 1. Normalisasi garis miring Windows (\) ke Linux/Laravel (/)
        $normalizedPath = str_replace('\\', '/', $path);

        // 2. Bersihkan prefix public/ atau storage/
        $cleanPath = ltrim(str_replace(['public/', 'storage/'], '', $normalizedPath), '/');

        // 3. Cek di disk public (storage/app/public/...)
        if (Storage::disk('public')->exists($cleanPath)) {
            $filePath = Storage::disk('public')->path($cleanPath);
            return response()->file($filePath);
        }

        // 4. Cek fallback di disk local (storage/app/...)
        if (Storage::exists($cleanPath)) {
            $filePath = Storage::path($cleanPath);
            return response()->file($filePath);
        }

        // 5. Jika file fisik tidak ditemukan
        abort(404, 'File media tidak ditemukan di storage server.');
    }
}