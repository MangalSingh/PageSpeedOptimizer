<?php
namespace Octocub\PageSpeedOptimizer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Octocub\PageSpeedOptimizer\Model\Config;
use Octocub\PageSpeedOptimizer\Model\DeviceDetector;
use Octocub\PageSpeedOptimizer\Model\Image\Queue;

class EnqueueViewedImages implements ObserverInterface
{
    public function __construct(
        private Config $config,
        private DeviceDetector $deviceDetector,
        private Queue $queue,
        private StoreManagerInterface $storeManager,
        private DirectoryList $dirList
    ) {}

    public function execute(Observer $observer)
    {
        if (!$this->config->enabled() || !$this->config->smartOptimizeViewedPages()) return;

        $action = $observer->getData('controller_action');
        if (!$action) return;

        $response = $action->getResponse();
        if (!($response instanceof HttpResponse)) return;

        $ctHeader = $response->getHeader('Content-Type');
        $ct = $ctHeader ? (string)$ctHeader->getFieldValue() : '';
        if ($ct && !str_contains(strtolower($ct), 'text/html')) return;

        $html = (string)$response->getBody();
        if ($html === '') return;

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $mediaDir = $this->dirList->getPath(DirectoryList::PUB) . '/media/';

        preg_match_all('#<img\b[^>]*\bsrc=("|\')([^"\']+)\1#i', $html, $m);
        $srcs = array_unique($m[2] ?? []);
        $device = $this->deviceDetector->deviceGroup();

        foreach ($srcs as $src) {
            if (!str_starts_with($src, $mediaUrl)) continue;
            $rel = ltrim(str_replace($mediaUrl, '', $src), '/');
            $abs = $mediaDir . $rel;
            $this->queue->enqueue($abs, $device);
        }
    }
}
