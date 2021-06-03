<?php
namespace Gento\Oca\Model\Operatory\Executor;

use Gento\Oca\Api\OperatoryRepositoryInterface;
use Gento\Oca\Api\ExecutorInterface;

class Delete implements ExecutorInterface
{
    /**
     * @var OperatoryRepositoryInterface
     */
    private $operatoryRepository;

    /**
     * Delete constructor.
     * @param OperatoryRepositoryInterface $operatoryRepository
     */
    public function __construct(
        OperatoryRepositoryInterface $operatoryRepository
    ) {
        $this->operatoryRepository = $operatoryRepository;
    }

    /**
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id)
    {
        $this->operatoryRepository->deleteById($id);
    }
}
