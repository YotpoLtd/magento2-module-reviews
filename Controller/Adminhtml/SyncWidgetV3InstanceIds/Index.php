<?php

namespace Yotpo\Reviews\Controller\Adminhtml\SyncWidgetV3InstanceIds;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Yotpo\Reviews\Model\Sync\WidgetV3InstanceIds\Processor;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Class Index
 * Sync widget v3 instances
 */
class Index extends Action
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Processor
     */
    protected $widgetV3InstanceIdsProcessor;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param Processor $subscriptionProcessor
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     * @param JsonFactory $jsonResultFactory
     */
    public function __construct(
        Context $context,
        Processor $widgetV3InstanceIdsProcessor,
        StoreWebsiteRelationInterface $storeWebsiteRelation,
        JsonFactory $jsonResultFactory
    ) {
        $this->messageManager = $context->getMessageManager();
        $this->widgetV3InstanceIdsProcessor = $widgetV3InstanceIdsProcessor;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
        $this->jsonResultFactory = $jsonResultFactory;
        parent::__construct($context);
    }

    /**
     * Process widget v3 instances sync
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $storeIds = [];
            $storeId = $this->_request->getParam('store');
            $websiteId = $this->_request->getParam('website');
            if ($storeId && $storeId !== 0) {
                $storeIds[] = $storeId;
                $this->widgetV3InstanceIdsProcessor->process($storeIds);
            } elseif ($websiteId && $websiteId !== 0) {
                $this->widgetV3InstanceIdsProcessor
                   ->process($this->storeWebsiteRelation->getStoreByWebsiteId($websiteId));
            } else {
                $this->widgetV3InstanceIdsProcessor->process();
            }
        } catch (NoSuchEntityException | LocalizedException $e) {
            $this->messageManager
                ->addErrorMessage(__('Something went wrong during widget V3 instances sync - ' . $e->getMessage()));
        }
        $result = $this->jsonResultFactory->create();
        $messages = $this->widgetV3InstanceIdsProcessor->getMessages();
        return $result->setData(['status' => $messages]);
    }
}
