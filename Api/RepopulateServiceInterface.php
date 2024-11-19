<?php
/**
 * PopulateServiceInterface
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
namespace Blackbird\RepopulateCart\Api;

interface RepopulateServiceInterface {
    /**
     * @param array $products
     * @return string
     */
    public function execute(array $products): string;

    /**
     * @param string $sku
     * @return mixed
     */
    public function executeFromSku(string $sku);
}
