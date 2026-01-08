<?php
namespace Octocub\PageSpeedOptimizer\Service\Html;

use Octocub\PageSpeedOptimizer\Model\Config;

class Preload
{
    public function __construct(private Config $config) {}

    /** @return array{0:string,1:string[]} */
    public function apply(string $html, string $device): array
    {
        $images = match ($device) {
            'mobile' => $this->config->preloadMobile(),
            'tablet' => $this->config->preloadTablet(),
            default  => $this->config->preloadDesktop(),
        };

        $fonts = $this->config->fontPreloads();

        $tags = [];
        $headers = [];

        foreach ($images as $url) {
            $tags[] = '<link rel="preload" as="image" href="' . htmlspecialchars($url, ENT_QUOTES) . '" fetchpriority="high">';
            if ($this->config->enableLinkHeaders()) {
                $headers[] = '<' . $url . '>; rel=preload; as=image; fetchpriority=high';
            }
        }

        foreach ($fonts as $url) {
            $tags[] = '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . htmlspecialchars($url, ENT_QUOTES) . '">';
            if ($this->config->enableLinkHeaders()) {
                $headers[] = '<' . $url . '>; rel=preload; as=font; type="font/woff2"; crossorigin';
            }
        }

        if (!$tags) return [$html, []];

        $injection = "\n" . implode("\n", $tags) . "\n";
        $html2 = preg_replace('#</head>#i', $injection . '</head>', $html, 1) ?? $html;

        return [$html2, $headers];
    }
}
