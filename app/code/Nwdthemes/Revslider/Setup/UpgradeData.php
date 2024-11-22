<?php

namespace Nwdthemes\Revslider\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface {

    protected $_blockRepositoryInterface;
    protected $_pageRepositoryInterface;
    protected $_searchCriteriaInterface;

    /**
     * @var \Nwdthemes\Revslider\Helper\Options
     */
    protected $_optionsHelper;

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepositoryInterface,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepositoryInterface,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaInterface,
        \Magento\Framework\App\State $state,
        \Nwdthemes\Revslider\Helper\Options $optionsHelper
    ) {
        $this->_blockRepositoryInterface = $blockRepositoryInterface;
        $this->_pageRepositoryInterface = $pageRepositoryInterface;
        $this->_searchCriteriaInterface = $searchCriteriaInterface;
        $this->_optionsHelper = $optionsHelper;

        try {
            $state->getAreaCode();
        } catch (\Exception $e) {
            $state->setAreaCode('frontend');
        }
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        // replace depricated revslider shortcode

        if (version_compare($context->getVersion(), '5.4.7.2') < 0) {

            $blockList = $this->_blockRepositoryInterface->getList($this->_searchCriteriaInterface);
            foreach ($blockList->getItems() as $blockItem) {
                if (is_array($blockItem)) {
                    $blockItem = $this->_blockRepositoryInterface->getById($blockItem['identifier']);
                }
                $content = $blockItem->getContent();
                if (strpos($content, '{{revslider ') !== FALSE) {
                    $blockItem
                        ->setContent(str_replace('{{revslider ', '{{block class="Nwdthemes\Revslider\Block\Revslider" ', $content))
                        ->save();
                }
            }

            $pageList = $this->_pageRepositoryInterface->getList($this->_searchCriteriaInterface);
            foreach ($pageList->getItems() as $pageItem) {
                if (is_array($pageItem)) {
                    $pageItem = $this->_pageRepositoryInterface->getById($pageItem['identifier']);
                }
                $content = $pageItem->getContent();
                if (strpos($content, '{{revslider ') !== FALSE) {
                    $pageItem
                        ->setContent(str_replace('{{revslider ', '{{block class="Nwdthemes\Revslider\Block\Revslider" ', $content))
                        ->save();
                }
            }

        }

        if (version_compare($context->getVersion(), '6.1.1') < 0) {

            // Add date and time format options

            $options = [
                'date_format' => 'F j, Y',
                'time_format' => 'g:i a'
            ];
            foreach ($options as $handle => $value) {
                $this->_optionsHelper->updateOption($handle, $value);
            }

        }

    }

}
