<?php
namespace Octocub\PageSpeedOptimizer\Service\Image;

use Octocub\PageSpeedOptimizer\Model\Config;
use Octocub\PageSpeedOptimizer\Service\Image\Adapters\ImagickAdapter;
use Octocub\PageSpeedOptimizer\Service\Image\Adapters\GdAdapter;

class Optimizer
{
    public function __construct(
        private Config $config,
        private ?ImagickAdapter $imagickAdapter = null,
        private ?GdAdapter $gdAdapter = null
    ) {}

    public function optimize(string $absPath, string $device): void
    {
        if (!is_file($absPath)) return;

        $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','gif','webp','avif'], true)) return;

        $adapter = $this->pickAdapter();

        $adapter->compressInPlace($absPath, $this->config->jpegQuality());

        $targets = match ($device) {
            'mobile' => [480, 768],
            'tablet' => [768, 1024],
            'desktop'=> [1200, 1600],
            'amp'    => [768, 1200],
            default  => [768, 1200, 1600],
        };

        foreach ($targets as $w) {
            $adapter->resizeVariant($absPath, $w, $this->config->resizeAlgorithm());
        }

        $adapter->createModernVariants($absPath, $this->config);
    }

    private function pickAdapter()
    {
        if ($this->imagickAdapter && $this->imagickAdapter->isSupported()) return $this->imagickAdapter;
        if ($this->gdAdapter && $this->gdAdapter->isSupported()) return $this->gdAdapter;
        throw new \RuntimeException('No supported image adapter found (Imagick/GD).');
    }
}
