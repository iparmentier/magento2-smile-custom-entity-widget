<?php
/**
 * Artbambou SmileCustomEntityWidget Module
 *
 * @category   Artbambou
 * @package    Artbambou_SmileCustomEntityWidget
 * @author     Ilan Parmentier
 */
declare(strict_types=1);

namespace Artbambou\SmileCustomEntityWidget\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\Entity;

/**
 * Combination of product conditions
 */
class Combine extends RuleCombine
{
    /**
     * {@inheritdoc}
     */
    protected $elementName = 'parameters';

    /**
     * @var string
     */
    protected $type = 'Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\Combine';

    /**
     * @var \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\EntityFactory
     */
    protected $entityFactory;

    /**
     * @var array
     */
    private $excludedAttributes;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\EntityFactory $entityFactory
     * @param array $data
     * @param array $excludedAttributes
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\EntityFactory $entityFactory,
        array $data = [],
        array $excludedAttributes = []
    ) {
        $this->entityFactory = $entityFactory;
        parent::__construct($context, $data);
        $this->setType($this->type);
        $this->excludedAttributes = $excludedAttributes;
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $entities = $this->entityFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($entities as $code => $label) {
            if (!in_array($code, $this->excludedAttributes)) {
                $attributes[] = [
                    'value' => Entity::class . '|' . $code,
                    'label' => $label,
                ];
            }
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Artbambou\SmileCustomEntityWidget\Model\Rule\Condition\Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Custom Entity Attribute'), 'value' => $attributes]
            ]
        );
        return $conditions;
    }

    /**
     * Collect validated attributes for Smile Custom Entity Collection
     *
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @return $this
     */
    public function collectValidatedAttributes($searchCriteriaBuilder)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($searchCriteriaBuilder);
        }
        return $this;
    }
}