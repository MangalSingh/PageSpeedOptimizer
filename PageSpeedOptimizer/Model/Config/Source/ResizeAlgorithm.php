<?php
namespace Octocub\PageSpeedOptimizer\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ResizeAlgorithm implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'lanczos', 'label' => 'Lanczos (high quality)'],
            ['value' => 'mitchell','label' => 'Mitchell (balanced)'],
        ];
    }
}
