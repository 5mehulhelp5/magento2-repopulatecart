<?php
/**
 * RepopulateCartConfig
 *
 * @copyright Copyright © 2024 Blackbird. All rights reserved.
 * @author    emilie (Blackbird Team)
 */
declare(strict_types=1);

namespace Blackbird\RepopulateCart\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class RepopulateCartConfig
{
    public function __construct(
        protected readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public const CONFIG_REPOPULATE_ACTION = 'repopulate_cart/general/action';

    public const CONFIG_REPOPULATE_REDIRECT_URL = 'repopulate_cart/general/redirect_url';

    public const REPOPULATE_ACTION_REPLACE = 'replace';

    public const REPOPULATE_ACTION_MERGE = 'merge';

    /**
     * @param string $configPath
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getConfig(string $configPath, string $scopeType = ScopeInterface::SCOPE_STORE): ?string
    {
        return $this->scopeConfig->getValue(
            $configPath,
            $scopeType
        );
    }

    /**
     * @return string
     */
    public function getUrlRedirect(): string
    {
        return $this->getConfig(self::CONFIG_REPOPULATE_REDIRECT_URL) ?? '/';
    }

    /**
     * @return string
     */
    public function getAction(): string {
        return $this->getConfig(self::CONFIG_REPOPULATE_ACTION);
    }

    /**
     * @return bool
     */
    public function isReplaceAction(): bool{
        return $this->getAction() === self::REPOPULATE_ACTION_REPLACE;
    }

    /**
     * @return bool
     */
    public function isMergeAction(): bool{
        return $this->getAction() === self::REPOPULATE_ACTION_MERGE;
    }
}
