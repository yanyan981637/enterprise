<?php

namespace Magezon\Blog\Model\Page\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;

class PageLayout extends \Magento\Cms\Model\Page\Source\PageLayout
{
    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;

    /**
     * @var array
     * @deprecated 103.0.1 since the cache is now handled by \Magento\Theme\Model\PageLayout\Config\Builder::$configFiles
     */
    protected $options;

    /**
     * Constructor
     *
     * @param BuilderInterface $pageLayoutBuilder
     */
    public function __construct(BuilderInterface $pageLayoutBuilder)
    {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $configOptions = $this->pageLayoutBuilder->getPageLayoutsConfig()->getOptions();
        $options = [];
        $default = [
            'label' => 'Use Default Page Layout',
            'value' => 'default'
        ];
        $options[] = $default;
        foreach ($configOptions as $key => $value) {
            if ($key != 'empty') {
                $options[] = [
                    'label' => $value,
                    'value' => $key,
                ];
            }
        }
        $this->options = $options;

        return $options;
    }
}