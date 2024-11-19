<?php
/**
 * RepopupulateCartUrlService
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);


namespace Blackbird\RepopulateCart\Model\Service;


use Blackbird\RepopulateCart\Api\RepopulateCartUrlServiceInterface;
use Magento\Framework\Url;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;

class RepopulateCartUrlService implements RepopulateCartUrlServiceInterface
{
    public function __construct(
       protected Url $urlHelper
    )
    {
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param $storeId
     * @return string
     * @throws \JsonException
     */
    public function getFromQuote(Quote $quote): string {
        $params = [
            'itemids' => [],
            'qties' => []
        ];

        $storeId = $quote->getStoreId();
        if (count($quote->getAllItems()) > 0) {
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductType() !== 'simple') {
                    continue;
                }
                $params['itemids'][] = $item->getProduct()->getId();
                $params['qties'][] = $item->getQty();
            }
        }

        return $this->urlHelper->getUrl('repopulate', [ '_scope' => $storeId, 'b64' => \base64_encode(\json_encode($params,
            JSON_THROW_ON_ERROR))]);
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     * @throws \JsonException
     */
    public function getFromOrder(OrderInterface $order): string {
        $items = $order->getItems();

        $productsInCart = [];
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            $url[$item->getId()]['id']               = $item->getData('product_id');
            $url[$item->getId()]['qty']              = $item->getData('qty') ?? $item->getData('qty_ordered');
            $url[$item->getId()]['super_attributes'] =
                ($item->getData('product_options')['info_buyRequest']['super_attribute']) ?? null;

            if ($url[$item->getId()]['super_attributes']) {
                $key = (key($url[$item->getId()]['super_attributes']));

                $productsInCart['itemIds'][] = $item->getData(
                        'product_id') . '-' . $key . '-' . $url[$item->getId()]['super_attributes'][$key];
            } else {
                $productsInCart['itemIds'][] = $item->getData('product_id');

            }
            $productsInCart['qties'][] = $item->getData('qty') ?? $item->getData('qty_ordered');
        }
        return $this->urlHelper->getUrl('repopulate', [ '_scope' => $order->getStoreId(), 'b64' => \base64_encode(\json_encode($productsInCart,
            JSON_THROW_ON_ERROR))]);
    }
}
