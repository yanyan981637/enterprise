<?php
namespace PluginCompany\ContactForms\Block\Adminhtml\Entry\Listing\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Shows product name in admin grids instead of product id
 */
class Fields extends Column
{
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;
    protected $productFactory;

    private $blockFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param BlockFactory $blockFactory
     * @param array $components
     * @param array $data
     * @internal param SystemStore $systemStore
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        BlockFactory $blockFactory,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->blockFactory = $blockFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['fields'] = $this->getSubmissionOverviewTable($item['fields']);
            }
        }

        return $dataSource;
    }


    public function getSubmissionOverviewTable($fields)
    {
        $fields = json_decode($fields, true);
        if(empty($fields) || !is_array($fields)) {
            $fields = [];
        }
        return $this->blockFactory
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('PluginCompany_ContactForms::entry/listing/submission_overview_table.phtml')
            ->setSubmissionParams($fields)
            ->toHtml()
            ;
    }

}
