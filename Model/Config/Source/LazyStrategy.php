<?php
namespace Octocub\PageSpeedOptimizer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LazyStrategy implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'native',  'label' => 'Native loading=lazy'],
            ['value' => 'io',      'label' => 'IntersectionObserver'],
            ['value' => 'io_lqip', 'label' => 'IO + LQIP Placeholder'],
            ['value' => 'idle',    'label' => 'Idle Loading (requestIdleCallback)'],
        ];
    }
}
