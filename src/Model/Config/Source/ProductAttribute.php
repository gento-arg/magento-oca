<?php

namespace Gento\Oca\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductAttribute extends AbstractSource
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
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
     * @inheridoc
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
