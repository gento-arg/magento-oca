<?php

namespace Gento\Oca\Model\Config\Source;

use Gento\Oca\Api\ConfigInterface;
use Gento\Oca\Model\OcaApi;

class AdmisionBranches extends AbstractSource
{
    /**
     * @param OcaApi $ocaApi
     * @param ConfigInterface $config
     */
    public function __construct(
        readonly private OcaApi $ocaApi,
        readonly private ConfigInterface $config,
    ) {
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     * @throws \Throwable
     */
    public function toArray(): array
    {
        $branches = $this->ocaApi->getAdmisionBranches();
        $branches = $this->config->addDescriptionToBranches($branches);
        $branches = array_reduce($branches, function ($result, $branch) {
            $result[$branch['code']] = $branch['branch_description'];
            return $result;
        }, []);
        asort($branches);
        return $branches;
    }
}
