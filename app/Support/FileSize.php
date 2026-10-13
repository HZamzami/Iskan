<?php

namespace App\Support;

/**
 * تحويل بين الكيلوبايت (الوحدة التي يتوقعها FileUpload::maxSize وقاعدة التحقق
 * max، والمخزَّنة في قاعدة البيانات) والميجابايت (الوحدة التي يراها المستخدم).
 */
class FileSize
{
    public static function kbToMb(int $kilobytes): float
    {
        return round($kilobytes / 1024, 2);
    }

    public static function mbToKb(float $megabytes): int
    {
        return max(1, (int) round($megabytes * 1024));
    }

    /**
     * نص مقروء للحد الأقصى، مثل "20 ميجابايت" أو "2.5 ميجابايت".
     */
    public static function label(int $kilobytes): string
    {
        $megabytes = self::kbToMb($kilobytes);

        return rtrim(rtrim(number_format($megabytes, 2, '.', ''), '0'), '.').' ميجابايت';
    }
}
