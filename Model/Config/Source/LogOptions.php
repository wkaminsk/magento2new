<?php
declare(strict_types=1);

namespace Riskified\Decider\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LogOptions implements OptionSourceInterface
{
    public const NONE_LOG_OPTION = 0;
    public const STANDARD_LOG_OPTION = 1;
    public const RICHFULL_LOG_OPTION = 2;

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => static::NONE_LOG_OPTION, 'label' => __('None')],
            ['value' => static::STANDARD_LOG_OPTION, 'label' => __('Standard')],
            ['value' => static::RICHFULL_LOG_OPTION, 'label' => __('Full Log')]
        ];
    }
}
