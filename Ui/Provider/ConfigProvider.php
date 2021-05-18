<?php

namespace Gento\Oca\Ui\Provider;

use Gento\Oca\Api\Data\OperatoryInterface;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\UrlInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * ConfigProvider constructor.
     * @param CollectionFactory $operatoryCollectionFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        CollectionFactory $operatoryCollectionFactory,
        UrlInterface $urlBuilder
    ) {
        $this->_operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function getConfig()
    {
        $withBranches = [];
        $operatory = $this->_operatoryCollectionFactory->create();
        foreach ($operatory->getUsesIdci()->getActiveList() as /** @var OperatoryInterface */ $operatory) {
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
