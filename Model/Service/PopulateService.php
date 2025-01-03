<?php
/**
 * PopulateService
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);


namespace Blackbird\RepopulateCart\Model\Service;

use Blackbird\RepopulateCart\Api\RepopulateServiceInterface;
use Blackbird\RepopulateCart\Model\Config\RepopulateCartConfig;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\DataObject;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

class PopulateService implements RepopulateServiceInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected readonly RepopulateCartConfig $repopulateCartConfig,
        protected Session $session,
        protected FormKey $formKey,
        protected QuoteRepository $quoteRepository,
        protected ManagerInterface $messageManager,
        protected CustomerCart $cart,
        protected CartRepositoryInterface $cartRepository,
        protected Configurable $configurable
    ) {
    }

    /**
     * @param array $products
     * @return string
     */
    public function execute(array $products): string
    {
        $redirectUrl = $this->repopulateCartConfig->getUrlRedirect();
        $quote = $this->cart->getQuote();

        if (empty($products)) {
            $this->messageManager->addErrorMessage(__('No cart could be retrieved from this link.'));
            return '/';
        }

        if ($this->isDuplicatedCart($quote, $products)) {
            $this->messageManager->addWarningMessage(__('Items that you are trying to add are already in your cart.'));
            return $redirectUrl;
        }

        if ($this->repopulateCartConfig->isReplaceAction() && $this->cart->getItemsCount()) {
            foreach ($this->cart->getItems() as $item) {
                $this->cart->removeItem($item->getId());
            }
        }

        foreach ($products as $id => $qty) {
            try {
                $productInfos = explode('-', (string)$id);
                $parameters['product'] = $parameters['item'] = $productInfos[0];

                // If there is too many informations it's a configurable product
                if (count($productInfos) > 1) {
                    $parameters['super_attribute'] = [
                        $productInfos[1] => $productInfos[2]
                    ];
                }

                $parameters['qty'] = $qty;
                $parameters['form_key'] = $this->formKey->getFormKey();
                $this->cart->addProduct($parameters['product'], new DataObject($parameters));
                $this->productRepository->cleanCache();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error during repopulate cart.'));
                return '/';
            }
        }

        $this->cart->save();
        $quote = $this->cart->getQuote();
        $quote->setData('repopulated', true);
        $this->cartRepository->save($quote);
        return $redirectUrl;
    }

    /**
     * @param $quote
     * @param $items
     * @return bool
     */
    protected function isDuplicatedCart($quote, $items): bool
    {
        if (!$quote->getAllItems()) {
            return false;
        }

        foreach ($quote->getAllItems() as $item) {
            if ($item->getProductType() !== 'simple') {
                continue;
            }
            $productsInCart[$item->getProduct()->getId()] = $item->getData('qty');
        }

        if (empty($productsInCart)) {
            return false;
        }

        foreach ($items as $id => $qty) {
            if (!isset($productsInCart[$id]) || ($productsInCart[$id] < $qty)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $sku
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function executeFromSku(string $sku)
    {
        $redirectUrl = $this->repopulateCartConfig->getUrlRedirect();
        $product = $this->productRepository->get($sku);

        if ($this->repopulateCartConfig->isReplaceAction() && $this->cart->getItemsCount()) {
            foreach ($this->cart->getItems() as $item) {
                $this->cart->removeItem($item->getId());
            }
        }

        //check if product as a parent product for configurable products
        if ($product->getTypeId() === 'simple') {
            $parentProducts = $this->configurable->getParentIdsByChild($product->getId());
            if (count($parentProducts)) {
                $parentProduct = $this->productRepository->getById($parentProducts[0]);
                $attributeId = array_key_first($this->configurable->getUsedProductAttributes($parentProduct) ?? []);
                $configurableOptions = $this->configurable->getConfigurableOptions($parentProduct);
                foreach ($configurableOptions as $configurableAttributeOption) {
                    foreach ($configurableAttributeOption as $configurableOption) {
                        if ($configurableOption['sku'] === $product->getSku()) {
                            $optionId = $configurableOption['value_index'];
                            break 2;
                        }
                    }
                }

                $parameters = $this->getParameters($parentProduct, $attributeId , $optionId);
                $this->cart->addProduct($parentProduct, new DataObject($parameters));
            } else {
                $parameters = $this->getParameters($product);
                $this->cart->addProduct($product, new DataObject($parameters));
            }
        }

        $this->cart->save();
        $quote = $this->cart->getQuote();
        $quote->setData('repopulated', true);
        $this->cartRepository->save($quote);
        return $redirectUrl;
    }

    public function getParameters(ProductInterface $product, $attributeId = null, $optionId = null): array
    {
        $parameters = [
            'product_id' => $product->getId(),
            'form_key' => $this->formKey->getFormKey(),
            'sku' => $product->getSku(),
            'qty' => 1,
            'product' => $product->getId(),
        ];

        if ($attributeId && $optionId) {
            $parameters = array_merge([
                'selected_configurable_option' => 1,
                'super_attribute' => [$attributeId => $optionId]
            ], $parameters);
        }

        return $parameters;
    }
}