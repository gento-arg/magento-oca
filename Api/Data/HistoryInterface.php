<?php

namespace Gento\Oca\Api\Data;

/**
 * @api
 */
interface HistoryInterface
{
    const CREATED_AT = 'created_at';
    const REQUEST_DATA = 'request_data';
    const REQUEST_ID = 'request_id';
    const REQUEST_URL = 'request_url';
    const RESPONSE_DATA = 'response_data';
    const SERVICE = 'service';

    /**
     * @param int $id
     * @return HistoryInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param string $url
     * @return HistoryInterface
     */
    public function setRequestUrl($url);

    /**
     * @return string
     */
    public function getRequestUrl();

    /**
     * @param string $data
     * @return HistoryInterface
     */
    public function setRequestData($data);

    /**
     * @return string
     */
    public function getRequestData();

    /**
     * @param string $data
     * @return HistoryInterface
     */
    public function setResponseData($data);

    /**
     * @return string
     */
    public function getResponseData();

    /**
     * @param string $data
     * @return HistoryInterface
     */
    public function setService($data);

    /**
     * @return string
     */
    public function getService();
}
