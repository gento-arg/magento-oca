<?php

declare(strict_types = 1);

namespace Gento\Oca\Model\Resolver;

use Gento\Oca\Service\Branch;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Throwable;

class Branches implements ResolverInterface
{
    /**
     * @var Branch
     */
    private $branchService;

    /**
     * @param Branch $branchService
     */
    public function __construct(
        Branch $branchService
    ) {
        $this->branchService = $branchService;
    }

    /**
     * @param Field            $field
     * @param ContextInterface $context
     * @param ResolveInfo      $info
     * @param array|null       $value
     * @param array|null       $args
     *
     * @throws Throwable
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $zipcode = $args['zipCode'];
        $branches = $this->branchService->getBranches($zipcode);
        return [
            'items' => $branches
        ];
    }
}
