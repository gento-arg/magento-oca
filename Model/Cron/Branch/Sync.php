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

            $branchsData = array_map(function ($row) {
                return [
                    'code' => $row['idCentroImposicion'],
                    'short_name' => $row['Sigla'],
                    'name' => $row['Descripcion'],
                    'description' => $row['Descripcion'],
                    'address_street' => $row['Calle'],
                    'address_number' => $row['Numero'],
                    'address_floor' => $row['Piso'],
                    'city' => $row['Localidad'],
                    'zipcode' => $row['CodigoPostal'],
                    'active' => true,
                ];
            }, $result);

            $this->eventManager->dispatch('gento_oca_get_branch_data', [
                'branchs_data' => $branchsData,
                'operatory' => $operatory,
            ]);
        }
    }
}
