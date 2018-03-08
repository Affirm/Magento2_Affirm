<?php

namespace Astound\Affirm\Block\System\Config\Form\Field;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;


/**
 * Class Date
 */
class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Core registry
     *
     * @var Registry $_coreRegistry
     */
    protected $_coreRegistry;

    /**
     * Module resource
     *
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Registry $coreRegistry
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->moduleResource = $moduleResource;
        parent::__construct($context, $data);
    }

    /**
     * Get element html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->moduleResource->getDbVersion('Astound_Affirm');
    }
}
