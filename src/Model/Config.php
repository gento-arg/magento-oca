<?php

declare(strict_types = 1);

namespace Gento\Oca\Model;

use Gento\Oca\Api\ConfigInterface;
use Gento\Oca\Model\Config\Source\UnitsAttribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterManager $filterManager
     */
    public function __construct(
        readonly private ScopeConfigInterface $scopeConfig,
        readonly private FilterManager $filterManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function addDescriptionToBranches($branches): array
    {
        foreach ($branches as $idx => $branch) {
            $branches[$idx] = $this->addDescriptionToBranch($branch);
        }
        return $branches;
    }

    /**
     * @param array $branch
     *
     * @return array
     */
    public function addDescriptionToBranch(array $branch): array
    {
        $branch['branch_description'] = $this->getParsedDescription($branch);
        return $branch;
    }

    /**
     * @param array $branch
     *
     * @return string
     */
    protected function getParsedDescription(array $branch): string
    {
        $template = $this->getBranchDescriptionTemplate();

        return $this->filterManager->template($template, ['variables' => $branch]);
    }

    /**
     * @inheritDoc
     */
    public function getBranchDescriptionTemplate()
    {
        return $this->scopeConfig->getValue(
            self::PREFIX . '/' . self::XPATH_BRANCH_DESCRIPTION_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getBranchAutoPopulate(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::PREFIX . '/' . self::XPATH_BRANCH_AUTOPOPULATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getDaysExtra(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::PREFIX . '/' . self::XPATH_DAYS_EXTRA,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getHistoryLimit(): int
    {
        return (int)$this->scopeConfig->getValue(
            self::PREFIX . '/' . self::XPATH_HISTORY_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getProductSize(Product $product): array
    {
        $attrs = ['width', 'height', 'length'];
        $width = $height = $length = 0;

        foreach ($attrs as $att) {
            ${$att . 'Att'} = $this->getConfigData("carriers/gento_oca/volume/" . $att);
        }

        if (!$widthAtt || !$heightAtt || !$lengthAtt) {
            return [0, 0, 0];
        }

        $unitValue = $this->getConfigData("carriers/gento_oca/volume/unit");
        $factor = 1;
        if ($unitValue == UnitsAttribute::UNIT_CENTIMETER) {
            $factor = 100;
        } elseif ($unitValue == UnitsAttribute::UNIT_MILLIMETER) {
            $factor = 1000;
        }

        foreach ($attrs as $att) {
            ${$att} = (float)$product->getData(${$att . 'Att'});
            if (${$att} > 0) {
                ${$att} /= $factor;
            }
        }

        return [$width, $height, $length];
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getConfigData($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function getReceptionTime(): string
    {
        return $this->scopeConfig->getValue(
            self::PREFIX . '/' . self::XPATH_RECEPTION_TIME,
            ScopeInterface::SCOPE_STORE
        );
    }
}
