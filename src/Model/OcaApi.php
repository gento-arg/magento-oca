<?php

namespace Gento\Oca\Model;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use Gento\Oca\Api\Data\HistoryInterfaceFactory;
use Gento\Oca\Api\HistoryRepositoryInterface;
use Gento\Oca\Helper\ArrayToXML;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json as JsonHelper;
use Throwable;
use Zend_Date;

class OcaApi
{
    const SERVICIO_ADMISION = 1;
    const SERVICIO_ENTREGA = 2;
    const WS_CENTROS_IMPOSICION = 'GetCentrosImposicion';
    const WS_CENTROS_IMPOSICION_SERVICIOS = 'GetCentrosImposicionConServicios';
    const WS_CENTROS_IMPOSICION_SERVICIOS_CP = 'GetCentrosImposicionConServiciosByCP';
    const WS_COST_CENTER_BY_OP = 'GetCentroCostoPorOperativa';
    const WS_ETIQUETA_PDF_ORDENRETIRO = 'GetPdfDeEtiquetasPorOrdenOrNumeroEnvio';
    const WS_MULTI_INGRESO_OR = 'IngresoORMultiplesRetiros';
    const WS_OPERATIVES_BY_USR = 'GetOperativasByUsuario';
    const WS_TARIFAR_ENVIO_CORPORATIVO = 'Tarifar_Envio_Corporativo';
    const WS_TRACKING_PIEZA = 'Tracking_Pieza';
    const XML_PATH_CUIT = 'carriers/gento_oca/cuit';
    const XML_PATH_EPAK_SERVICE_URL = 'carriers/gento_oca/elocker_service_url';
    const XML_PATH_SERVICE_URL = 'carriers/gento_oca/service_url';
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
     * @var HistoryInterfaceFactory
     */
    protected $historyFactory;
    /**
     * @var HistoryRepositoryInterface
     */
    protected $historyRepository;
    /**
     * @var string
     */
    protected $_cuit;

    /**
     * OcaApi constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ArrayToXML $arrayToXML
     * @param EncryptorInterface $encryptor
     * @param CurlFactory $curlFactory
     * @param HistoryInterfaceFactory $historyFactory
     * @param HistoryRepositoryInterface $historyRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ArrayToXML $arrayToXML,
        EncryptorInterface $encryptor,
        CurlFactory $curlFactory,
        HistoryInterfaceFactory $historyFactory,
        HistoryRepositoryInterface $historyRepository,
        JsonHelper $jsonHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->arrayToXML = $arrayToXML;
        $this->encryptor = $encryptor;
        $this->curlFactory = $curlFactory;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->jsonHelper = $jsonHelper;

        $this->_cuit = $this->getFixedCuit($scopeConfig->getValue(self::XML_PATH_CUIT));
        $this->_serviceEpakUrl = trim($scopeConfig->getValue(self::XML_PATH_EPAK_SERVICE_URL));
        if ($this->_serviceEpakUrl !== '' && !preg_match('~/$~', $this->_serviceEpakUrl)) {
            $this->_serviceEpakUrl .= '/';
        }
        $this->_serviceUrl = trim($scopeConfig->getValue(self::XML_PATH_SERVICE_URL));
        if ($this->_serviceUrl !== '' && !preg_match('~/$~', $this->_serviceUrl)) {
            $this->_serviceUrl .= '/';
        }
    }

    /**
     * @return array[]
     * @throws Throwable
     */
    public function getBranches()
    {
        $data = $this->callPost(self::WS_CENTROS_IMPOSICION);
        $centros = $this->loadDataset($data, [
            'idCentroImposicion',
            'Sigla',
            'Descripcion',
            'Calle',
            'Numero',
            'Piso',
            'Localidad',
            'CodigoPostal',
        ]);

        return $this->processBranches($centros);
    }

