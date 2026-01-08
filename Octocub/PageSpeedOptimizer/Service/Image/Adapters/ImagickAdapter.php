<?php
namespace Octocub\PageSpeedOptimizer\Service\Image\Adapters;

use Octocub\PageSpeedOptimizer\Model\Config;

class ImagickAdapter
{
    public function isSupported(): bool
    {
        return class_exists(\Imagick::class);
    }

    public function compressInPlace(string $absPath, int $jpegQuality): void
    {
        $img = new \Imagick($absPath);
        $fmt = strtolower($img->getImageFormat());

        if (in_array($fmt, ['jpeg','jpg'], true)) {
            $img->setImageCompressionQuality($jpegQuality);
            $img->stripImage();
            $img->writeImage($absPath);
        } elseif ($fmt === 'png') {
            $img->stripImage();
            $img->setImageCompressionQuality(85);
            $img->writeImage($absPath);
        }

        $img->clear();
        $img->destroy();
    }

    public function resizeVariant(string $absPath, int $targetWidth, string $algorithm): void
    {
        $img = new \Imagick($absPath);
        $w = $img->getImageWidth();
        $h = $img->getImageHeight();
        if ($w <= $targetWidth) { $img->clear(); $img->destroy(); return; }

        $filter = match ($algorithm) {
            'mitchell' => \Imagick::FILTER_MITCHELL,
            default => \Imagick::FILTER_LANCZOS,
        };

        $newH = (int)round(($h / $w) * $targetWidth);
        $img->resizeImage($targetWidth, $newH, $filter, 1);

        $dir = dirname($absPath);
        $name = pathinfo($absPath, PATHINFO_FILENAME);
        $ext  = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        $out  = $dir . '/' . $name . '-w' . $targetWidth . '.' . $ext;

        $img->stripImage();
        $img->writeImage($out);
        $img->clear();
        $img->destroy();
    }

    public function createModernVariants(string $absPath, Config $config): void
    {
        $dir = dirname($absPath);
        $name = pathinfo($absPath, PATHINFO_FILENAME);
        $ext  = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

        $variants = glob($dir . '/' . $name . '*.' . $ext) ?: [];
        $variants[] = $absPath;
        $variants = array_unique($variants);

        foreach ($variants as $src) {
            if ($config->enableWebp()) {
                $this->convert($src, 'webp', $config->webpQuality());
            }
            if ($config->enableAvif()) {
                try {
                    $this->convert($src, 'avif', $config->avifQuality());
                } catch (\Throwable $e) {
                    // AVIF may not be supported on server; ignore
                }
            }
        }
    }

    private function convert(string $src, string $to, int $quality): void
    {
        $out = preg_replace('/\.[a-z0-9]+$/i', '.' . $to, $src);
        if (!$out) return;
        if (is_file($out)) return;

        $img = new \Imagick($src);
        $img->setImageFormat($to);
        $img->setImageCompressionQuality($quality);

        $img->stripImage();
        $img->writeImage($out);
        $img->clear();
        $img->destroy();
    }
}
