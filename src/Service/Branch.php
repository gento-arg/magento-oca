<?php

namespace Gento\Oca\Service;

use Gento\Oca\Helper\Data;
use Gento\Oca\Model\OcaApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Throwable;

class Branch
{
    /**
     * @var OcaApi
     */
    private $ocaApi;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param OcaApi $ocaApi
     * @param Data $helper
     * @param ManagerInterface $eventManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        OcaApi $ocaApi,
        Data $helper,
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->ocaApi = $ocaApi;
        $this->helper = $helper;
        $this->eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $zipCode
     *
     * @return array[]
     * @throws Throwable
     */
    public function getBranches($zipCode)
    {
        $branches = $this->ocaApi->getDeliveryBranchesZipCode($zipCode);
        $branches = $this->helper->addDescriptionToBranches($branches);

        if ($this->getBranchAutoPopulate()) {
            $this->eventManager->dispatch('gento_oca_get_branch_data', [
                'branchs_data' => $branches
            ]);
        }

        return $branches;
    }

    /**
     * @return mixed
     */
    protected function getBranchAutoPopulate()
    {
        return $this->scopeConfig->getValue('carriers/gento_oca/branch_autopopulate');
    }
}
