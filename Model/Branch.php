<?php
namespace Gento\Oca\Model;

use Gento\Oca\Api\Data\BranchInterface;
use Gento\Oca\Model\ResourceModel\Branch as BranchResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * @method \Gento\Oca\Model\ResourceModel\Branch _getResource()
 * @method \Gento\Oca\Model\ResourceModel\Branch getResource()
 */
class Branch extends AbstractModel implements BranchInterface
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'gento_oca_branch';
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
    protected $_eventPrefix = 'gento_oca_branch';
    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'branch';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BranchResourceModel::class);
    }

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
     * @param int $branchId
     * @return BranchInterface
     */
    public function setBranchId($branchId)
    {
        return $this->setData(BranchInterface::BRANCH_ID, $branchId);
    }

    /**
     * @return int
     */
    public function getBranchId()
    {
        return $this->getData(BranchInterface::BRANCH_ID);
    }

    /**
     * @param string $code
     * @return BranchInterface
     */
    public function setCode($code)
    {
        return $this->setData(BranchInterface::CODE, $code);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getData(BranchInterface::CODE);
    }

    /**
     * @param string $shortName
     * @return BranchInterface
     */
    public function setShortName($shortName)
    {
        return $this->setData(BranchInterface::SHORT_NAME, $shortName);
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        return $this->getData(BranchInterface::SHORT_NAME);
    }

    /**
     * @param string $name
     * @return BranchInterface
     */
    public function setName($name)
    {
        return $this->setData(BranchInterface::NAME, $name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getData(BranchInterface::NAME);
    }

    /**
     * @param string $description
     * @return BranchInterface
     */
    public function setDescription($description)
    {
        return $this->setData(BranchInterface::DESCRIPTION, $description);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(BranchInterface::DESCRIPTION);
    }

    /**
     * @param string $addressStreet
     * @return BranchInterface
     */
    public function setAddressStreet($addressStreet)
    {
        return $this->setData(BranchInterface::ADDRESS_STREET, $addressStreet);
    }

    /**
     * @return string
     */
    public function getAddressStreet()
    {
        return $this->getData(BranchInterface::ADDRESS_STREET);
    }

    /**
     * @param string $addressNumber
     * @return BranchInterface
     */
    public function setAddressNumber($addressNumber)
    {
        return $this->setData(BranchInterface::ADDRESS_NUMBER, $addressNumber);
    }

    /**
     * @return string
     */
    public function getAddressNumber()
    {
        return $this->getData(BranchInterface::ADDRESS_NUMBER);
    }

    /**
     * @param string $addressFloor
     * @return BranchInterface
     */
    public function setAddressFloor($addressFloor)
    {
        return $this->setData(BranchInterface::ADDRESS_FLOOR, $addressFloor);
    }

    /**
     * @return string
     */
    public function getAddressFloor()
    {
        return $this->getData(BranchInterface::ADDRESS_FLOOR);
    }

    /**
     * @param string $city
     * @return BranchInterface
     */
    public function setCity($city)
    {
        return $this->setData(BranchInterface::CITY, $city);
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->getData(BranchInterface::CITY);
    }

    /**
     * @param string $zipcode
     * @return BranchInterface
     */
    public function setZipcode($zipcode)
    {
        return $this->setData(BranchInterface::ZIPCODE, $zipcode);
    }

    /**
     * @return string
     */
    public function getZipcode()
    {
        return $this->getData(BranchInterface::ZIPCODE);
    }

    /**
     * @param int $active
     * @return BranchInterface
     */
    public function setActive($active)
    {
        return $this->setData(BranchInterface::ACTIVE, $active);
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->getData(BranchInterface::ACTIVE);
    }
}
