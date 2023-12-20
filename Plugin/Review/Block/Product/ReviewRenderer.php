<?php
namespace Yotpo\Reviews\Plugin\Review\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yotpo\Reviews\Plugin\AbstractYotpoReviewsSummary;
use Magento\Review\Block\Product\ReviewRenderer as ProductReviewRenderer;

/**
 * Class ReviewRenderer - Plugin to render review summary html
 */
class ReviewRenderer extends AbstractYotpoReviewsSummary
{
    /**
     * Render review summary html
     *
     * @param ProductReviewRenderer $reviewRendererBlock
     * @param callable $proceed
     * @param Product $product
     * @param string $templateType
     * @param bool $displayIfNoReviews
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundGetReviewsSummaryHtml(
        ProductReviewRenderer $reviewRendererBlock,
        callable $proceed,
        Product $product,
        $templateType = ProductReviewRenderer::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if (!$this->_yotpoConfig->isEnabled()) {
            return $proceed($product, $templateType, $displayIfNoReviews);
        }

        $currentProduct = $this->_coreRegistry->registry('current_product');
        if (!$currentProduct || $currentProduct->getId() !== $product->getId()) {
            if ($this->_yotpoConfig->isCategoryBottomlineEnabled()) {
                return $this->_getCategoryBottomLineHtml($product);
            } elseif (!$this->_yotpoConfig->isMdrEnabled()) {
                return $proceed($product, $templateType, $displayIfNoReviews);
            }
        }

        return '';
    }
}
