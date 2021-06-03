<?php

namespace Gento\Oca\Model\Config\Source;

use Gento\Oca\Helper\Data;
use Gento\Oca\Model\OcaApi;

class AdmisionBranches extends AbstractSource
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
     * Branches constructor.
     * @param OcaApi $ocaApi
     * @param Data $helper
     */
    public function __construct(
        OcaApi $ocaApi,
        Data $helper
    ) {
        $this->ocaApi = $ocaApi;
        $this->helper = $helper;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $branches = $this->ocaApi->getAdmisionBranches();
        $branches = $this->helper->addDescriptionToBranches($branches);
        $branches = array_reduce($branches, function ($result, $branch) {
            $result[$branch['code']] = $branch['branch_description'];
            return $result;
        }, []);
        asort($branches);
        return $branches;
    }
}