    /**
     * @return array|array[]
     */
    public function getBranchesWithService()
    {
        $data = $this->callPost(self::WS_CENTROS_IMPOSICION_SERVICIOS);
        $centros = $this->loadDataset($data, [
            'idCentroImposicion' => 'IdCentroImposicion',
            'Sigla',
            'Sucursal',
            'Calle',
            'Numero',
            'Torre',
            'Piso',
            'Depto',
            'Localidad',
            'CodigoPostal',
            'Provincia',
            'Telefono',
            'Latitud',
            'Longitud',
            'IdSucursalOCA' => 'SucursalOCA',
            'Servicios'
        ], '//CentrosDeImposicion/Centro');

        return $this->processBranches($centros);
    }

    /**
     * @param $zipcode
     * @return array|array[]
     * @throws Throwable
     */
    public function getBranchesWithServiceZipCode($zipcode)
    {
        if (!$zipcode) {
            return [];
        }
        $data = $this->callPost(self::WS_CENTROS_IMPOSICION_SERVICIOS_CP, [
            'CodigoPostal' => $zipcode
        ]);
        $centros = $this->loadDataset($data, [
            'idCentroImposicion' => 'IdCentroImposicion',
            'Sigla',
            'Sucursal',
            'Calle',
            'Numero',
            'Torre',
            'Piso',
            'Depto',
            'Localidad',
            'CodigoPostal',
            'Provincia',
            'Telefono',
            'Latitud',
            'Longitud',
            'IdSucursalOCA' => 'SucursalOCA',
            'Servicios'
        ], '//CentrosDeImposicion/Centro');

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
     * @throws Throwable
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

        if (count($dataSet) <= 0) {
            return null;
        }

        return (object)array_shift($dataSet);
    }

    /**
     * @param $trackingCode
     * @return array
     */
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

    /**
     * @param DataObject $request
     */
    public function requestShipment(DataObject $request)
    {
        //$operativa = $request->getOperativa();
        //$centros = $this->getCostCenterByOperative($this->_cuit, $operativa);
        //$centroCosto = $centros[0]['NroCentroCosto'];
        //
        //$request->setCentroCosto($centroCosto);
        $request->setCentroCosto('');

        $xmlOr = $this->getXmlOR($request);
        $xmlOr = mb_convert_encoding($xmlOr, 'ISO-8859-1', 'UTF-8');
        return $this->getIngresoORMultiple(
            $this->getUsername(),
            $this->getPassword(),
            $xmlOr
        );
    }

    /**
     * @return array
     */
    public function getOperatives()
    {
        $data = $this->callPost(self::WS_OPERATIVES_BY_USR, [
            'usr' => $this->getUsername(),
            'psw' => $this->getPassword(),
        ]);

        return $this->loadDataset($data, [
            'IdOperativa',
            'Descripcion',
            'ConVolumen',
            'ConValorDeclarado',
            'ASucursal',
            'OrigenSucursal',
        ]);
    }

    /**
     * @param $cuit
     * @param $operative
     * @return array
     */
    public function getCostCenterByOperative($cuit, $operative)
    {
        $data = $this->callPost(self::WS_COST_CENTER_BY_OP, [
            'CUIT' => $cuit,
            'Operativa' => $operative,
        ], false);

        return $this->loadDataset($data, [
            'NroCentroCosto',
            'Solicitante',
            'CalleRetiro',
            'NumeroRetiro',
            'PisoRetiro',
            'DeptoRetiro',
            'LocalidadRetiro',
            'CodigoPostal',
            'TelContactoRetiro',
            'EmaiContactolRetiro',
            'ContactoRetiro',
        ]);
    }

