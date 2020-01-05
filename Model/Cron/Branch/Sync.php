<?php

namespace Gento\Oca\Model\Cron\Branch;

use Gento\Oca\Model\OcaApi;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\Framework\Event\ManagerInterface;

class Sync
{
    /**
     * @var OcaApi
     */
    protected $_ocaApi;

    /**
     * @var CollectionFactory
     */
    protected $operatoryCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        CollectionFactory $operatoryCollectionFactory,
        ManagerInterface $eventManager,
        OcaApi $ocaApi
    ) {
        $this->_ocaApi = $ocaApi;
        $this->operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        /**
         * @var \Gento\Oca\Model\ResourceModel\Operatory\Collection
         */
        $operatoryCollection = $this->operatoryCollectionFactory->create();

        foreach ($operatoryCollection->getUsesIdci() as /** @var \Gento\Oca\Model\Operatory */$operatory) {
            $result = $this->_ocaApi->getBranches($operatory->getCode());

            $this->eventManager->dispatch('gento_oca_get_branch_data', [
                'branchs_data' => $result,
                'operatory' => $operatory,
            ]);
        }
    }
}
