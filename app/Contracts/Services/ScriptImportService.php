<?php


namespace App\Contracts\Services;


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

/**
 * Interface ScriptImportService
 * @package App\Contracts\Services
 */
interface ScriptImportService
{
    public function import(int $roomId, UploadedFile $file): ?Collection;
}
