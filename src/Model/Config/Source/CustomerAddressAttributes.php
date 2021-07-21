<?php

namespace Gento\Oca\Model\Config\Source;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomerAddressAttributes extends AbstractSource
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        ScopeConfigInterface $scopeConfig,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->scopeConfig = $scopeConfig;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheridoc
     */
    public function toArray()
    {
        $attributes = [
            '' => __('-- No selected --'),
        ];
        $lineQtys = $this->scopeConfig->getValue('customer/address/street_lines');
        for ($i = 1; $i <= $lineQtys; $i++) {
            $attributes['__street_line_' . $i] = __('< Street Line %1 >', $i);
        }

        $sortOrder = $this->sortOrderBuilder
            ->setField('frontend_label')
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addSortOrder($sortOrder)
            ->create();

        $attributeRepository = $this->attributeRepository->getList(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            $searchCriteria
        );

        foreach ($attributeRepository->getItems() as $attribute) {
            $attributes[$attribute->getAttributeCode()] = sprintf('%s (%s)',
                $attribute->getFrontendLabel(),
                $attribute->getAttributeCode()
            );
        }
        return $attributes;
    }
}
