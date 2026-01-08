<?php
namespace Octocub\PageSpeedOptimizer\Service\Html;

use Octocub\PageSpeedOptimizer\Model\Config;

class LazyLoad
{
    public function __construct(private Config $config) {}

    public function apply(string $html, string $device): string
    {
        $strategy = $this->resolveStrategy($html, $device);

        return match ($strategy) {
            'native'  => $this->applyNative($html),
            'io'      => $this->applyIo($html, 'octocub_gps/lazy/io'),
            'io_lqip' => $this->applyIoLqip($html, 'octocub_gps/lazy/io_lqip'),
            'idle'    => $this->applyIo($html, 'octocub_gps/lazy/idle'),
            default   => $this->applyNative($html),
        };
    }

    private function resolveStrategy(string $html, string $device): string
    {
        $default = $this->config->lazyDefault();
        $rulesJson = $this->config->lazyRulesJson();

        $rules = json_decode($rulesJson, true);
        if (!is_array($rules)) return $default;

        foreach ($rules as $r) {
            $pattern = (string)($r['pattern'] ?? '');
            $dev = (string)($r['device'] ?? '*');
            $str = (string)($r['strategy'] ?? '');
            if ($pattern === '' || $str === '') continue;
            if ($dev !== '*' && $dev !== $device) continue;

            if (str_contains($html, $pattern)) return $str;
        }

        return $default;
    }

    private function applyNative(string $html): string
    {
        $html = preg_replace_callback('#<img\b[^>]*>#i', function ($m) {
            $tag = $m[0];
            if (stripos($tag, 'loading=') === false) {
                $tag = rtrim(substr($tag, 0, -1)) . ' loading="lazy">';
            }
            if (stripos($tag, 'decoding=') === false) {
                $tag = rtrim(substr($tag, 0, -1)) . ' decoding="async">';
            }
            return $tag;
        }, $html) ?? $html;

        $html = preg_replace_callback('#<iframe\b[^>]*>#i', function ($m) {
            $tag = $m[0];
            if (stripos($tag, 'loading=') === false) {
                $tag = rtrim(substr($tag, 0, -1)) . ' loading="lazy">';
            }
            return $tag;
        }, $html) ?? $html;

        return $html;
    }

    private function applyIo(string $html, string $requireModule): string
    {
        $html = preg_replace_callback('#<img\b([^>]*?)\bsrc=("|')([^"']+)\2([^>]*)>#i', function ($m) {
            $before = $m[1]; $q = $m[2]; $src = $m[3]; $after = $m[4];
            if (stripos($before.$after, 'data-src=') !== false) return $m[0];
            if (stripos($before.$after, 'octocub-gps-skip') !== false) return $m[0];
            $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
            return '<img' . $before
                . ' src=' . $q . $placeholder . $q
                . ' data-src=' . $q . $src . $q
                . ' class="gps-lazy"'
                . $after . '>';
        }, $html) ?? $html;

        if (!str_contains($html, $requireModule)) {
            $init = '<script>require(["' . $requireModule . '"], function(g){g && g.init && g.init();});</script>';
            $html = preg_replace('#</body>#i', $init . "\n</body>", $html, 1) ?? ($html . $init);
        }

        return $html;
    }

    private function applyIoLqip(string $html, string $requireModule): string
    {
        $html = preg_replace_callback('#<img\b([^>]*?)\bsrc=("|')([^"']+)\2([^>]*)>#i', function ($m) {
            $before = $m[1]; $q = $m[2]; $src = $m[3]; $after = $m[4];
            if (stripos($before.$after, 'data-src=') !== false) return $m[0];
            if (stripos($before.$after, 'octocub-gps-skip') !== false) return $m[0];

            $lqip = null;
            if (preg_match('#data-lqip=("|')([^"']+)\1#i', $before.$after, $mm)) {
                $lqip = $mm[2];
            }
            $placeholder = $lqip ?: 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

            return '<img' . $before
                . ' src=' . $q . $placeholder . $q
                . ' data-src=' . $q . $src . $q
                . ' class="gps-lazy gps-lqip"'
                . $after . '>';
        }, $html) ?? $html;

        if (!str_contains($html, $requireModule)) {
            $init = '<script>require(["' . $requireModule . '"], function(g){g && g.init && g.init();});</script>';
            $html = preg_replace('#</body>#i', $init . "\n</body>", $html, 1) ?? ($html . $init);
        }

        return $html;
    }
}
