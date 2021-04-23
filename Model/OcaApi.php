<?php

namespace Gento\Oca\Model;

use DOMDocument;
use DOMXPath;
use Gento\Oca\Helper\ArrayToXML;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Oca;
use Zend_Date;

class OcaApi
{
    const XML_PATH_SERVICE_URL = 'carriers/gento_oca/elocker_service_url';
    const XML_PATH_CUIT = 'carriers/gento_oca/cuit';
    const WS_CENTROS_IMPOSICION = 'GetCentrosImposicion';
    const WS_CENTROS_IMPOSICION_CP = 'GetCentrosImposicionPorCP';
    const WS_TARIFAR_ENVIO_CORPORATIVO = 'Tarifar_Envio_Corporativo';
    const WS_TRACKING_PIEZA = 'Tracking_Pieza';
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
    /**
     * @var CurlFactory
     */
    protected $curlFactory;
    /**
     * @var string
     */
    protected $_cuit;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ArrayToXML $arrayToXML,
        EncryptorInterface $encryptor,
        CurlFactory $curlFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->arrayToXML = $arrayToXML;
        $this->encryptor = $encryptor;
        $this->curlFactory = $curlFactory;

        $this->_cuit = $this->getFixedCuit($scopeConfig->getValue(self::XML_PATH_CUIT));
        $this->_serviceUrl = trim($scopeConfig->getValue(self::XML_PATH_SERVICE_URL));
        if ($this->_serviceUrl !== '' && !preg_match('~/$~', $this->_serviceUrl)) {
            $this->_serviceUrl .= '/';
        }
    }

    /**
     * @return array[]
     */
    public function getBranches()
    {
        $data = $this->callPost(self::WS_CENTROS_IMPOSICION);
        $centros = $this->loadDataset($data, [
            'idCentroImposicion', 'Sigla', 'Descripcion',
            'Calle', 'Numero', 'Piso', 'Localidad', 'CodigoPostal',
        ]);

        return $this->processBranches($centros);
    }

    /**
     * @return array[]
     */
    public function getBranchesZipCode($zipcode)
    {
        if (!$zipcode) {
            return [];
        }
        $data = $this->callPost(self::WS_CENTROS_IMPOSICION_CP, [
            'CodigoPostal' => $zipcode
        ]);
        $centros = $this->loadDataset($data, [
            'idCentroImposicion', 'IdSucursalOCA', 'Sigla', 'Descripcion',
            'Calle', 'Numero', 'Torre', 'Piso', 'Depto', 'Localidad',
            'IdProvincia', 'idCodigoPostal', 'Telefono', 'eMail',
            'Provincia', 'CodigoPostal',
        ]);

        return $this->processBranches($centros);
    }

    /**
     * @param $operatoryCode
     * @param $weight
     * @param $volume
     * @param $senderZipcode
     * @param $receiverZipcode
     * @param $packageQty
     * @param $packageValue
     * @return object
     */
    public function getQuote(
        $operatoryCode,
        $weight,
        $volume,
        $senderZipcode,
        $receiverZipcode,
        $packageQty,
        $packageValue
    ) {
        $data = $this->callPost(self::WS_TARIFAR_ENVIO_CORPORATIVO, [
            'PesoTotal' => floatval($weight),
            'VolumenTotal' => $volume,
            'CodigoPostalOrigen' => $senderZipcode,
            'CodigoPostalDestino' => $receiverZipcode,
            'CantidadPaquetes' => $packageQty,
            'ValorDeclarado' => $packageValue,
            'Cuit' => $this->_cuit,
            'Operativa' => $operatoryCode,
        ]);

        $dataSet = $this->loadDataset($data, [
            'Tarifador',
            'Precio',
            'Ambito',
            'PlazoEntrega',
            'Adicional',
            'Total',
        ]);

        if (count($dataSet) <= 0)
            return null;

        return (object)array_shift($dataSet);
    }

    public function getTracking($trackingCode)
    {
        $data = $this->callPost(self::WS_TRACKING_PIEZA, [
            'Pieza' => $trackingCode,
            'NroDocumentoCliente' => '',
            'CUIT' => $this->_cuit,
        ]);

        return $this->loadDataset($data, [
            'NumeroEnvio',
            'Motivo' => 'Descripcion_Motivo',
            'Estado' => 'Desdcripcion_Estado',
            'Sucursal' => 'SUC',
            'Fecha' => 'fecha',
        ]);
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

    public function getOperativas()
    {
        $client = new Oca($this->_cuit);
        return $client->getOperativas(
            $this->getUsername(),
            $this->getPassword()
        );
    }


    protected function getFixedCuit($cuit)
    {
        if (!$cuit || strlen($cuit) != 11 || preg_match("/[^0-9]/", $cuit)) {
            return false;
        }

        return substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10);
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

    protected function loadDataset($xmlObject, $fields)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlObject, ~LIBXML_DTDVALID);
        $xpath = new DOMXpath($dom);

        $data = [];
        foreach ($xpath->query("//NewDataSet/Table") as $ci) {
            $data[] = $this->loadFields($ci, $fields);
        }
        return $data;
    }

    protected function loadFields($ci, $fields)
    {
        $return = [];
        $map = [];

        array_walk($fields, function ($value, $key) use (&$map) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $map[$key] = $value;
        });

        foreach ($map as $alias => $field) {
            $value = null;

            $item = $ci->getElementsByTagName($field)->item(0);
            if ($item != null) {
                $value = $item->nodeValue;
            }

            $return[$alias] = $value;
        }

        return $return;
    }

    protected function getCurlClient()
    {
        return $this->curlFactory->create();
    }

    protected function getServiceUrl($service)
    {
        return sprintf('%s%s',
            $this->_serviceUrl,
            $service
        );
    }

    protected function callPost($service, $data = [])
    {
        $curlClient = $this->getCurlClient();
        $url = $this->getServiceUrl($service);
        $curlClient->post($url, $data);
        return $curlClient->getBody();
    }
}
