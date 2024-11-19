<?php
/**
 * RepopulateCartUrlServiceInterface
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
namespace Blackbird\RepopulateCart\Api;

use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;

interface RepopulateCartUrlServiceInterface {
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return string
     */
    public function getFromQuote(Quote $quote): string;

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     */
    public function getFromOrder(OrderInterface $order): string;
}
