<?php

namespace Gento\Oca\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Oca;

class OcaApi
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $_cuit;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_cuit = $scopeConfig->getValue('tax/defaults/cuit');
    }

    public function getBranches($operatoryCode)
    {
        $client = new Oca($this->_cuit, $operatoryCode);
        $centros = $client->getCentrosImposicion();
        array_walk($centros, function ($item, $key) use (&$centros) {
            foreach ($item as $k => $v) {
                $item[$k] = trim($v);
            }
            $centros[$key] = $item;
        });
        return $centros;
    }
}
