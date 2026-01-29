<?php

namespace Yotpo\Reviews\Model\Sync\Reviews;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Yotpo\Reviews\Model\Sync\Main as YotpoSyncMain;
use Yotpo\Reviews\Model\Config as YotpoConfig;
use Yotpo\Core\Model\AbstractJobs;
use Yotpo\Reviews\Model\Logger as ReviewsLogger;

/**
 * Class Processor - Sync Reviews related APIs
 */
class Processor extends AbstractJobs
{
    /**
     * @var YotpoSyncMain
     */
    protected $yotpoSyncMain;

    /**
     * @var YotpoConfig
     */
    protected $yotpoConfig;

    /**
     * @var ReviewsLogger
     */
    protected $logger;

    /**
     * Processor constructor.
     * @param AppEmulation $appEmulation
     * @param ResourceConnection $resourceConnection
     * @param YotpoSyncMain $yotpoSyncMain
     * @param YotpoConfig $yotpoConfig
     * @param ReviewsLogger $logger
     */
    public function __construct(
        AppEmulation $appEmulation,
        ResourceConnection $resourceConnection,
        YotpoSyncMain $yotpoSyncMain,
        YotpoConfig $yotpoConfig,
        ReviewsLogger $logger
    ) {
        $this->yotpoSyncMain = $yotpoSyncMain;
        $this->yotpoConfig = $yotpoConfig;
        $this->logger = $logger;
        parent::__construct($appEmulation, $resourceConnection);
    }

    /**
     * Get Reviews Metrics
     *
     * @param int $storeId
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array<mixed>
     */
    public function getMetrics(int $storeId, ?string $fromDate = null, ?string $toDate = null): array
    {
        $return = [];
        try {
            $this->emulateFrontendArea($storeId);

            $data = [
                'utoken'            => '',
                'platform'          => 'magento2',
                'extension_version' => $this->yotpoConfig->getModuleVersion(),
            ];
            if ($fromDate) {
                $data['since'] = $fromDate;
            }
            if ($toDate) {
                $data['until'] = $toDate;
            }

            $endPoint = $this->yotpoConfig->getEndpoint('metrics');
            $data['entityLog'] = 'general';

            $response = $this->yotpoSyncMain->sync('GET', $endPoint, $data);

            if ($response['is_success']) {
                $return = (array)$response['response']['response'];
            }

            $this->stopEnvironmentEmulation();
            $this->logger->info(__('API Issue - Reason is %1', 323232));
        } catch (\Exception $e) {
            $this->logger->info(__('API Issue - Reason is %1', $e->getMessage()));
        }

        return $return;
    }
}
