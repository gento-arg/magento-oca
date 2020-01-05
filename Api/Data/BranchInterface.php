<?php
namespace Gento\Oca\Api\Data;

/**
 * @api
 */
interface BranchInterface
{
    const BRANCH_ID = 'branch_id';
    const CODE = 'code';
    const SHORT_NAME = 'short_name';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const ADDRESS_STREET = 'address_street';
    const ADDRESS_NUMBER = 'address_number';
    const ADDRESS_FLOOR = 'address_floor';
    const CITY = 'city';
    const ZIPCODE = 'zipcode';
    const ACTIVE = 'active';

    /**
     * @param int $id
     * @return BranchInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $branchId
     * @return BranchInterface
     */
    public function setBranchId($branchId);

    /**
     * @return int
     */
    public function getBranchId();

    /**
     * @param string $code
     * @return BranchInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();
    /**
     * @param string $shortName
     * @return BranchInterface
     */
    public function setShortName($shortName);

    /**
     * @return string
     */
    public function getShortName();
    /**
     * @param string $name
     * @return BranchInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();
    /**
     * @param string $description
     * @return BranchInterface
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();
    /**
     * @param string $addressStreet
     * @return BranchInterface
     */
    public function setAddressStreet($addressStreet);

    /**
     * @return string
     */
    public function getAddressStreet();
    /**
     * @param string $addressNumber
     * @return BranchInterface
     */
    public function setAddressNumber($addressNumber);

    /**
     * @return string
     */
    public function getAddressNumber();
    /**
     * @param string $addressFloor
     * @return BranchInterface
     */
    public function setAddressFloor($addressFloor);

    /**
     * @return string
     */
    public function getAddressFloor();
    /**
     * @param string $city
     * @return BranchInterface
     */
    public function setCity($city);

    /**
     * @return string
     */
    public function getCity();
    /**
     * @param string $zipcode
     * @return BranchInterface
     */
    public function setZipcode($zipcode);

    /**
     * @return string
     */
    public function getZipcode();
    /**
     * @param int $active
     * @return BranchInterface
     */
    public function setActive($active);

    /**
     * @return int
     */
    public function getActive();

    /**
     * @return string
     */
    public function getFullDescription();

    /**
     * @return string
     */
    public function getFullAddress();
}
