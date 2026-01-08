<?php
namespace Octocub\PageSpeedOptimizer\Plugin\FrontController;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\Response\Http as HttpResponse;
use Octocub\PageSpeedOptimizer\Model\Config;
use Octocub\PageSpeedOptimizer\Model\DeviceDetector;
use Octocub\PageSpeedOptimizer\Service\Html\Optimizer as HtmlOptimizer;

class OptimizeHtmlPlugin
{
    public function __construct(
        private Config $config,
        private DeviceDetector $deviceDetector,
        private HtmlOptimizer $htmlOptimizer
    ) {}

    public function afterDispatch(FrontController $subject, $result)
    {
        if (!$this->config->enabled()) return $result;

        if (!($result instanceof HttpResponse)) return $result;

        $ctHeader = $result->getHeader('Content-Type');
        $ct = $ctHeader ? (string)$ctHeader->getFieldValue() : '';
        if ($ct && !str_contains(strtolower($ct), 'text/html')) return $result;

        $body = (string)$result->getBody();
        if ($body === '' || str_contains($body, '<!-- octocub_gps:skip -->')) return $result;

        $device = $this->deviceDetector->deviceGroup();
        [$newBody, $headers] = $this->htmlOptimizer->optimize($body, $device);

        $result->setBody($newBody);

        foreach ($headers as $headerValue) {
            $result->setHeader('Link', $headerValue, false);
        }

        return $result;
    }
}
