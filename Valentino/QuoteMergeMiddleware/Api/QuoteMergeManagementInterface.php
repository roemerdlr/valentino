<?php
/**
 *
 */

namespace Valentino\QuoteMergeMiddleware\Api;

/**
 * Interface for managing customers accounts.
 * @api
 */
interface QuoteMergeManagementInterface
{

    /**
     * Check if given email is associated with a customer account in given website.
     *
     * @param mixed|string $quoteIds
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function merge($quoteIds);

}
