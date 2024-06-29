<?php

namespace Valentino\QuoteMergeMiddleware\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template\Context;
class QuoteMergePopup extends Template {

    const XML_PATH_QUOTE_MERGE_MIDDLEWARE = 'quote_middleware/general/enable';

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getEnable() {
        return $this->configProvider->getValue(self::XML_PATH_QUOTE_MERGE_MIDDLEWARE, ScopeInterface::SCOPE_STORE);
    }
}
