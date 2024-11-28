<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model\I18n;

use Magento\Framework\Component\ComponentRegistrar;
use Magefan\TranslationPlus\Model\PhrasesTranslations;

class Context extends \Magento\Setup\Module\I18n\Context
{
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar
    ) {
        parent::__construct($componentRegistrar);
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @param string $path
     * @return array
     */
    public function getContextByPath($path)
    {
        if ($value = $this->getComponentName(ComponentRegistrar::MODULE, $path)) {
            $type = self::CONTEXT_TYPE_MODULE;
        } elseif ($value = $this->getComponentName(ComponentRegistrar::THEME, $path)) {
            $type = self::CONTEXT_TYPE_THEME;
        } elseif ($value = strstr($path, '/lib/web/')) {
            $type = self::CONTEXT_TYPE_LIB;
            $value = ltrim($value, '/');
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid path given: "%s".', $path));
        }

        return [$type, $value . PhrasesTranslations::MODULE_PATH_SEPARATOR . $path];
    }

    /**
     * @param string $componentType
     * @param string $path
     * @return bool|int|string
     */
    private function getComponentName($componentType, $path)
    {
        foreach ($this->componentRegistrar->getPaths($componentType) as $componentName => $componentDir) {
            $componentDir .= '/';
            if (strpos($path, $componentDir) !== false) {
                return $componentName;
            }
        }
        return false;
    }
}
