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
 * Class Reviews - Controller to External Reviews page
 */
class Reviews extends Action
{

    /**
     * @var AbstractReviews
     */
    protected $abstractReviews;

    /**
     * Reviews constructor.
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
     * Execute the External Review controller
     *
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $appKey = $this->abstractReviews->initialize($this->getRequest());
        if ($appKey) {
            $url = 'https://yap.yotpo.com/?utm_source=MagentoAdmin_ReportingReviews#/moderation/reviews';
        } else {
            $url = 'https://www.yotpo.com/integrations/
            adobe-commerce-magento/?utm_source=MagentoAdmin_ReportingReviews';
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setUrl($url);
    }
}
