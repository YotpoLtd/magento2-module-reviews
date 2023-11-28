<?php
namespace Yotpo\Reviews\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;

/**
 * Class Data - To render the review widget at any place
 */
class Data extends AbstractHelper
{
    /**
     * Load the block dynamically
     *
     * @param string $blockName
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    private function renderYotpoProductBlock(string $blockName, AbstractBlock $parentBlock, Product $product = null)
    {
        /**
         * @var Template $template
         */
        $template = $parentBlock->getLayout()->createBlock(\Yotpo\Reviews\Block\Yotpo::class);
        $template->setTemplate('Yotpo_Reviews::' . $blockName . '.phtml');
        $template->setAttribute('product', $product);
        $template->setAttribute('fromHelper', true);
        return $template->toHtml();
    }

    /**
     * Show review form
     *
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    public function showWidget(AbstractBlock $parentBlock, Product $product = null)
    {
        return $this->renderYotpoProductBlock('widget_div', $parentBlock, $product);
    }

    /**
     * Show BottomLine(Reviews count and Rating average) in PDP
     *
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    public function showBottomline(AbstractBlock $parentBlock, Product $product = null)
    {
        return $this->renderYotpoProductBlock('bottomline', $parentBlock, $product);
    }

    /**
     * Show Q&A in PDP
     *
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    public function showQNA(AbstractBlock $parentBlock, Product $product = null)
    {
        return $this->renderYotpoProductBlock('qna', $parentBlock, $product);
    }

    /**
     * Show Carousel in PDP
     *
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    public function showReviewsCarousel(AbstractBlock $parentBlock, Product $product = null)
    {
        return $this->renderYotpoProductBlock('reviews_carousel', $parentBlock, $product);
    }

    /**
     * Show Promoted Products in PDP
     *
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    public function showPromotedProducts(AbstractBlock $parentBlock, Product $product = null)
    {
        return $this->renderYotpoProductBlock('promoted_products', $parentBlock, $product);
    }

    /**
     * Show SEO Page in PDP
     *
     * @param AbstractBlock $parentBlock
     * @param Product|null $product
     * @return mixed
     * @throws LocalizedException
     */
    public function showSEOPage(AbstractBlock $parentBlock, Product $product = null)
    {
        return $this->renderYotpoProductBlock('seo_page', $parentBlock, $product);
    }
}
