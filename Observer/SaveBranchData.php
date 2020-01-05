<?php

namespace Gento\Oca\Observer;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\Data\BranchInterface;
use Gento\Oca\Api\Data\BranchInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveBranchData implements ObserverInterface
{
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
        BranchInterfaceFactory $branchFactory,
        BranchRepositoryInterface $branchRepository,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->branchFactory = $branchFactory;
        $this->branchRepository = $branchRepository;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    public function execute(EventObserver $observer)
    {
        $branchsData = $observer->getBranchsData();
        // $operatory = $observer->getOperatory();

        foreach ($branchsData as $branchData) {
            try {
                $branch = $this->branchRepository
                    ->getByCode($branchData['code']);
            } catch (NoSuchEntityException $e) {
                /** @var \Gento\Oca\Model\Branch */
                $branch = $this->branchFactory->create();

                try {
                    $this->dataObjectHelper->populateWithArray($branch, $branchData, BranchInterface::class);
                    $this->branchRepository->save($branch);
                } catch (CouldNotSaveException $nse) {
                    // Duplicate branch code
                }
            }
        }
        return $this;
    }
}
