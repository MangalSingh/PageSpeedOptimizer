<?php
namespace Octocub\PageSpeedOptimizer\Service\Html;

use Octocub\PageSpeedOptimizer\Model\Config;

class Optimizer
{
    public function __construct(
        private Config $config,
        private Preload $preload,
        private LazyLoad $lazyLoad
    ) {}

    /** @return array{0:string,1:string[]} */
    public function optimize(string $html, string $device): array
    {
        [$html, $linkHeaders] = $this->preload->apply($html, $device);
        $html = $this->lazyLoad->apply($html, $device);

        if ($this->config->inlineCssMinify()) {
            $html = preg_replace_callback('#<style\b[^>]*>(.*?)</style>#is', function ($m) {
                $css = $m[1];
                $css = preg_replace('!/\*.*?\*/!s', '', $css);
                $css = preg_replace('/\s+/', ' ', $css);
                $css = str_replace([' {', '{ '], '{', $css);
                $css = str_replace([' }', '} '], '}', $css);
                $css = str_replace([' ;', '; '], ';', $css);
                return str_replace($m[1], trim($css), $m[0]);
            }, $html) ?? $html;
        }

        if ($this->config->htmlMinify()) {
            $html = preg_replace('/<!--(?!\[if).*?-->/s', '', $html) ?? $html;
            $html = preg_replace('/>\s+</', '><', $html) ?? $html;
            $html = trim($html);
        }

        return [$html, $linkHeaders];
    }
}
