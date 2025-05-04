<?php
namespace Yotpo\Reviews\Controller\Adminhtml\External;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Backend\App\Action;

/**
 * Class Analytics - Controller to external analytics page
 */
class Analytics extends Action
{
    /**
     * @var AbstractReviews
     */
    private $abstractReviews;

    /**
     * Analytics constructor.
     * @param Context $context
     * @param AbstractReviews $abstractReviews
     */
    public function __construct(
        Context $context,
        AbstractReviews $abstractReviews
    ) {
        $this->abstractReviews = $abstractReviews;
        parent::__construct($context);
    }

    /**
     * Execute analytics controller
     *
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $appKey = $this->abstractReviews->initialize($this->getRequest());
        if ($appKey) {
            // phpcs:ignore
            $url = 'https://yap.yotpo.com/?utm_source=MagentoAdmin_ReportingAnalytics#/tools/conversions_dashboard/engagement';
        } else {
            $url = 'https://www.yotpo.com/integrations/
            adobe-commerce-magento/?utm_source=MagentoAdmin_ReportingAnalytics';
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($url);
    }
}
