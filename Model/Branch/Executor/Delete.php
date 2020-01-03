<?php
namespace Gento\Oca\Model\Branch\Executor;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\ExecutorInterface;

class Delete implements ExecutorInterface
{
    /**
     * @var BranchRepositoryInterface
     */
    private $branchRepository;

    /**
     * Delete constructor.
     * @param BranchRepositoryInterface $branchRepository
     */
    public function __construct(
        BranchRepositoryInterface $branchRepository
    ) {
        $this->branchRepository = $branchRepository;
    }

    /**
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id)
    {
        $this->branchRepository->deleteById($id);
    }
}
