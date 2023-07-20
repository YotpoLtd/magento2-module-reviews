<?php
namespace Yotpo\Reviews\Model;

class RichSnippet extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Standard model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\RichSnippet::class);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $expirationTime = $this->getExpirationTime();
        if (is_null($expirationTime)) {
            return false;
        }

        return $expirationTime > time();
    }
}
