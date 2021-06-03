<?php
namespace Gento\Oca\Block\Adminhtml\Button\Operatory;

use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Delete implements ButtonProviderInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Delete constructor.
     * @param Registry $registry
     * @param UrlInterface $url
     */
    public function __construct(Registry $registry, UrlInterface $url)
    {
        $this->registry = $registry;
        $this->url = $url;
    }

    /**
     * get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getOperatoryId()) {
            $data = [
                'label' => __('Delete Operatory'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return \Gento\Oca\Api\Data\OperatoryInterface | null
     */
    private function getOperatory()
    {
        return $this->registry->registry('current_operatory');
    }

    /**
     * @return int|null
     */
    private function getOperatoryId()
    {
        $operatory = $this->getOperatory();
        return ($operatory) ? $operatory->getId() : null;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->url->getUrl(
            '*/*/delete',
            [
                'operatory_id' => $this->getoperatoryId()
            ]
        );
    }
}
