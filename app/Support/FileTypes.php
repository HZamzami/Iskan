<?php

namespace App\Support;

/**
 * خريطة موحّدة بين امتدادات الملفات وأنواع MIME المقابلة لها، تُستخدم لبناء
 * تلميح المتصفح عند رفع الملفات. التحقق الفعلي من الامتداد يعتمد على قاعدة
 * التحقق "extensions:" الأدق من فحص MIME، لذا هذه الخريطة تبقى تلميحاً فقط
 * وتضيف "application/octet-stream" كحل احتياطي للامتدادات غير المعروفة.
 */
class FileTypes
{
    /**
     * @var array<string, string>
     */
    private const MIME_TYPES = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'kml' => 'application/vnd.google-earth.kml+xml',
        'kmz' => 'application/vnd.google-earth.kmz',
        'zip' => 'application/zip',
        'rar' => 'application/vnd.rar',
        'gpkg' => 'application/geopackage+sqlite3',
        'dwg' => 'image/vnd.dwg',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
    ];

    /**
     * @return array<int, string>
     */
    public static function suggestions(): array
    {
        return array_keys(self::MIME_TYPES);
    }

    /**
     * @param  array<int, string>  $extensions
     * @return array<int, string>
     */
    public static function mimeTypesFor(array $extensions): array
    {
        return collect($extensions)
            ->map(fn (string $extension): string => self::MIME_TYPES[strtolower($extension)] ?? 'application/octet-stream')
            ->push('application/octet-stream')
            ->unique()
            ->values()
            ->all();
    }
}
