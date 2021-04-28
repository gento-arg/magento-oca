<?php

namespace Gento\Oca\Model;

use Gento\Oca\Api\Data\HistoryInterface;
use Gento\Oca\Model\ResourceModel\History as HistoryResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * @method HistoryResourceModel _getResource()
 * @method HistoryResourceModel getResource()
 */
class History extends AbstractModel implements HistoryInterface
{
    /**
     */
    const CACHE_TAG = 'gento_oca_history';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'gento_oca_history';
    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'history';

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function setRequestUrl($url)
    {
        return $this->setData(HistoryInterface::REQUEST_URL, $url);
    }

    /**
     * @inheritDoc
     */
    public function getRequestUrl()
    {
        return $this->getData(HistoryInterface::REQUEST_URL);
    }

    /**
     * @inheritDoc
     */
    public function setRequestData($data)
    {
        return $this->setData(HistoryInterface::REQUEST_DATA, $data);
    }

    /**
     * @inheritDoc
     */
    public function getRequestData()
    {
        return $this->getData(HistoryInterface::REQUEST_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setResponseData($data)
    {
        return $this->setData(HistoryInterface::RESPONSE_DATA, $data);
    }

    /**
     * @inheritDoc
     */
    public function getResponseData()
    {
        return $this->getData(HistoryInterface::RESPONSE_DATA);
    }

    /**
     * @inheritDoc
     */
    public function setService($data)
    {
        return $this->setData(HistoryInterface::SERVICE, $data);
    }

    /**
     * @inheritDoc
     */
    public function getService()
    {
        return $this->getData(HistoryInterface::SERVICE);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(HistoryResourceModel::class);
    }
}
