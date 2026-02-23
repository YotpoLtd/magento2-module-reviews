<?php

namespace Yotpo\Reviews\Model;

use Magento\Eav\Model\Entity;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yotpo\Core\Model\Config as YotpoCoreConfig;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Review\Block\Product\ReviewRenderer;

class StarRatingSectionId
{
    const PRODUCT = 'product';
    const CATEGORY = 'collection';
    const HOME = 'index';
    const CART = 'cart';
    const PRODUCT_GRID = 'product-grid';
}

/**
 * Class Config - Get Reviews related config values
 */
class Config extends YotpoCoreConfig
{
    const MODULE_NAME = 'Yotpo_Reviews';

    /**
     * @var mixed[]
     */
    protected $reviewsConfig = [
        'yotpo_widget_url' => ['path' => 'yotpo/env/yotpo_widget_url'],
        'yotpo_installation_date' => ['path' => 'yotpo/module_info/yotpo_installation_date'],
        'widget_enabled' => ['path' => 'yotpo/settings/widget_enabled'],
        'category_bottomline_enabled' => ['path' => 'yotpo/settings/category_bottomline_enabled'],
        'bottomline_enabled' => ['path' => 'yotpo/settings/bottomline_enabled'],
        'qna_enabled' => ['path' => 'yotpo/settings/qna_enabled'],
        'carousel_product_enabled' => ['path' => 'yotpo/settings/carousel_product_enabled'],
        'carousel_category_enabled' => ['path' => 'yotpo/settings/carousel_category_enabled'],
        'carousel_home_enabled' => ['path' => 'yotpo/settings/carousel_home_enabled'],
        'promoted_products_product_enabled' => ['path' => 'yotpo/settings/promoted_products_product_enabled'],
        'promoted_products_category_enabled' => ['path' => 'yotpo/settings/promoted_products_category_enabled'],
        'promoted_products_home_enabled' => ['path' => 'yotpo/settings/promoted_products_home_enabled'],
        'reviews_tab_enabled' => ['path' => 'yotpo/settings/reviews_tab_enabled'],
        'mdr_enabled' => ['path' => 'yotpo/settings/mdr_enabled'],
        'v3_enabled' => ['path' => 'yotpo/reviews/v3_enabled'],
        'sync_widget_v3_instance_ids_data' => ['path' => 'yotpo/reviews/sync_widget_v3_instance_ids_data'],
        'widget_v3_instance_ids_last_sync_time' => ['path' => 'yotpo/reviews/widget_v3_instance_ids_last_sync_time'],
    ];

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var string[]
     */
    protected $reviewsEndPoints = [
        'metrics'  => 'apps/{store_id}/account_usages/metrics',
        'product_bottomline'  => 'products/{store_id}/{product_id}/bottomline',
        'api_v2_widgets' => 'api/v2/widgets?app_key={store_id}'
    ];

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param ModuleListInterface $moduleList
     * @param WriterInterface $configWriter
     * @param ConfigResource $configResource
     * @param ProductMetadataInterface $productMetadata
     * @param Entity $entity
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        ModuleListInterface $moduleList,
        WriterInterface $configWriter,
        ConfigResource $configResource,
        ProductMetadataInterface $productMetadata,
        Entity $entity
    ) {
        $this->encryptor = $encryptor;

        parent::__construct(
            $storeManager,
            $scopeConfig,
            $moduleList,
            $encryptor,
            $configWriter,
            $configResource,
            $productMetadata,
            $entity
        );
        $this->config = array_merge($this->config, $this->reviewsConfig);
        $this->endPoints = array_merge($this->endPoints, $this->reviewsEndPoints);
    }

    /**
     * Get Store Manager
     *
     * @return StoreManagerInterface
     */
    public function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }

    /**
     * Check if review form is enabled
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isWidgetEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return ($this->getConfig('widget_enabled', $scopeId, $scope)) ? true : false;
    }

    /**
     * Check if BottomLine is enabled for category page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isCategoryBottomlineEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return ($this->getConfig('category_bottomline_enabled', $scopeId, $scope)) ? true : false;
    }

    /**
     * Check if BottomLine is enabled for PDP Page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isBottomlineEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return ($this->getConfig('bottomline_enabled', $scopeId, $scope)) ? true : false;
    }

    /**
     * Check if BottomLine QnA enabled for PDP page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isBottomlineQnaEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('qna_enabled', $scopeId, $scope);
    }

    /**
     * Check if Carousel enabled for category pages
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isCarouselOnCategoryPagesEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('carousel_category_enabled', $scopeId, $scope);
    }

    /**
     * Check if Carousel enabled for home pages
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isCarouselOnHomePagesEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('carousel_home_enabled', $scopeId, $scope);
    }

    /**
     * Check if Carousel enabled for PDP page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isCarouselOnProductPagesEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('carousel_product_enabled', $scopeId, $scope);
    }

    /**
     * Check if Promoted Products enabled for PDP page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isPromotedProductsOnProductPageEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('promoted_products_product_enabled', $scopeId, $scope);
    }

    /**
     * Check if Promoted Products enabled for category pages
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isPromotedProductsOnCategoryPagesEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('promoted_products_category_enabled', $scopeId, $scope);
    }

    /**
     * Check if Promoted Products enabled for home page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isPromotedProductsOnHomePageEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('promoted_products_home_enabled', $scopeId, $scope);
    }

    /**
     * Check if Reviews Tab enabled for PDP page
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isReviewsTabEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('reviews_tab_enabled', $scopeId, $scope);
    }

    /**
     * Find whether Magento default review form is to display or not
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isMdrEnabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return (bool)$this->getConfig('mdr_enabled', $scopeId, $scope);
    }

    /**
     * Find whether widgets V3 are enabled
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isV3Enabled(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return (bool)$this->getConfig('v3_enabled', $scopeId, $scope);
    }

    /**
     * Find if Yotpo is activated
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isActivated(?int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->isEnabled($scopeId, $scope) && $this->isAppKeyAndSecretSet($scopeId, $scope);
    }

    /**
     * Get Yotpo No Schema Api Url
     *
     * @param string $path
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getYotpoNoSchemaApiUrl($path = "")
    {
        return preg_replace('#^https?:#', '', $this->getYotpoApiUrl($path));
    }

    /**
     * Get Yotpo Api Url
     *
     * @param string $path
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getYotpoApiUrl($path = "")
    {
        return $this->getConfig('apiV1') . $path;
    }

    /**
     * Get Widget URL
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getYotpoWidgetUrl(): string
    {
        return $this->getConfig('yotpo_widget_url') . $this->getAppKey() . '/widget.js';
    }

    /**
     * Find if Youtpo is disabled for any store
     *
     * @param array<int, int> $storeIds
     * @return array<int, int>
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function filterDisabledStoreIds(array $storeIds = []): array
    {
        foreach ($storeIds as $key => $storeId) {
            if (!($this->isEnabled($storeId, ScopeInterface::SCOPE_STORE)
                && $this->isAppKeyAndSecretSet($storeId, ScopeInterface::SCOPE_STORE))) {
                unset($storeIds[$key]);
            }
        }
        return array_values($storeIds);
    }

    /**
     * Find if Yotpo is enabled and configured correctly
     *
     * @param int $storeId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function filterDisabledStoreId(int $storeId): bool
    {
        if (!($this->isEnabled($storeId, ScopeInterface::SCOPE_STORE)
            && $this->isAppKeyAndSecretSet($storeId, ScopeInterface::SCOPE_STORE))) {
            return false;
        }
        return true;
    }

    /**
     * Returns widget v3 instance id from config by name
     *
     * @param string $widgetTypeName
     * @return string|bool
     */
    public function getV3InstanceId($widgetTypeName)
    {
        $syncWidgetV3InstanceIdsData = $this->getConfig('sync_widget_v3_instance_ids_data');
        if (!$syncWidgetV3InstanceIdsData) {
            return false;
        }

        $v3InstanceIds = json_decode($syncWidgetV3InstanceIdsData, true);

        if (is_array($v3InstanceIds) && array_key_exists($widgetTypeName, $v3InstanceIds)) {
            $v3InstanceId = $v3InstanceIds[$widgetTypeName];
            if (strlen($v3InstanceId) == 0) {
                return false;
            }

            return $v3InstanceId;
        }

        return false;
    }

    public function isV3StarRatingWidget()
    {
        $instanceId = $this->getV3InstanceId('ReviewsStarRatingsWidget');

        return $instanceId !== false && $this->isV3Enabled();
    }

    public static function getWidgetSectionId($sectionId)
    {
        switch ($sectionId) {
            case ReviewRenderer::DEFAULT_VIEW:
            case ReviewRenderer::FULL_VIEW:
                return StarRatingSectionId::PRODUCT;
            case ReviewRenderer::SHORT_VIEW:
                return StarRatingSectionId::CATEGORY;
            default:
                return StarRatingSectionId::CATEGORY;
        }
    }
}
