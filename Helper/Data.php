<?php

namespace Gento\Oca\Helper;

use Gento\Oca\Model\Config\Source\UnitsAttribute;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
            ${$att} = (float)$product->getData(${$att . 'Att'});
            if (${$att} > 0) {
                ${$att} /= $factor;
            }
        }

        return [$width, $height, $length];
    }

    protected function getConfigData($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

}
