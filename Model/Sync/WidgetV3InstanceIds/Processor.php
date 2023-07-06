<?php

namespace Yotpo\Reviews\Model\Sync\WidgetV3InstanceIds;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Yotpo\Reviews\Model\Config as YotpoConfig;
use Yotpo\Reviews\Model\Logger as ReviewsLogger;
use Yotpo\Core\Model\AbstractJobs;
use Yotpo\Reviews\Model\Sync\Main as YotpoSyncMain;

class Processor extends AbstractJobs
{
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
     * @var array <mixed>
     */
    protected $messages = ['success' => [], 'error' => []];

    /**
     * Processor constructor.
     * @param AppEmulation $appEmulation
     * @param ResourceConnection $resourceConnection
     * @param YotpoSyncMain $yotpoSyncMain
     * @param SerializerInterface $serializer
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
     * Process widget v3 instance ids
     * @param array <mixed> $storeIds
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function process($storeIds = [])
    {
        if (!$storeIds) {
            $storeIds = $this->yotpoConfig->getAllStoreIds(false);
        }
        /** @phpstan-ignore-next-line */
        foreach ($storeIds as $storeId) {
            $this->emulateFrontendArea($storeId);
            if (!$this->yotpoConfig->isEnabled()) {
                $this->addMessage(
                    'error',
                    __(
                        'Yotpo is disabled for Magento Store ID: %1, Name: %2',
                        $storeId,
                        $this->yotpoConfig->getStoreName($storeId)
                    )
                );
                $this->stopEnvironmentEmulation();
                continue;
            }


            $this->logger->info(
                __(
                    'Process widget v3 instance ids for Magento Store ID: %1, Name: %2',
                    $storeId,
                    $this->yotpoConfig->getStoreName($storeId)
                ),
                []
            );

            $this->processWidgetV3InstanceIds();
            $this->stopEnvironmentEmulation();
        }
    }

    /**
     * Process widget v3 instance ids
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function processWidgetV3InstanceIds()
    {
        $storeId = $this->yotpoConfig->getStoreId();
        $currentTime = date('Y-m-d H:i:s');
        //call to API
        $response = $this->getWidgetV3InstanceIds();
        $storeCode = $this->yotpoConfig->getStoreName($storeId);
        $this->updateLastSyncDate($currentTime);
        if ($response->getData('is_success')) {
            $responseData = $response->getData('response');
            $widgetV3InstanceIds = [];
            foreach ($responseData['widget_instances'] as $widgetInstance) {
                if (!empty($widgetInstance['widget_type_name']) && !empty($widgetInstance['widget_instance_id'])) {
                    $widgetV3InstanceIds[$widgetInstance['widget_type_name']] = $widgetInstance['widget_instance_id'];
                }
            }

            $serializedData = json_encode($widgetV3InstanceIds);
            $this->yotpoConfig->saveConfig('sync_widget_v3_instance_ids_data', (string)$serializedData);
            $this->logger->info('Widget v3 instances id sync - success', []);
            $this->addMessage(
                'success',
                __(
                    'Widget synced for Adobe Commerce Store ID: %1, Name: %2. <br/><br/>Please clear your cache to finish.',
                    $storeId,
                    $storeCode
                )
            );
        } else {
            $this->addMessage(
                'error',
                __(
                    'Store not found at API for Magento Store ID: %1, Name: %2',
                    $storeId,
                    $storeCode
                )
            );
        }
    }

    /**
     * Api call to get all widget v3 instance ids
     *
     * @return DataObject
     * @throws NoSuchEntityException
     */
    public function getWidgetV3InstanceIds()
    {
        $url = $this->yotpoConfig->getEndpoint('api_v2_widgets');
        return $this->yotpoSyncMain->sync($this->yotpoConfig::METHOD_GET, $url, ['utoken' => true]);
    }

    /**
     * Updates the last sync date to the database
     *
     * @param string $currentTime
     * @return void
     * @throws NoSuchEntityException
     */
    public function updateLastSyncDate($currentTime)
    {
        $this->yotpoConfig->saveConfig('widget_v3_instance_ids_last_sync_time', $currentTime);
    }

    /**
     * @param string $flag
     * @param string $message
     * @return void
     */
    public function addMessage($flag, $message = '')
    {
        if ($flag == 'success') {
            $this->messages['success'][] = $message;
        }
        if ($flag == 'error') {
            $this->messages['error'][] = $message;
        }
    }

    /**
     * @return array <mixed>
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
