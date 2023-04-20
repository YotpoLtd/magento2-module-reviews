<?php

namespace Yotpo\Reviews\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SyncWidgetV3InstanceIdsButton
 *
 * Sync new or updated widget v3 instances.
 */
class SyncWidgetV3InstanceIdsButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Yotpo_Reviews::system/config/sync_widget_v3_instance_ids_button.phtml';

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element) // @codingStandardsIgnoreLine - required by parent class
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for widget v3 instance ids button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('yotpo_reviews/syncwidgetv3instanceids/index');
    }

    /**
     * Generate Sync Widget V3 Instance Ids button HTML
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(/** @phpstan-ignore-line */
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
                'id'    => 'yotpo_sync_widget_v3_instance_ids_btn',
                'label' => __('Sync Widget V3 instances'),
            ]);

        return $button->toHtml();
    }

    /**
     * Get current store scope
     *
     * @return int|mixed
     */
    public function getStoreScope()
    {
        return $this->getRequest()->getParam('store') ? : 0;
    }

    /**
     * Get current website scope
     *
     * @return int|mixed
     */
    public function getWebsiteScope()
    {
        return $this->getRequest()->getParam('website') ? : 0;
    }
}
