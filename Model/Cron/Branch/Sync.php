<?php

namespace Gento\Oca\Model\Cron\Branch;

use Gento\Oca\Model\OcaApi;
use Magento\Framework\Event\ManagerInterface;

class Sync
{
    /**
     * @var OcaApi
     */
    protected $_ocaApi;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    public function __construct(
        ManagerInterface $eventManager,
        OcaApi $ocaApi
    ) {
        $this->_ocaApi = $ocaApi;
        $this->eventManager = $eventManager;
    }

    public function execute()
    {
        $result = $this->_ocaApi->getBranches();

        $this->eventManager->dispatch('gento_oca_get_branch_data', [
            'branchs_data' => $result
        ]);
    }
}