    public function getIngresoORMultiple($user, $password, $xml, $confirm = true)
    {
        $data = $this->callPost(self::WS_MULTI_INGRESO_OR, [
            'usr' => $user,
            'psw' => $password,
            'xml_Datos' => $xml,
            'ConfirmarRetiro' => $confirm ? 'true' : 'false',
            'ArchivoCliente' => '',
            'ArchivoProceso' => '',
        ]);

        $childs = $this->loadPaths($data, [
            'resumen' => '//Resultado/Resumen',
            'ingresos' => '//Resultado/DetalleIngresos',
            'rechazos' => '//Resultado/DetalleRechazos',
        ]);

        if (isset($childs['rechazos'])) {
            // TODO ¿Pueden ser rechazados algunos y admitidos otros?
            $rechazo = $this->loadFields($childs['rechazos'][0], [
                'Operativa',
                'Remito',
                'Motivo',
                'Cantidad'
            ]);

            // TODO Con datos de ejemplo, por mas que venga rechazado por este motivo trae datos de rastreo.
            // TODO Determinar si es correcto no arrojar error cuando operativa es 0
            if ($rechazo['Operativa'] != 0) {
                throw new LocalizedException(__('OCA refuse the request with reason "%1"', $rechazo['Motivo']));
            }
        }

        $ingresos = [];
        foreach ($childs['ingresos'] as $resumen) {
            $ingresos[] = $this->loadFields($resumen, [
                'Operativa',
                'OrdenRetiro',
                'NumeroEnvio',
                'Remito',
                'Estado',
                'SucursalDestino' => 'sucursalDestino'
            ]);
        }

        $resumeData = [];
        foreach ($childs['resumen'] as $resumen) {
            $resumeData[] = $this->loadFields($resumen, [
                'CodigoOperacion',
                'FechaIngreso',
                'mailUsuario',
                'origen',
                'CantidadRegistros',
                'CantidadIngresados',
                'CantidadRechazados'
            ]);
        }
        return [
            'resume' => $resumeData,
            'data' => $ingresos
        ];
    }

    /**
     * @param $ordenRetiro
     * @param $nroEnvio
     * @return string PDF on base64 encode
     */
    public function getPDFEtiqueta($ordenRetiro, $nroEnvio)
    {
        $data = $this->callPost(self::WS_ETIQUETA_PDF_ORDENRETIRO, [
            'idOrdenRetiro' => $ordenRetiro,
            'nroEnvio' => $nroEnvio,
            'logisticaInversa' => 'false'
        ], false);

        $xpath = $this->getXPath($data);
        return $xpath->query('/*')->item(0)->nodeValue;
    }

    /**
     * @return array|array[]
     * @throws Throwable
     */
    public function getAdmisionBranches()
    {
        $branches = [];
        foreach ($this->getBranchesWithService() as $branch) {
            if (in_array(self::SERVICIO_ADMISION, $branch['servicios'])) {
                $branches[] = $branch;
            }
        }

        return $branches;
    }

    public function getDeliveryBranchesZipCode($zipCode)
    {
        $branches = [];
        foreach ($this->getBranchesWithServiceZipCode($zipCode) as $branch) {
            if (in_array(self::SERVICIO_ENTREGA, $branch['servicios'])) {
                $branches[] = $branch;
            }
        }

        return $branches;
    }

    /**
     * @param $cuit
     * @return false|string
     */
    protected function getFixedCuit($cuit)
    {
        if (!$cuit || strlen($cuit) != 11 || preg_match("/[^0-9]/", $cuit)) {
            return false;
        }

        return substr($cuit, 0, 2) . '-' . substr($cuit, 2, 8) . '-' . substr($cuit, 10);
    }

    /**
     * @param $centros
     * @return array|array[]
     */
    protected function processBranches($centros)
    {
        array_walk($centros, function ($item, $key) use (&$centros) {
            foreach ($item as $k => $v) {
                $item[$k] = $v;
                if (is_string($v))
                    $item[$k] = trim($v);
            }
            $centros[$key] = $item;
        });

        return array_map(function ($row) {
            $servicios = [];
            if (isset($row['Servicios']) && isset($row['Servicios']['Servicio'])) {
                foreach ($row['Servicios']['Servicio'] as $servicio) {
                    $servicios[] = $servicio['IdTipoServicio'];
                }
            }

            return [
                'code' => $row['idCentroImposicion'],
                'short_name' => $row['Sigla'] ?? '',
                'address_street' => $row['Calle'] ?? '',
                'address_number' => $row['Numero'] ?? '',
                'address_floor' => $row['Piso'] ?? '',
                'address_dpt' => $row['Depto'] ?? '',
                'address_tower' => $row['Torre'] ?? '',
                'telephone' => $row['Telefono'] ?? '',
                'email' => $row['eMail'] ?? '',
                'city' => $row['Localidad'] ?? '',
                'zipcode' => $row['CodigoPostal'],
                'servicios' => $servicios,
                'active' => true,
            ];
        }, $centros);
    }

