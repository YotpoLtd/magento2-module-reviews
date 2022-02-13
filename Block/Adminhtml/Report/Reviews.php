<?php
namespace Yotpo\Reviews\Block\Adminhtml\Report;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\ScopeInterface;
use Yotpo\Reviews\Model\Sync\Reviews\Processor as YotpoReviewsApi;
use Yotpo\Reviews\Model\Config as YotpoConfig;
use Magento\Backend\Block\Template;
use Magento\Framework\View\Element\Template as ViewTemplate;

/**
 * Class Reviews - Block to display reviews metrics
 */
class Reviews extends Template
{
    /**
     * @var bool
     */
    private $initialized;

    /**
     * @var string
     */
    private $scope = ScopeInterface::SCOPE_STORE;

    /**
     * @var int
     */
    private $scopeId;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var string
     */
    private $appKey;

    /**
     * @var bool
     */
    private $isAppKeyAndSecretSet;

    /**
     * @var int
     */
    private $allStoreId;

    /**
     * @var array<int, mixed>
     */
    private $totals = [];

    /**
     * @var string
     */
    protected $_defaultPeriod = '30d';

    /**
     * @var string
     */
    protected $_template = 'Yotpo_Reviews::report/reviews.phtml';

    /**
     * @var YotpoConfig
     */
    private $yotpoConfig;

    /**
     * @var YotpoReviewsApi
     */
    private $yotpoApi;

    /**
     * Reviews constructor.
     * @param Context $context
     * @param YotpoConfig $yotpoConfig
     * @param YotpoReviewsApi $yotpoApi
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        YotpoConfig $yotpoConfig,
        YotpoReviewsApi $yotpoApi,
        array $data = []
    ) {
        $this->yotpoConfig = $yotpoConfig;
        $this->yotpoApi = $yotpoApi;
        parent::__construct($context, $data);
    }

    /**
     * Initialize the global variables
     *
     * @return void
     * @throws LocalizedException
     */
    private function initialize()
    {
        if ($this->initialized) {
            return;
        }
        $this->initialized = true;

        if (!($storeId = $this->getRequest()->getParam(ScopeInterface::SCOPE_STORE, 0))) {
            $websiteId = $this->yotpoConfig->getDefaultWebsiteId();
            $storeId = $this->yotpoConfig->getDefaultStoreId($websiteId);
        }
        $this->allStoreId = $storeId;

        $this->scopeId = ($this->allStoreId) ? $this->allStoreId : null;

        $this->isEnabled = $this->yotpoConfig->isEnabled($storeId);
        $this->isAppKeyAndSecretSet = $this->yotpoConfig->filterDisabledStoreId($storeId);
        $this->appKey = ($this->scopeId) ? $this->yotpoConfig->getAppKey($this->scopeId, $this->scope) : null;
    }

    /**
     * Get request value of period
     *
     * @param string|null $default
     * @return mixed
     */
    public function getPeriod(string $default = null)
    {
        return $this->getRequest()->getParam('period', ($default ?: $this->_defaultPeriod));
    }

    /**
     * Check if Yotpo is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * Get APP Key
     *
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->appKey ?: '';
    }

    /**
     * Check if App Key and Secret is setup correctly
     * @return bool
     */
    public function isAppKeyAndSecretSet(): bool
    {
        return $this->isAppKeyAndSecretSet;
    }

    /**
     * Get Reviews Metrics Totals
     *
     * @return array<int, mixed>
     */
    public function getTotals()
    {
        return $this->totals;
    }

    /**
     * Add Totals
     *
     * @param string $label
     * @param mixed $value
     * @param string $class
     * @return void
     */
    private function addTotal(string $label, $value, string $class = ""): void
    {
        $this->totals[] = ['label' => $label, 'value' => $value, 'class' => $class];
    }

    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param int $customStart
     * @param int $customEnd
     * @param bool $returnObjects
     * @return array<string, string|null>|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getDateRange(string $range, $customStart, $customEnd, $returnObjects = false)
    {
        $dateEnd = new \DateTime();
        $dateStart = new \DateTime();

        // go to the end of a day
        //$dateEnd->setTime(23, 59, 59);
        //$dateStart->setTime(0, 0, 0);

        switch ($range) {
            case '24h':
                $dateEnd = new \DateTime();
                $dateEnd->modify('+1 hour');
                $dateStart = clone $dateEnd;
                $dateStart->modify('-1 day');
                break;
            case '1d':
                $dateStart->modify('-1 days');
                break;

            case '7d':
                $dateStart->modify('-6 days');
                break;

            case '30d':
                $dateStart->modify('-30 days');
                break;

            case '1m':
                $dateStart->setDate(
                    (int)$dateStart->format('Y'),
                    (int)$dateStart->format('m'),
                    $this->yotpoConfig->getConfig('reports/dashboard/mtd_start')
                );
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(
                    ',',
                    $this->yotpoConfig->getConfig('reports/dashboard/ytd_start')
                );
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setDate((int)$dateStart->format('Y'), $startMonth, $startDay);
                if ($range == '2y') {
                    $dateStart->modify('-1 year');
                }
                break;

            case 'all':
                $dateStart->modify('-1000 years');
                break;
        }

        if ($returnObjects) {
            return [$dateStart, $dateEnd];
        } else {
            return ['from' => $dateStart, 'to' => $dateEnd, 'datetime' => true];
        }
    }

