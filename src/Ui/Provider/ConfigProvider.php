<?php

namespace Gento\Oca\Ui\Provider;

use Gento\Oca\Api\Data\OperatoryInterface;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var CollectionFactory
     */
    private $operatoryCollectionFactory;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ConfigProvider constructor.
     * @param CollectionFactory $operatoryCollectionFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        CollectionFactory $operatoryCollectionFactory,
        UrlInterface $urlBuilder
    ) {
        $this->operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        $withBranches = [];
        $operatory = $this->operatoryCollectionFactory->create();
        foreach ($operatory->getDeliveryToBranch()->getActiveList() as /** @var OperatoryInterface */ $operatory) {
            $withBranches[] = $operatory->getCode();
        }

        return [
            'oca' => [
                'useBranches' => $withBranches,
                'branchesUrl' => $this->urlBuilder->getUrl('oca/ajax/branches'),
            ]
        ];
    }
}
