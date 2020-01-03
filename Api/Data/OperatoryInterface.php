<?php
namespace Gento\Oca\Api\Data;

/**
 * @api
 */
interface OperatoryInterface
{
    const OPERATORY_ID = 'operatory_id';
    const NAME = 'name';
    const CODE = 'code';
    const ACTIVE = 'active';
    const USES_IDCI = 'uses_idci';
    const PAYS_ON_DESTINATION = 'pays_on_destination';
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
}
