# Blackbird_RepopulateCart

The **Blackbird_RepopulateCart** module for Magento allows you to easily add products to the cart directly from a URL. This can be helpful for promotional links, order fulfillment, or custom checkout flows.

## Features

1. **Add Simple Products by ID and Quantity**  
   You can add multiple simple products to the cart by using a URL with specific item IDs and quantities.  
   Example URL:  
   https://yourstore.com/repopulate/index/index/itemIds/3584;23698/qties/1;3

In this example, product IDs `3584` and `23698` will be added to the cart with quantities `1` and `3`, respectively.

2. **Add Products by SKU**  
   Products, either simple or configurable, can be added to the cart using their SKU in the URL.  
   Example URL for a simple or configurable product:  
   https://yourstore.com/repopulate/add/sku/SPCKU00304-M000-XXS

This URL will add the product with SKU `SPCKU00304-M000-XXS` to the cart.

3. **RepopulateCartUrlServiceInterface**  
   The module also provides a service interface, **RepopulateCartUrlServiceInterface**, that can be used to generate URLs dynamically. 
   You can create URLs from an existing order or cart, which can be useful for custom integrations, marketing campaigns, or customer-specific checkout flows.

## Installation

**Zip Package:**

Unzip the package in app/code/Blackbird/RepopulateCart, from the root of your Magento instance.

**Composer Package:**

```
composer require blackbird/repopulatecart
```

### Install the module

Go to your Magento root, then run the following Magento command:

```
php bin/magento setup:upgrade
```

**If you are in production mode, do not forget to recompile and redeploy the static resources, or to use the `--keep-generated` option.**

## Contact

For further information, contact us:

- by email: hello@bird.eu
- or by form: [https://black.bird.eu/en/contacts/](https://black.bird.eu/contacts/)

## Authors

- **Emilie Wittmann** - *Maintainer* - [It's me!](https://github.com/emilie-blackbird)
- **Blackbird Team** - *Contributor* - [They're awesome!](https://github.com/blackbird-agency)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

***That's all folks!***
