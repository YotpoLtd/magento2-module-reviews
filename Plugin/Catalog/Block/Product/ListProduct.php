<?php
namespace Yotpo\Reviews\Plugin\Catalog\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yotpo\Reviews\Plugin\AbstractYotpoReviewsSummary;
use Magento\Catalog\Block\Product\ListProduct as CatalogListProduct;

/**
 * Class ListProduct - Plugin to show the reviews summary in list page
 */
class ListProduct extends AbstractYotpoReviewsSummary
{

    /**
     * Show reviews summary in list page
     *
     * @param CatalogListProduct $listProductBlock
     * @param callable $proceed
     * @param Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundGetReviewsSummaryHtml(
        CatalogListProduct $listProductBlock,
        callable $proceed,
        Product $product,
        bool $templateType = false,
        bool $displayIfNoReviews = false
    ): string {
        if (!$this->_yotpoConfig->isEnabled()) {
            return $proceed($product, $templateType, $displayIfNoReviews);
        }

        if ($this->_yotpoConfig->isCategoryBottomlineEnabled()) {
            return $this->_getCategoryBottomLineHtml($product);
        } elseif (!$this->_yotpoConfig->isMdrEnabled()) {
            return $proceed($product, $templateType, $displayIfNoReviews);
        } else {
            return '';
        }
    }
}
