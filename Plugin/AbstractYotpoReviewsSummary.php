<?php
namespace Yotpo\Reviews\Plugin;

use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Context;
use Magento\Review\Block\Product\ReviewRenderer;
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
     * @var CatalogImageHelper
     */
    protected $_catalogImageHelper;

    /**
     * AbstractYotpoReviewsSummary constructor.
     * @param Context $context
     * @param YotpoConfig $yotpoConfig
     * @param Registry $coreRegistry
     * @param CatalogImageHelper $catalogImageHelper
     */
    public function __construct(
        Context $context,
        YotpoConfig $yotpoConfig,
        Registry $coreRegistry,
        CatalogImageHelper $catalogImageHelper
    ) {
        $this->_context = $context;
        $this->_yotpoConfig = $yotpoConfig;
        $this->_coreRegistry = $coreRegistry;
        $this->_catalogImageHelper = $catalogImageHelper;
    }

    /**
     * Show BottomLine html
     *
     * @param Product $product
     * @return string
     */
    protected function _getCategoryBottomLineHtml(Product $product, $templateType = ReviewRenderer::SHORT_VIEW)
    {
        if ($this->_yotpoConfig->isV3StarRatingWidget()) {
            $escaper = $this->_context->getEscaper();
            $name = $escaper->escapeHtmlAttr(strip_tags((string)$product->getName()));
            $imageUrl = $escaper->escapeHtmlAttr(
                $this->_catalogImageHelper->init($product, 'product_page_image_large')->getUrl()
            );
            $description = $escaper->escapeHtmlAttr(strip_tags((string)$product->getShortDescription()));

            // phpcs:ignore
            return '<div class="yotpo-widget-instance"
                data-yotpo-section-id="' . $this->_yotpoConfig->getWidgetSectionId($templateType) . '"
                data-yotpo-instance-id="' . $this->_yotpoConfig->getV3InstanceId('ReviewsStarRatingsWidget') . '"
                data-yotpo-product-id="' . $product->getId() . '"
                data-yotpo-url="' . $product->getProductUrl() . '"
                data-yotpo-name="' . $name . '"
                data-yotpo-image-url="' . $imageUrl . '"
                data-yotpo-description="' . $description . '"
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
