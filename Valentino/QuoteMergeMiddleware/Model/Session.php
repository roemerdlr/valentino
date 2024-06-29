<?php
/**
 *
 */
namespace Valentino\QuoteMergeMiddleware\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;

class Session extends \Magento\Checkout\Model\Session
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        LoggerInterface $logger = null,
        private \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        private \Magento\Checkout\CustomerData\ItemPoolInterface $itemPoolInterface,
        private SessionManagerInterface $sessionManager,

    ) {
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $orderFactory,
            $customerSession,
            $quoteRepository,
            $remoteAddress,
            $eventManager,
            $storeManager,
            $customerRepository,
            $quoteIdMaskFactory,
            $quoteFactory,
            $logger
        );
    }

    /**
     * Load data for customer quote and merge with current quote
     *
     * @return $this
     */
    public function loadCustomerQuote()
    {
        if (!$this->_customerSession->getCustomerId()) {
            return $this;
        }

        $this->_eventManager->dispatch('load_customer_quote_before', ['checkout_session' => $this]);

        try {
            $customerQuote = $this->quoteRepository->getForCustomer($this->_customerSession->getCustomerId());
        } catch (NoSuchEntityException $e) {
            $customerQuote = $this->quoteFactory->create();
        }

        $customerQuote->setStoreId($this->_storeManager->getStore()->getId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {

                $items = $this->getRecentItems($customerQuote);
                $this->sessionManager->start();
                $this->sessionManager->setCustomerActiveQuoteId($customerQuote->getId());
                $this->sessionManager->setCustomerActiveCartItems($items);

                $quote = $this->getQuote();
                $quote->setCustomerIsGuest(0);
                $quote->updateCustomerData($customerQuote->getCustomer());
                $this->quoteRepository->save(
                    $quote->collectTotals()
                );
                $customerQuote = $quote;
            }

            if ($this->_quote) {
                $this->quoteRepository->delete($this->_quote);
            }

            $this->setQuoteId($customerQuote->getId());

            $this->_quote = $customerQuote;
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer($this->_customerSession->getCustomerDataObject())
                ->setCustomerIsGuest(0)
                ->setTotalsCollectedFlag(false)
                ->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }
        return $this;
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
