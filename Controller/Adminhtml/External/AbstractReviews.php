<?php
namespace Yotpo\Reviews\Controller\Adminhtml\External;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Yotpo\Reviews\Model\Config as YotpoConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AbstractReviews - Common methods to External Reviews controller
 */
class AbstractReviews
{
    /**
     * @var YotpoConfig
     */
    private $yotpoConfig;

    /**
     * AbstractReviews constructor.
     * @param YotpoConfig $yotpoConfig
     */
    public function __construct(
        YotpoConfig $yotpoConfig
    ) {
        $this->yotpoConfig = $yotpoConfig;
    }

    /**
     * Initialize the global variables
     *
     * @param RequestInterface $request
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function initialize(RequestInterface $request): string
    {
        $appKey = '';
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if (($scopeId = $request->getParam("store", null))) {
            $scope = ScopeInterface::SCOPE_STORE;
        } elseif (($scopeId = $request->getParam("website", null))) {
            $scope = ScopeInterface::SCOPE_WEBSITE;
        }
        if (!$this->yotpoConfig->isActivated($scopeId, $scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
            foreach ((array)$this->yotpoConfig->getAllStoreIds(true) as $storeId) {
                if ($this->yotpoConfig->isActivated($storeId, $scope)) {
                    $appKey = $this->yotpoConfig->getAppKey($storeId, $scope);
                    break;
                }
            }
        } else {
            $appKey = $this->yotpoConfig->getAppKey($scopeId, $scope);
        }

        return $appKey;
    }
}