    /**
     * @param DataObject $request
     * @return string
     * @throws Exception
     */
    protected function getXmlOR(DataObject $request)
    {
        $date = new Zend_Date();
        $packages = [];
        foreach ($request->getPackages() as $package) {
            $packages[] = [
                '@alto' => $package['params']['height'],
                '@ancho' => $package['params']['width'],
                '@largo' => $package['params']['length'],
                '@peso' => $package['params']['weight'],
                '@valor' => $package['params']['customs_value'],
                '@cant' => 1,
                // If the next line is uncomemnted, OCA will return one label for each products instead of one label
                // for each package
                // '@cant' => array_reduce($package['items'], function ($ax, $dx) {
                //     return $ax + $dx['qty'];
                // }, 0),
            ];
        }
        $packages = ['paquete' => $packages];

        $xmlData = [
            'cabecera' => [
                '@ver' => '2.0',
                '@nrocuenta' => $this->getAccountNumber(),
            ],
            'origenes' => [
                'origen' => [
                    '@calle' => $request->getShipperAddressStreet1(),
                    '@nro' => '',
                    '@piso' => '',
                    '@depto' => '',
                    '@cp' => $request->getShipperAddressPostalCode(),
                    '@localidad' => $request->getShipperAddressCity(),
                    '@provincia' => $request->getShipperAddressProvince(),
                    '@contacto' => $request->getShipperContactPersonName(),
                    '@email' => $request->getShipperEmail(),
                    '@solicitante' => '',
                    '@observaciones' => '',
                    '@centrocosto' => $request->getCentroCosto(),
                    '@idfranjahoraria' => $request->getFranjaHoraria(),
                    '@idcentroimposicionorigen' => $request->getCentroImposicionOrigen(),
                    '@fecha' => $date->toString(Zend_Date::YEAR . Zend_Date::MONTH . Zend_Date::DAY),
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
                                '@calle' => $request->getRecipientAddressStreet(),
                                '@nro' => '',
                                '@piso' => '',
                                '@depto' => '',
                                '@localidad' => $request->getRecipientAddressCity(),
                                '@provincia' => $request->getRecipientAddressProvince(),
                                '@cp' => $request->getRecipientAddressPostalCode(),
                                '@telefono' => $request->getRecipientContactPhoneNumber(),
                                '@email' => $request->getRecipientEmail(),
                                '@idci' => $request->getCentroImposicion(),
                                '@celular' => '',
                                '@observaciones' => '',
                            ],
                            'paquetes' => $packages
                        ]
                    ]
                ]
            ]
        ];

        return $this->arrayToXML->buildXML($xmlData, 'ROWS');
    }

    /**
     * @return mixed
     */
    protected function getAccountNumber()
    {
        return $this->scopeConfig->getValue('carriers/gento_oca/account_number');
    }

    /**
     * @return mixed
     */
    protected function getUsername()
    {
        return $this->scopeConfig->getValue('carriers/gento_oca/username');
    }

    /**
     * @return string
     */
    protected function getPassword()
    {
        $password = $this->scopeConfig->getValue('carriers/gento_oca/password');
        return $this->encryptor->decrypt($password);
    }

    /**
     * @param $xmlObject
     * @param $fields
     * @return array
     */
    protected function loadDataset($xmlObject, $fields, $path = '//NewDataSet/Table')
    {
        $table = $this->loadPaths($xmlObject, ['table' => $path]);
        if (!isset($table['table'])) {
            return [];
        }

        $data = [];
        foreach ($table['table'] as $row) {
            $data[] = $this->loadFields($row, $fields);
        }
        return $data;
    }

    /**
     * @param $xmlObject
     * @param $paths
     * @param $fields
     * @return DOMElement[]
     */
    protected function loadPaths($xmlObject, $paths)
    {
        $xpath = $this->getXPath($xmlObject);

        if (!is_array($paths)) {
            $paths = [$paths];
        }

        $map = [];
        array_walk($paths, function ($value, $key) use (&$map) {
            if (is_numeric($key)) {
                $key = $value;
            }
            $map[$key] = $value;
        });

        $data = [];
        foreach ($map as $alias => $path) {
            foreach ($xpath->query($path) as $ci) {
                $data[$alias][] = $ci;
            }
        }
        return $data;
    }

    /**
     * @param $ci
     * @param $fields
     * @return array
     */
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
                // En caso de que sea un array de elementos (como puede ser Servicios)
                if ($item->firstChild != null && $item->firstChild->nodeType === XML_ELEMENT_NODE) {
                    $value = $this->childToArray($item->childNodes);
                }
                if ($item->firstChild == null || $item->firstChild->nodeType === XML_TEXT_NODE) {
                    $value = $item->nodeValue;
                }
            }

            $return[$alias] = $value;
        }

        return $return;
    }

    /**
     * @return Curl
     */
    protected function getCurlClient()
    {
        return $this->curlFactory->create();
    }

    /**
     * @param $service
     * @return string
     */
    protected function getServiceEpakUrl($service)
    {
        return sprintf('%s%s',
            $this->_serviceEpakUrl,
            $service
        );
    }

    /**
     * @param $service
     * @return string
     */
    protected function getServiceUrl($service)
    {
        return sprintf('%s%s',
            $this->_serviceUrl,
            $service
        );
    }

    /**
     * @param $service
     * @param array $data
     * @return string
     */
    protected function callPost($service, $data = [], $epak = true)
    {
        $curlClient = $this->getCurlClient();
        if ($epak) {
            $url = $this->getServiceEpakUrl($service);
        } else {
            $url = $this->getServiceUrl($service);
        }
        $history = $this->historyFactory->create();
        $history->setRequestUrl($url)
            ->setService($service)
            ->setRequestData($this->jsonHelper->serialize($data));

        $curlClient->post($url, $data);
        $response = $curlClient->getBody();
        $history->setResponseData($response);

        try {
            $this->handleError($curlClient);
            $history->setStatus('success');
        } catch (Throwable $e) {
            $history->setStatus('error');
            throw $e;
        } finally {
            $this->historyRepository->save($history);
        }

        return $response;
    }

    protected function handleError(Curl $curl)
    {
        $xpath = $this->getXPath($curl->getBody());

        $errors = $xpath->query("//Errores/Error/Descripcion");
        if ($errors->count() > 0) {
            throw new Exception($errors->item(0)->nodeValue);
        }
        $errors = $xpath->query("//NewDataSet/Table1/Error");
        if ($errors->count() > 0) {
            throw new Exception($errors->item(0)->nodeValue);
        }
    }

    protected function getXPath($xmlString)
    {
        $dom = new DOMDocument();
        $dom->loadXML($xmlString, ~LIBXML_DTDVALID);
        return new DOMXpath($dom);
    }

    protected function childToArray(\DOMNodeList $nodes)
    {
        $value = [];
        foreach ($nodes as $childNode) {
            if ($childNode->firstChild->nodeType === XML_ELEMENT_NODE) {
                $value[$childNode->nodeName][] = $this->childToArray($childNode->childNodes);
            }

            if ($childNode->firstChild->nodeType === XML_TEXT_NODE) {
                $value[$childNode->nodeName] = $childNode->nodeValue;
            }
        }

        return $value;
    }
}
