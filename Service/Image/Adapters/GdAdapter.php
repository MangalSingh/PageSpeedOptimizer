<?php
namespace Octocub\PageSpeedOptimizer\Service\Image\Adapters;

use Octocub\PageSpeedOptimizer\Model\Config;

class GdAdapter
{
    public function isSupported(): bool
    {
        return extension_loaded('gd');
    }

    public function compressInPlace(string $absPath, int $jpegQuality): void
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg'], true)) return;

        $im = @imagecreatefromjpeg($absPath);
        if (!$im) return;
        imagejpeg($im, $absPath, $jpegQuality);
        imagedestroy($im);
    }

    public function resizeVariant(string $absPath, int $targetWidth, string $algorithm): void
    {
        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) return;

        $info = @getimagesize($absPath);
        if (!$info) return;
        [$w, $h] = $info;
        if ($w <= 0 || $h <= 0 || $w <= $targetWidth) return;

        $newH = (int)round(($h / $w) * $targetWidth);

        $src = match ($ext) {
            'png' => @imagecreatefrompng($absPath),
            'gif' => @imagecreatefromgif($absPath),
            default => @imagecreatefromjpeg($absPath),
        };
        if (!$src) return;

        $dst = imagecreatetruecolor($targetWidth, $newH);
        imagecopyresampled($dst, $src, 0,0,0,0, $targetWidth, $newH, $w, $h);

        $dir = dirname($absPath);
        $name = pathinfo($absPath, PATHINFO_FILENAME);
        $out  = $dir . '/' . $name . '-w' . $targetWidth . '.' . $ext;

        match ($ext) {
            'png' => imagepng($dst, $out, 6),
            'gif' => imagegif($dst, $out),
            default => imagejpeg($dst, $out, 82),
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    public function createModernVariants(string $absPath, Config $config): void
    {
        if (!$config->enableWebp() || !function_exists('imagewebp')) return;

        $dir = dirname($absPath);
        $name = pathinfo($absPath, PATHINFO_FILENAME);
        $ext  = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        $variants = glob($dir . '/' . $name . '*.' . $ext) ?: [];
        $variants[] = $absPath;
        $variants = array_unique($variants);

        foreach ($variants as $src) {
            $out = preg_replace('/\.[a-z0-9]+$/i', '.webp', $src);
            if (!$out || is_file($out)) continue;

            $im = match ($ext) {
                'png' => @imagecreatefrompng($src),
                'gif' => @imagecreatefromgif($src),
                default => @imagecreatefromjpeg($src),
            };
            if (!$im) continue;

            imagewebp($im, $out, $config->webpQuality());
            imagedestroy($im);
        }
    }
}
