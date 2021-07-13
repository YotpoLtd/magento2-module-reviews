<?php
namespace Yotpo\Reviews\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Yotpo\Core\Model\Config as YotpoCoreConfig;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;

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
        'mdr_enabled' => ['path' => 'yotpo/settings/mdr_enabled']
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
    ];

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param ModuleListInterface $moduleList
     * @param WriterInterface $configWriter
     * @param ConfigResource $configResource
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        ModuleListInterface $moduleList,
        WriterInterface $configWriter,
        ConfigResource $configResource
    ) {
        $this->encryptor = $encryptor;

        parent::__construct(
            $storeManager,
            $scopeConfig,
            $moduleList,
            $encryptor,
            $configWriter,
            $configResource
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
     * Get API KEY of the store
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return string|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAppKey(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->getConfig('app_key', $scopeId, $scope);
    }

    /**
     * Get API Secret key of the store
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return string|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSecret(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return (($secret = $this->getConfig('secret', $scopeId, $scope))) ? $this->encryptor->decrypt($secret) : null;
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
    public function isWidgetEnabled(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
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
    public function isCategoryBottomlineEnabled(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
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
    public function isBottomlineEnabled(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
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
    public function isBottomlineQnaEnabled(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return (bool)$this->getConfig('qna_enabled', $scopeId, $scope);
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
    public function isMdrEnabled(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return (bool)$this->getConfig('mdr_enabled', $scopeId, $scope);
    }

    /**
     * Find if APP Key and Secret is setup correctly
     *
     * @param int|null $scopeId
     * @param string $scope
     * @return boolean
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isAppKeyAndSecretSet(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
    {
        return $this->getAppKey($scopeId, $scope) && $this->getSecret($scopeId, $scope);
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
    public function isActivated(int $scopeId = null, string $scope = ScopeInterface::SCOPE_STORE): bool
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
     * Reset store credentials
     *
     * @param int|null $storeId
     * @param string|null  $scope
     * @return $this
     * @throws NoSuchEntityException
     */
    public function resetStoreCredentials($storeId = null, $scope = ScopeInterface::SCOPE_STORES)
    {
        $this->deleteConfig('yotpo_active', $scope, $storeId);
        $this->deleteConfig('app_key', $scope, $storeId);
        $this->deleteConfig('secret', $scope, $storeId);
        return $this;
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
}
