<?php

namespace Gento\Oca\Api\Data;

/**
 * @api
 */
interface OperatoryInterface
{
    const ACTIVE = 'active';
    const CODE = 'code';
    const NAME = 'name';
    const OPERATORY_ID = 'operatory_id';
    const ORIGIN_BRANCH_ID = 'origin_branch_id';
    const PAYS_ON_DESTINATION = 'pays_on_destination';
    const TYPE = 'operatory_type';
    const USES_IDCI = 'uses_idci';

    /**
     * @param int $id
     * @return OperatoryInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $operatoryId
     * @return OperatoryInterface
     */
    public function setOperatoryId($operatoryId);

    /**
     * @return int
     */
    public function getOperatoryId();

    /**
     * @param string $name
     * @return OperatoryInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $code
     * @return OperatoryInterface
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param int $active
     * @return OperatoryInterface
     */
    public function setActive($active);

    /**
     * @return int
     */
    public function getActive();

    /**
     * @param int $usesIdci
     * @return OperatoryInterface
     */
    public function setUsesIdci($usesIdci);

    /**
     * @return int
     */
    public function getUsesIdci();

    /**
     * @param int $paysOnDestination
     * @return OperatoryInterface
     */
    public function setPaysOnDestination($paysOnDestination);

    /**
     * @return int
     */
    public function getPaysOnDestination();

    /**
     * @param string $type
     * @return OperatoryInterface
     */
    public function setOperatoryType($type);

    /**
     * @return string
     */
    public function getOperatoryType();

    /**
     * @param int $branchId
     * @return OperatoryInterface
     */
    public function setOriginBranchId($branchId);

    /**
     * @return int
     */
    public function getOriginBranchId();

}
