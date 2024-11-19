<?php
/**
 * SalesEventObserver
 *
 * @copyright Copyright © 2022 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);


namespace Blackbird\RepopulateCart\Observer;


use Blackbird\RepopulateCart\Model\Config\RepopulateCartConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class SalesEventQuoteMerge implements ObserverInterface
{
    public function __construct(
        protected readonly RepopulateCartConfig $repopulateCartConfig
    ) {
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Zend_Log_Exception
     */
    public function execute(Observer $observer): void
    {

        /** @var  Quote $targetQuote */
        $targetQuote = $observer->getData('quote');
        /** @var  Quote $sourceQuote */
        $sourceQuote = $observer->getData('source');

        if ($this->repopulateCartConfig->isMergeAction()) {
            //If cart is from repopulatecart do not duplicate products
            if (!empty($sourceQuote->getData('repopulated'))) {
                $customerQuote = [];
                if (count($targetQuote->getAllItems()) > 0) {
                    foreach ($targetQuote->getAllItems() as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        $customerQuote[$item->getProduct()->getId()] = $item->getId();
                    }
                }

                if (!empty($customerQuote) && count($sourceQuote->getAllItems()) > 0) {
                    foreach ($sourceQuote->getAllItems() as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (isset($customerQuote[$item->getProduct()->getId()])) {
                            $targetQuote->removeItem($customerQuote[$item->getProduct()->getId()]);
                        }
                    }
                }
            }
        } elseif (!empty($sourceQuote->getData('repopulated')) && count($targetQuote->getAllItems()) > 0) {
            foreach ($targetQuote->getAllItems() as $item) {
                $targetQuote->removeItem($item->getId());
            }
        }
    }
}
