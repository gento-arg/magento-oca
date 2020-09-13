<?php

namespace Gento\Oca\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

class ProductAttribute implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return array_map(function ($key, $label) {
            return ['value' => $key, 'label' => $label];
        }, array_keys($this->toArray()), $this->toArray());
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = [
            '' => __('-- No selected --'),
        ];

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('frontend_input', 'text')
            ->addFilter('is_user_defined', 1)
            ->create();
        $attributeRepository = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );

        foreach ($attributeRepository->getItems() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        }
        return $attributes;
    }
}
