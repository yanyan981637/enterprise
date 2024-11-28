<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Model\I18n;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\I18n\FilesCollector;
use Magefan\TranslationPlus\Model\I18n\Context;

class ServiceLocator extends \Magento\Setup\Module\I18n\ServiceLocator
{
    /**
     * Domain abstract factory
     *
     * @var \Magento\Setup\Module\I18n\Factory
     */
    private static $_factory;

    /**
     * @var
     */
    private static $_context;

    /**
     * Dictionary generator
     *
     * @var \Magento\Setup\Module\I18n\Dictionary\Generator
     */
    private static $_dictionaryGenerator;

    /**
     * Pack generator
     *
     * @var \Magento\Setup\Module\I18n\Pack\Generator
     */
    private static $_packGenerator;

    public static function getDictionaryGenerator()
    {
        if (null === self::$_dictionaryGenerator) {

            $filesCollector = new FilesCollector();

            $phraseCollector = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer());
            $adapters = [
                'php' => new \Magento\Setup\Module\I18n\Parser\Adapter\Php($phraseCollector),
                'html' => new \Magento\Setup\Module\I18n\Parser\Adapter\Html(),
                'js' => new \Magento\Setup\Module\I18n\Parser\Adapter\Js(),
                'xml' => new \Magento\Setup\Module\I18n\Parser\Adapter\Xml(),
            ];

            $parser = new \Magento\Setup\Module\I18n\Parser\Parser($filesCollector, self::_getFactory());

            $parserContextual = new \Magento\Setup\Module\I18n\Parser\Contextual($filesCollector, self::_getFactory(), self::_getContext());

            foreach ($adapters as $type => $adapter) {
                $parser->addAdapter($type, $adapter);
                $parserContextual->addAdapter($type, $adapter);
            }

            self::$_dictionaryGenerator = new \Magento\Setup\Module\I18n\Dictionary\Generator(
                $parser,
                $parserContextual,
                self::_getFactory(),
                new \Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory()
            );
        }

        return self::$_dictionaryGenerator;
    }

    /**
     * Get factory
     *
     * @return \Magento\Setup\Module\I18n\Factory
     */
    private static function _getFactory()
    {
        if (null === self::$_factory) {
            self::$_factory = new \Magento\Setup\Module\I18n\Factory();
        }
        return self::$_factory;
    }

    /**
     * @return \Magefan\TranslationPlus\Model\I18n\Context|\Magento\Setup\Module\I18n\Context|\Magento\Setup\Module\I18n\Factory
     */
    private static function _getContext()
    {
        if (null === self::$_context) {
            self::$_context = new Context(new ComponentRegistrar());
        }
        return self::$_context;
    }
}
