<?php

namespace Valentino\QuoteMergeMiddleware\Model;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Item\Processor;
use Valentino\QuoteMergeMiddleware\Api\QuoteMergeManagementInterface;

class QuoteMergeManagement implements QuoteMergeManagementInterface
{

    public function __construct(
        protected Processor $itemProcessor,
        protected SessionManagerInterface $sessionManager,
        protected \Magento\Customer\Model\Session $customerSession,
        protected CartRepositoryInterface $quoteRepository
    )
    {
    }

    /**
     * @inheritdoc
     *
     * @param mixed|string $quoteIds
     * @return mixed|string
     * @throws LocalizedException
     */
    public function merge($quoteIds)
    {
        
        $customerQuote = $this->quoteRepository->getForCustomer($this->customerSession->getCustomerId());
        $noMergedQuote = $this->quoteRepository->get($this->sessionManager->getCustomerActiveQuoteId());

        foreach ($noMergedQuote->getAllVisibleItems() as $item) {
            $found = false;
            foreach ($customerQuote->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setQty($quoteItem->getQty() + $item->getQty());
                    $this->itemProcessor->merge($item, $quoteItem);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $newItem = clone $item;
                $$customerQuote->addItem($newItem);
                if ($item->getHasChildren()) {
                    foreach ($item->getChildren() as $child) {
                        $newChild = clone $child;
                        $newChild->setParentItem($newItem);
                        $$customerQuote->addItem($newChild);
                    }
                }
            }
        }

        $this->quoteRepository->save(
            $customerQuote->collectTotals()
        );
    }
}
