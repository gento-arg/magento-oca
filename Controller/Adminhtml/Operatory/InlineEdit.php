<?php
namespace Gento\Oca\Controller\Adminhtml\Operatory;

use Gento\Oca\Api\OperatoryRepositoryInterface;
use Gento\Oca\Api\Data\OperatoryInterface;
use Gento\Oca\Model\ResourceModel\Operatory as OperatoryResourceModel;
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
     * Operatory repository
     * @var OperatoryRepositoryInterface
     */
    protected $operatoryRepository;
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
     * Operatory resource model
     * @var OperatoryResourceModel
     */
    protected $operatoryResourceModel;

    /**
     * constructor
     * @param Context $context
     * @param OperatoryRepositoryInterface $operatoryRepository
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param JsonFactory $jsonFactory
     * @param OperatoryResourceModel $operatoryResourceModel
     */
    public function __construct(
        Context $context,
        OperatoryRepositoryInterface $operatoryRepository,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        JsonFactory $jsonFactory,
        OperatoryResourceModel $operatoryResourceModel
    ) {
        $this->operatoryRepository = $operatoryRepository;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->jsonFactory = $jsonFactory;
        $this->operatoryResourceModel = $operatoryResourceModel;
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

        foreach (array_keys($postItems) as $operatoryId) {
            /** @var \Gento\Oca\Model\Operatory|\Gento\Oca\Api\Data\OperatoryInterface $operatory */
            try {
                $operatory = $this->operatoryRepository->get((int)$operatoryId);
                $operatoryData = $postItems[$operatoryId];
                $this->dataObjectHelper->populateWithArray($operatory, $operatoryData, OperatoryInterface::class);
                $this->operatoryResourceModel->saveAttribute($operatory, array_keys($operatoryData));
            } catch (LocalizedException $e) {
                $messages[] = $this->getErrorWithOperatoryId($operatory, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithOperatoryId($operatory, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithOperatoryId(
                    $operatory,
                    __('Something went wrong while saving the Operatory.')
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
     * Add Operatory id to error message
     *
     * @param \Gento\Oca\Api\Data\OperatoryInterface $operatory
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithOperatoryId(OperatoryInterface $operatory, $errorText)
    {
        return '[Operatory ID: ' . $operatory->getId() . '] ' . $errorText;
    }
}
