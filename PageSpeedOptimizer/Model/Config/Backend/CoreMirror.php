<?php
namespace Octocub\PageSpeedOptimizer\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\Storage\WriterInterface;

class CoreMirror extends Value
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        private WriterInterface $writer,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $cacheFrontendPool, $data);
    }

    public function afterSave()
    {
        $value = (string)$this->getValue();
        $field = (string)$this->getField();

        $map = [
            'enable_js_bundling' => 'dev/js/enable_js_bundling',
            'minify_js'          => 'dev/js/minify_files',
            'merge_js'           => 'dev/js/merge_files',
            'minify_css'         => 'dev/css/minify_files',
            'merge_css'          => 'dev/css/merge_css_files',
        ];

        if (isset($map[$field])) {
            $this->writer->save($map[$field], $value, $this->getScope(), $this->getScopeId());
        }

        return parent::afterSave();
    }
}
