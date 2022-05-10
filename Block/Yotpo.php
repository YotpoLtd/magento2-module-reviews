<?php
namespace Yotpo\Reviews\Block;

use Magento\Catalog\Helper\Image as CatalogImageHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Yotpo\Reviews\Model\Config as YotpoConfig;
use Magento\Framework\View\Element\Template;

/**
 * Class Yotpo - Block file for Yotpo Reviews Widget
 */
class Yotpo extends Template
{
    /**
     * @var YotpoConfig
     */
    private $yotpoConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var CatalogImageHelper
     */
    private $catalogImageHelper;

    /**
     * Yotpo constructor.
     * @param Context $context
     * @param YotpoConfig $yotpoConfig
     * @param Registry $coreRegistry
     * @param CatalogImageHelper $catalogImageHelper
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        YotpoConfig $yotpoConfig,
        Registry $coreRegistry,
        CatalogImageHelper $catalogImageHelper,
        array $data = []
    ) {
        $this->yotpoConfig = $yotpoConfig;
        $this->coreRegistry = $coreRegistry;
        $this->catalogImageHelper = $catalogImageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check module is enabled and API tokens set
     *
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isEnabled()
    {
        return $this->yotpoConfig->isEnabled() && $this->yotpoConfig->isAppKeyAndSecretSet();
    }

    /**
     * Get Yotpo API Key
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAppKey()
    {
        return $this->yotpoConfig->getAppKey();
    }

    /**
     * Set current product to the block object
     *
     * @return array|mixed|null
     */
    public function getProduct()
    {
        if ($this->getData('product') === null) {
            $this->setData('product', $this->coreRegistry->registry('current_product'));
        }
        return $this->getData('product');
    }

    /**
     * Find if current product is exist
     *
     * @return bool
     */
    public function hasProduct()
    {
        return $this->getProduct() && $this->getProduct()->getId();
    }

    /**
     * Get Product ID from product object
     *
     * @return int|null
     */
    public function getProductId()
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->getProduct()->getId();
    }

    /**
     * Get product Name from product object
     *
     * @return array<int, mixed>|string|null
     */
    public function getProductName()
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->escapeString($this->getProduct()->getName());
    }

    /**
     * Get description from product object
     *
     * @return array<int, mixed>|string|null
     */
    public function getProductDescription()
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->escapeString($this->getProduct()->getShortDescription());
    }

    /**
     * Get Product URL from product object
     *
     * @return string|null
     */
    public function getProductUrl()
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->getProduct()->getProductUrl();
    }

    /**
     * Get price from product object
     *
     * @return float|null
     */
    public function getProductFinalPrice()
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->getProduct()->getFinalPrice();
    }

    /**
     * Get Product Image from product object
     *
     * @param  string  $imageId
     * @return string|null
     */
    public function getProductImageUrl($imageId = 'product_page_image_large')
    {
        if (!$this->hasProduct()) {
            return null;
        }
        return $this->catalogImageHelper->init($this->getProduct(), $imageId)->getUrl();
    }

    /**
     * Check if widget is ready to render
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isRenderWidget()
    {
        return $this->hasProduct() && ($this->yotpoConfig->isWidgetEnabled() || $this->getData('fromHelper'));
    }

    /**
     * Check if BottomLine is ready to render
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isRenderBottomline()
    {
        return $this->hasProduct() && ($this->yotpoConfig->isBottomlineEnabled() || $this->getData('fromHelper'));
    }

    /**
     * Check if BottomLineQnA is ready to render
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isRenderBottomlineQna()
    {
        return $this->hasProduct() && $this->yotpoConfig->isBottomlineQnaEnabled();
    }

    /**
     * Escape tags from string
     *
     * @param string|null $str
     * @return array<int, mixed>|string
     */
    public function escapeString($str)
    {
        if ($str === null) {
            return '';
        }

        return $this->_escaper->escapeHtml(strip_tags($str));
    }

    /**
     * Get widget URL
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getYotpoWidgetUrl()
    {
        return $this->yotpoConfig->getYotpoWidgetUrl();
    }
}
