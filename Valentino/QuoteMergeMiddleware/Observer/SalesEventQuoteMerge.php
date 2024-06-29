<?php

namespace Valentino\QuoteMergeMiddleware\Observer;

use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\CustomerData\ItemPoolInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Event\ObserverInterface;

class SalesEventQuoteMerge implements ObserverInterface
{
    public function __construct(
        private SessionManagerInterface $sessionManager,
        private Url $catalogUrl,
        private ItemPoolInterface $itemPoolInterface
    ){}

    public function execute(Observer $observer)
    {
        $currentQuote = $observer->getQuote();
        $items = $this->getRecentItems($currentQuote);
        $this->sessionManager->start();
        $this->sessionManager->setCustomerActiveQuoteId($currentQuote->getId());
        $this->sessionManager->setCustomerActiveCartItems($items);
        //$currentQuote->removeAllItems();
    }

    private function getRecentItems($quote)
    {
        $items = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            /* @var $item \Magento\Quote\Model\Quote\Item */
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product = $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (isset($products[$product->getId()])) {
                    $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($urlDataObject);
                }
            }

            $items[] = $this->itemPoolInterface->getItemData($item);
        }

        return $items;
    }
}
