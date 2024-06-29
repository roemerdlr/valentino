<?php
/**
 *
 */
declare(strict_types=1);

namespace Valentino\QuoteMergeMiddleware\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Session\SessionManagerInterface;
class CustomerActiveQuote implements SectionSourceInterface
{

    public function __construct(
        private SessionManagerInterface $sessionManager
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $items = [];
        if ($this->sessionManager->getCustomerActiveCartItems()) {
            $items = $this->sessionManager->getCustomerActiveCartItems();
        }

        return ['items' => $items];
    }
}
