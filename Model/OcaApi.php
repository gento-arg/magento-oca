<?php

namespace Gento\Oca\Model;

use Gento\Oca\Helper\ArrayToXML;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Oca;
use Zend_Date;

class OcaApi
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ArrayToXML
     */
    protected $arrayToXML;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    protected $_cuit;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ArrayToXML $arrayToXML,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->arrayToXML = $arrayToXML;
        $this->encryptor = $encryptor;

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

    public function requestShipment(DataObject $request)
    {
        $client = new Oca($this->_cuit);
        $metodo = explode('_', $request->getShippingMethod());
        $operativa = $metodo[0];
        $centroImposicion = null;
        if (isset($metodo[1])) {
            $centroImposicion = $metodo[1];
        }

        $centros = $client->getCentroCostoPorOperativa($this->_cuit, $operativa);
        $centroCosto = $centros[0]['NroCentroCosto'];
        $request->setOperativa($operativa);
        $request->setCentroCosto($centroCosto);
        $request->setCentroImposicion($centroImposicion);
        $xmlOr = $this->getXmlOR($request);
        $xmlOr = str_replace(PHP_EOL, '', $xmlOr);
        file_put_contents('ingresoor.xml', $xmlOr);
        $ingresoOR = $client->ingresoORMultiplesRetiros(
            $this->getUsername(),
            $this->getPassword(),
            $xmlOr
        );
    }

    protected function getXmlOR(DataObject $request)
    {
        $fecha = new Zend_Date();
        $paquetes = [];
        foreach ($request->getPackages() as $package) {
            $paquetes[] = [
                '@alto' => $package['params']['height'],
                '@ancho' => $package['params']['width'],
                '@largo' => $package['params']['length'],
                '@peso' => $package['params']['weight'],
                '@valor' => $package['params']['customs_value'],
                '@cantidad' => array_reduce($package['items'], function ($ax, $dx) {
                    return $ax + $dx['qty'];
                }, 0),
            ];
        }
        $paquetes = ['paquete' => $paquetes];

        $xmlData = [
            'cabecera' => [
                '@ver' => '2.0',
                '@nrocuenta' => $this->getAccountNumber(),
            ],
            'origenes' => [
                'origen' => [
                    '@calle' => $request->getShipperAddressStreet1(),
                    '@nro' => $request->getShipperAddressStreet2(),
                    '@piso' => '', // @TODO
                    '@depto' => '', // @TODO
                    '@cp' => $request->getShipperAddressPostalCode(),
                    '@localidad' => $request->getShipperAddressCity(),
                    '@provincia' => $request->getShipperAddressProvince(),
                    '@contacto' => $request->getShipperContactPersonName(),
                    '@email' => $request->getShipperEmail(),
                    '@solicitante' => '',
                    '@observaciones' => '',
                    '@centrocosto' => $request->getCentroCosto(),
                    '@centrocosto' => '',
                    '@idfranjahoraria' => '3', // @TODO
                    '@idcentroimposicionorigen' => 0,
                    '@idcentroimposicionorigen' => '1',
                    '@fecha' => $fecha->toString(Zend_Date::YEAR . Zend_Date::MONTH . Zend_Date::DAY),
                    'envios' => [
                        'envio' => [
                            '@idoperativa' => $request->getOperativa(),
                            '@nroremito' => sprintf('%s-%s',
                                $request->getOrderShipment()->getOrder()->getIncrementId(),
                                $request->getPackageId()
                            ),
                            'destinatario' => [
                                '@apellido' => $request->getRecipientContactPersonLastName(),
                                '@nombre' => $request->getRecipientContactPersonFirstName(),
                                '@calle' => $request->getRecipientAddressStreet1(),
                                '@nro' => $request->getRecipientAddressStreet2(),
                                '@piso' => '', // @TODO
                                '@depto' => '', // @TODO
                                '@localidad' => $request->getRecipientAddressCity(),
                                '@provincia' => $request->getRecipientAddressProvince(),
                                '@cp' => $request->getRecipientAddressPostalCode(),
                                '@telefono' => $request->getRecipientContactPhoneNumber(),
                                '@email' => $request->getRecipientEmail(),
                                '@idci' => $request->getCentroImposicion(),
                                '@celular' => '', // @TODO
                                '@observaciones' => '',
                            ],
                            'paquetes' => $paquetes
                        ]
                    ]
                ]
            ]
        ];

        $xmlData['origenes']['origen']['@calle'] = 'Crespo';
        $xmlData['origenes']['origen']['@nro'] = 1014;
        $xmlData['origenes']['origen']['@provincia'] = 'SANTA FE';
        $xmlData['origenes']['origen']['@contacto'] = 'Jose Fernandez';
        $xmlData['origenes']['origen']['@email'] = 'info@noaflojes.com.ar';
        $xmlData['origenes']['origen']['envios']['envio']['destinatario']['@calle'] = 'Alberdi';
        $xmlData['origenes']['origen']['envios']['envio']['destinatario']['@nro'] = 525;
        $xmlData['origenes']['origen']['envios']['envio']['destinatario']['@provincia'] = 'BUENOS AIRES';
        $xmlData['origenes']['origen']['envios']['envio']['destinatario']['@apellido'] = 'Canepa';
        return $this->arrayToXML->buildXML($xmlData, 'ROWS');
    }

    protected function getAccountNumber()
    {
        return $this->scopeConfig->getValue('carriers/gento_oca/account_number');
    }

    protected function getUsername()
    {
        return $this->scopeConfig->getValue('carriers/gento_oca/username');
    }

    protected function getPassword()
    {
        $password = $this->scopeConfig->getValue('carriers/gento_oca/password');
        return $this->encryptor->decrypt($password);
    }

    public function getOperativas()
    {
        $client = new Oca($this->_cuit);
        return $client->getOperativas(
            $this->getUsername(),
            $this->getPassword()
        );
    }
}
