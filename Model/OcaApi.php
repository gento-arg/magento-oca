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
        $this->_cuit = $this->getFixedCuit($scopeConfig->getValue('tax/defaults/cuit'));
    }

    protected function getFixedCuit($cuit)
    {
        if (!$cuit || strlen($cuit) != 11 || preg_match("/[^0-9]/", $cuit)) {
            return false;
        }

        return substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10);
    }

    /**
     * @return array[]
     */
    public function getBranches($operatoryCode)
    {
        $client = new Oca($this->_cuit, $operatoryCode);
        $centros = $client->getCentrosImposicion();

        return $this->processBranches($centros);
    }

    /**
     * @return array[]
     */
    public function getBranchesZipCode($operatoryCode, $zipcode)
    {
        $client = new Oca($this->_cuit, $operatoryCode);
        $centros = $client->getCentrosImposicionPorCP($zipcode);

        return $this->processBranches($centros);
    }

    public function getQuote(
        $operatoryCode,
        $weight,
        $volume,
        $senderZipcode,
        $receiverZipcode,
        $packageQty,
        $packageValue
    ) {
        $client = new Oca($this->_cuit, $operatoryCode);
        return $client->tarifarEnvioCorporativo(
            floatval($weight),
            $volume,
            $senderZipcode,
            $receiverZipcode,
            $packageQty,
            $packageValue
        );
    }

    public function getTracking($trackingCode)
    {
        $client = new Oca($this->_cuit);
        $tracking = $client->trackingPieza($trackingCode);
        return $tracking;
    }

    protected function processBranches($centros)
    {
        array_walk($centros, function ($item, $key) use (&$centros) {
            foreach ($item as $k => $v) {
                $item[$k] = trim($v);
            }
            $centros[$key] = $item;
        });

        return array_map(function ($row) {
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
        }, $centros);
    }
}
