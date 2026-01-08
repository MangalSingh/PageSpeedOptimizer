<?php
namespace Octocub\PageSpeedOptimizer\Model;

use Magento\Framework\App\RequestInterface;

class DeviceDetector
{
    public function __construct(private RequestInterface $request, private Config $config) {}

    public function deviceGroup(): string
    {
        if ($this->request->getParam('amp') == 1) {
            return 'amp';
        }

        if (!$this->config->enableUserAgents()) {
            return 'all';
        }

        $ua = (string)$this->request->getHeader('User-Agent');
        $uaL = strtolower($ua);

        if (str_contains($uaL, 'ipad') || str_contains($uaL, 'tablet')) return 'tablet';
        if (preg_match('/mobi|android|iphone|ipod|blackberry|windows phone/i', $ua)) return 'mobile';
        return 'desktop';
    }
}