    /**
     * Layout preparation
     *
     * @return $this|void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        $this->initialize();
        $dateRange = $this->getDateRange($this->getPeriod(), 0, 0, true);

        $metrics = $this->yotpoApi->getMetrics(
            $this->allStoreId,
            $dateRange[0]->format(DateTime::DATETIME_PHP_FORMAT),
            $dateRange[1]->format(DateTime::DATETIME_PHP_FORMAT)
        );

        if (!isset($metrics['emails_sent'])) {
            $emailsSent = "-";
        } elseif ($metrics['emails_sent'] > 999999) {
            $emailsSent = number_format((float)($metrics['emails_sent']/1000000), 1, '.', "") . 'M';
        } elseif ($metrics['emails_sent'] > 99999) {
            $emailsSent = number_format((float)($metrics['emails_sent']/1000), 0, '.', "") . 'K';
        } elseif ($metrics['emails_sent'] > 999) {
            $emailsSent = number_format((float)($metrics['emails_sent']/1000), 1, '.', "") . 'K';
        } else {
            $emailsSent = round((float)$metrics['emails_sent'], 2);
        }

        $this->addTotal(__('Emails Sent'), $emailsSent, 'yotpo-totals-emails-sent');
        $this->addTotal(
            __('Avg. Star Rating'),
            (isset($metrics['star_rating'])) ? round((float)$metrics['star_rating'], 2) : '-',
            'yotpo-totals-star-rating'
        );
        $this->addTotal(
            __('Collected Reviews'),
            (isset($metrics['total_reviews'])) ? round((float)$metrics['total_reviews'], 2) : '-',
            'yotpo-totals-total-reviews'
        );
        $this->addTotal(
            __('Collected Photos'),
            (isset($metrics['photos_generated'])) ? round((float)$metrics['photos_generated'], 2) : '-',
            'yotpo-totals-photos-generated'
        );
        $this->addTotal(
            __('Published Reviews'),
            (isset($metrics['published_reviews'])) ? round((float)$metrics['published_reviews'], 2) : '-',
            'yotpo-totals-published-reviews'
        );
        $this->addTotal(
            __('Engagement Rate'),
            (isset($metrics['engagement_rate'])) ? round((float)$metrics['engagement_rate'], 2) . '%' : '-',
            'yotpo-totals-engagement-rate'
        );
    }

    /**
     * Generate yotpo button html
     *
     * @param string $utm
     * @return string
     * @throws LocalizedException
     */
    public function getLounchYotpoButtonHtml(string $utm = 'MagentoAdmin_Dashboard'): string
    {
        $this->initialize();
        /**
         * @var ViewTemplate $button
         */
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        $button->setData(
            [
            'id' => 'launch_yotpo_button',
            'class' => 'launch-yotpo-button yotpo-cta-add-arrow',
            ]
        );
        if (!($appKey = $this->getAppKey())) {
            $button->setLabel(__('Get Started'));
            $button->setOnClick(
                "window.open('https://www.yotpo.com/integrations/magento/?utm_source={$utm}','_blank');"
            );
        } else {
            $button->setLabel(__('Launch Yotpo'));
            $button->setOnClick(
                "window.open('https://reviews.yotpo.com','_blank');"
            );
        }

        return $button->toHtml();
    }

    /**
     * Get url for Yotpo configuration
     *
     * @return string
     * @throws LocalizedException
     */
    public function getYotpoConfigUrl(): string
    {
        $this->initialize();
        $params = ['section' => 'yotpo_core'];
        if ($this->scope) {
            $params[$this->scope] = $this->scopeId;
        }
        return $this->_urlBuilder->getUrl('adminhtml/system_config/edit', $params);
    }

    /**
     * Periods limit options
     *
     * @return string[]
     */
    public function getPeriods(): array
    {
        return [
            '1d' => 'Last Day',
            '7d' => 'Last 7 Days',
            '30d' => 'Last 30 Days',
            'all' => 'All Time',
        ];
    }
}
