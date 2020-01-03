<?php
namespace Gento\Oca\Controller\Adminhtml\Branch;

use Gento\Oca\Api\BranchRepositoryInterface;
use Gento\Oca\Api\Data\BranchInterface;
use Gento\Oca\Model\ResourceModel\Branch as BranchResourceModel;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class InlineEdit
 */
class InlineEdit extends Action
{
    /**
     * Branch repository
     * @var BranchRepositoryInterface
     */
    protected $branchRepository;
    /**
     * Data object processor
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
    /**
     * Data object helper
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * JSON Factory
     * @var JsonFactory
     */
    protected $jsonFactory;
    /**
     * Branch resource model
     * @var BranchResourceModel
     */
    protected $branchResourceModel;

    /**
     * constructor
     * @param Context $context
     * @param BranchRepositoryInterface $branchRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param JsonFactory $jsonFactory
     * @param BranchResourceModel $branchResourceModel
     */
    public function __construct(
        Context $context,
        BranchRepositoryInterface $branchRepository,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        JsonFactory $jsonFactory,
        BranchResourceModel $branchResourceModel
    ) {
        $this->branchRepository = $branchRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->jsonFactory = $jsonFactory;
        $this->branchResourceModel = $branchResourceModel;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $branchId) {
            /** @var \Gento\Oca\Model\Branch|\Gento\Oca\Api\Data\BranchInterface $branch */
            try {
                $branch = $this->branchRepository->get((int)$branchId);
                $branchData = $postItems[$branchId];
                $this->dataObjectHelper->populateWithArray($branch, $branchData, BranchInterface::class);
                $this->branchResourceModel->saveAttribute($branch, array_keys($branchData));
            } catch (LocalizedException $e) {
                $messages[] = $this->getErrorWithBranchId($branch, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithBranchId($branch, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithBranchId(
                    $branch,
                    __('Something went wrong while saving the Branch.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Branch id to error message
     *
     * @param \Gento\Oca\Api\Data\BranchInterface $branch
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBranchId(BranchInterface $branch, $errorText)
    {
        return '[Branch ID: ' . $branch->getId() . '] ' . $errorText;
    }
}
