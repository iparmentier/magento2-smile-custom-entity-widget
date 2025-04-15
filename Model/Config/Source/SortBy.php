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

use Magento\Framework\Option\ArrayInterface;
use Smile\ScopedEav\Model\AbstractEntity;

class SortBy implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => AbstractEntity::ATTRIBUTE_SET_ID, 'label' => __('ID')],
            ['value' => AbstractEntity::CREATED_AT, 'label' => __('Created At')],
            ['value' => AbstractEntity::UPDATED_AT, 'label' => __('Updated At')],
            ['value' => AbstractEntity::NAME, 'label' => __('Name')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public static function toArray(): array
    {
        return [
            AbstractEntity::ATTRIBUTE_SET_ID => __('ID'),
            AbstractEntity::CREATED_AT => __('Created At'),
            AbstractEntity::UPDATED_AT => __('Updated At'),
            AbstractEntity::NAME => __('Name')
        ];
    }
}