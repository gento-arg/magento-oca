<?php

namespace Gento\Oca\Model;

use Gento\Oca\Api\Data\OperatoryInterface;
use Gento\Oca\Model\ResourceModel\Operatory as OperatoryResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * @method \Gento\Oca\Model\ResourceModel\Operatory _getResource()
 * @method \Gento\Oca\Model\ResourceModel\Operatory getResource()
 */
class Operatory extends AbstractModel implements OperatoryInterface
{
    /**
     */
    const CACHE_TAG = 'gento_oca_operatory';

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
    protected $_eventPrefix = 'gento_oca_operatory';
    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'operatory';

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
     * Get Page id
     *
     * @return array
     */
    public function getOperatoryId()
    {
        return $this->getData(OperatoryInterface::OPERATORY_ID);
    }

    /**
     * set Operatory id
     *
     * @param int $operatoryId
     * @return OperatoryInterface
     */
    public function setOperatoryId($operatoryId)
    {
        return $this->setData(OperatoryInterface::OPERATORY_ID, $operatoryId);
    }

    /**
     * @param string $name
     * @return OperatoryInterface
     */
    public function setName($name)
    {
        return $this->setData(OperatoryInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(OperatoryInterface::NAME);
    }

    /**
     * @param string $code
     * @return OperatoryInterface
     */
    public function setCode($code)
    {
        return $this->setData(OperatoryInterface::CODE, $code);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData(OperatoryInterface::CODE);
    }

    /**
     * @param int $active
     * @return OperatoryInterface
     */
    public function setActive($active)
    {
        return $this->setData(OperatoryInterface::ACTIVE, $active);
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->getData(OperatoryInterface::ACTIVE);
    }

    /**
     * @param int $usesIdci
     * @return OperatoryInterface
     */
    public function setUsesIdci($usesIdci)
    {
        return $this->setData(OperatoryInterface::USES_IDCI, $usesIdci);
    }

    /**
     * @return int
     */
    public function getUsesIdci()
    {
        return $this->getData(OperatoryInterface::USES_IDCI);
    }

    /**
     * @param int $paysOnDestination
     * @return OperatoryInterface
     */
    public function setPaysOnDestination($paysOnDestination)
    {
        return $this->setData(OperatoryInterface::PAYS_ON_DESTINATION, $paysOnDestination);
    }

    /**
     * @return int
     */
    public function getPaysOnDestination()
    {
        return $this->getData(OperatoryInterface::PAYS_ON_DESTINATION);
    }

    /**
     * @param string $type
     * @return OperatoryInterface
     */
    public function setOperatoryType($type)
    {
        return $this->setData(OperatoryInterface::TYPE, $type);
    }

    /**
     * @return string
     */
    public function getOperatoryType()
    {
        return $this->getData(OperatoryInterface::TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setOriginBranchId($branchId)
    {
        return $this->setData(OperatoryInterface::ORIGIN_BRANCH_ID, $branchId);
    }

    /**
     * @inheritDoc
     */
    public function getOriginBranchId()
    {
        return $this->getData(OperatoryInterface::ORIGIN_BRANCH_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPosition($position)
    {
        return $this->setData(OperatoryInterface::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function getPosition()
    {
        return $this->getData(OperatoryInterface::POSITION);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(OperatoryResourceModel::class);
    }
}
