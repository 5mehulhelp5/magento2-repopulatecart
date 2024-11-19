<?php
/**
 * Action
 *
 * @copyright Copyright © 2022 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);


namespace Blackbird\RepopulateCart\Model\Config\Source;

use Blackbird\RepopulateCart\Model\Config\RepopulateCartConfig;
use Magento\Framework\Data\OptionSourceInterface;

class Action implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            ['value' => RepopulateCartConfig::REPOPULATE_ACTION_REPLACE , 'label' => __('Replace Cart')],
            ['value' => RepopulateCartConfig::REPOPULATE_ACTION_MERGE, 'label' => __('Merge Cart')],
            ['value' => 'nothing', 'label' => __('Do nothing')],
        ];
    }
}
