<?php

declare(strict_types = 1);

namespace Gento\Oca\Service;

use Gento\Oca\Api\ConfigInterface;
use Gento\Oca\Model\OcaApi;
use Magento\Framework\Event\ManagerInterface;
use Throwable;

class Branch
{
    /**
     * @param OcaApi $ocaApi
     * @param ManagerInterface $eventManager
     * @param ConfigInterface $config
     */
    public function __construct(
        readonly private OcaApi $ocaApi,
        readonly private ManagerInterface $eventManager,
        readonly private ConfigInterface $config,
    ) {
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
        $branches = $this->config->addDescriptionToBranches($branches);

        if ($this->config->getBranchAutoPopulate()) {
            $this->eventManager->dispatch('gento_oca_get_branch_data', [
                'branchs_data' => $branches
            ]);
        }

        return $branches;
    }
}
