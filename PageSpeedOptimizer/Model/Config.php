<?php
namespace Octocub\PageSpeedOptimizer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    public function __construct(private ScopeConfigInterface $scopeConfig) {}

    private function get(string $path, $storeId = null): string
    {
        return (string)$this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function enabled(): bool { return $this->get('octocub_gps/general/enabled') === '1'; }
    public function enableUserAgents(): bool { return $this->get('octocub_gps/general/enable_user_agents') === '1'; }
    public function enableLinkHeaders(): bool { return $this->get('octocub_gps/general/enable_link_headers') === '1'; }

    public function htmlMinify(): bool { return $this->get('octocub_gps/general/html_minify') === '1'; }
    public function inlineCssMinify(): bool { return $this->get('octocub_gps/general/inline_css_minify') === '1'; }

    public function lazyDefault(): string { return $this->get('octocub_gps/lazyload/default_strategy'); }
    public function lazyRulesJson(): string { return $this->get('octocub_gps/lazyload/page_rules_json'); }

    public function preloadDesktop(): array { return $this->lines('octocub_gps/preload/desktop_images'); }
    public function preloadMobile(): array { return $this->lines('octocub_gps/preload/mobile_images'); }
    public function preloadTablet(): array { return $this->lines('octocub_gps/preload/tablet_images'); }
    public function fontPreloads(): array { return $this->lines('octocub_gps/preload/font_preloads'); }

    private function lines(string $path): array
    {
        $raw = $this->get($path);
        $rows = array_filter(array_map('trim', preg_split('/\R+/', $raw)));
        return array_values(array_unique($rows));
    }

    public function autoOptimizeUploads(): bool { return $this->get('octocub_gps/image/auto_optimize_uploads') === '1'; }
    public function smartOptimizeViewedPages(): bool { return $this->get('octocub_gps/image/smart_optimize_viewed_pages') === '1'; }
    public function enableWebp(): bool { return $this->get('octocub_gps/image/enable_webp') === '1'; }
    public function enableAvif(): bool { return $this->get('octocub_gps/image/enable_avif') === '1'; }
    public function jpegQuality(): int { return max(1, min(100, (int)$this->get('octocub_gps/image/quality_jpeg'))); }
    public function webpQuality(): int { return max(1, min(100, (int)$this->get('octocub_gps/image/quality_webp'))); }
    public function avifQuality(): int { return max(1, min(63, (int)$this->get('octocub_gps/image/quality_avif'))); }
    public function resizeAlgorithm(): string { return $this->get('octocub_gps/image/resize_algorithm'); }
}
