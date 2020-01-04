<?php

namespace Gento\Oca\Model\Cron\Branch;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\Data\BranchInterface;
use Gento\Oca\Api\Data\BranchInterfaceFactory;
use Gento\Oca\Model\OcaApi;
use Gento\Oca\Model\ResourceModel\Operatory\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;

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
     * @var BranchRepositoryInterface
     */
    protected $branchRepository;

    /**
     * Data Object Helper
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Branch factory
     * @var BranchInterfaceFactory
     */
    protected $branchFactory;

    public function __construct(
        CollectionFactory $operatoryCollectionFactory,
        BranchInterfaceFactory $branchFactory,
        BranchRepositoryInterface $branchRepository,
        DataObjectHelper $dataObjectHelper,
        OcaApi $ocaApi
    ) {
        $this->_ocaApi = $ocaApi;
        $this->operatoryCollectionFactory = $operatoryCollectionFactory;
        $this->branchFactory = $branchFactory;
        $this->branchRepository = $branchRepository;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    public function execute()
    {
        /**
         * @var \Gento\Oca\Model\ResourceModel\Operatory\Collection
         */
        $operatoryCollection = $this->operatoryCollectionFactory->create();

        foreach ($operatoryCollection->getUsesIdci() as /** @var \Gento\Oca\Model\Operatory */$operatory) {
            $result = $this->_ocaApi->getBranches($operatory->getCode());

            foreach ($result as $row) {
                try {
                    $branch = $this->branchRepository->getByCode($row['idCentroImposicion']);
                } catch (\Exception $e) {
                    /** @var \Gento\Oca\Model\Branch */
                    $branch = $this->branchFactory->create();
                    $branchData = [
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

                    $this->dataObjectHelper->populateWithArray($branch, $branchData, BranchInterface::class);
                    $this->branchRepository->save($branch);
                }
            }
        }
    }
}
