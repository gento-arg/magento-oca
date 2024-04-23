<?php

declare(strict_types = 1);

namespace Gento\Oca\Helper;

use Gento\Oca\Model\Config\Source\UnitsAttribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filter\FilterManager;
use Magento\Store\Model\ScopeInterface;

class Data
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    protected FilterManager $filterManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FilterManager $filterManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->filterManager = $filterManager;
    }

    /**
     * @param array $branch
     *
     * @return array
     */
    public function addDescriptionToBranch(array $branch)
    {
        $branch['branch_description'] = $this->getParsedDescription($branch);
        return $branch;
    }

    /**
     * @param $branches
     *
     * @return array[]
     */
    public function addDescriptionToBranches($branches)
    {
        foreach ($branches as $idx => $branch) {
            $branches[$idx] = $this->addDescriptionToBranch($branch);
        }
        return $branches;
    }

    public function getProductSize(Product $product)
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
            ${$att} = (float) $product->getData(${$att . 'Att'});
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
     * @param array $branch
     *
     * @return string
     */
    protected function getParsedDescription(array $branch)
    {
        $template = $this->getConfigData('carriers/gento_oca/branch_description');

        return $this->filterManager->template($template, ['variables' => $branch]);
    }

}
