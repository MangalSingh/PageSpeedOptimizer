<?php
namespace Octocub\PageSpeedOptimizer\Plugin\MediaStorage;

use Magento\MediaStorage\Model\File\Uploader;
use Octocub\PageSpeedOptimizer\Model\Config;
use Octocub\PageSpeedOptimizer\Model\Image\Queue;

class EnqueueUploadedImagePlugin
{
    public function __construct(private Config $config, private Queue $queue) {}

    public function afterSave(Uploader $subject, $result)
    {
        if (!$this->config->enabled() || !$this->config->autoOptimizeUploads()) {
            return $result;
        }

        if (is_array($result) && !empty($result['path']) && !empty($result['file'])) {
            $abs = rtrim($result['path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($result['file'], DIRECTORY_SEPARATOR);
            $this->queue->enqueue($abs, 'all');
        }

        return $result;
    }
}
