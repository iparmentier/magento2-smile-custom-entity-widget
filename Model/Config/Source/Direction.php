<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Model\Config\Source;

use Magento\Framework\Api\SortOrder;

class Direction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => SortOrder::SORT_DESC, 'label' => __('Descending')],
            ['value' => SortOrder::SORT_ASC, 'label' => __('Ascending')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public static function toArray(): array
    {
        return [SortOrder::SORT_DESC => __('Descending'), SortOrder::SORT_ASC => __('Ascending')];
    }
}