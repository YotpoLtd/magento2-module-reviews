<?php
namespace Yotpo\Reviews\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;
use Yotpo\Reviews\Model\Config as YotpoConfig;

/**
 * Class AbstractYotpoReviewsSummary - Abstract plugin for reviews plugin
 */
class AbstractYotpoReviewsSummary
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var YotpoConfig
     */
    protected $_yotpoConfig;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * AbstractYotpoReviewsSummary constructor.
     * @param Context $context
     * @param YotpoConfig $yotpoConfig
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        YotpoConfig $yotpoConfig,
        Registry $coreRegistry
    ) {
        $this->_context = $context;
        $this->_yotpoConfig = $yotpoConfig;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Show BottomLine html
     *
     * @param Product $product
     * @return string
     */
    protected function _getCategoryBottomLineHtml(Product $product, $templateType)
    {
        if ($this->_yotpoConfig->isV3StarRatingWidget()) {
            // phpcs:ignore
            return '<div class="yotpo-widget-instance"
                data-yotpo-section-id="' . $this->_yotpoConfig->getWidgetSectionId($templateType) . '"
                data-yotpo-instance-id="' . $this->_yotpoConfig->getV3InstanceId('ReviewsStarRatingsWidget') . '"
                data-yotpo-product-id="' . $product->getId() . '"
                data-yotpo-url="' . $product->getProductUrl() . '"
                data-yotpo-name="' . $product->getProductName() . '"
                data-yotpo-image-url="' . $product->getProductImageUrl() . '"
                data-yotpo-description="' . $product->getProductDescription() . '"
            ></div>';
        } else {
            // phpcs:ignore
            return '<div class="yotpo bottomLine bottomline-position"
                data-product-id="' . $product->getId() . '"
                data-url="' . $product->getProductUrl() . '"
            ></div>';
        }
    }
}
