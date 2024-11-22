<?php
namespace PluginCompany\ContactForms\Block\Adminhtml\Entry\Listing\Renderer;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use PluginCompany\ContactForms\Model\FormRepository;

/**
 * Shows product name in admin grids instead of product id
 */
class ParentFormLink extends Column
{
    private $formRepo;
    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    protected $productFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FormRepository $formRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FormRepository $formRepository,
        array $components = [],
        array $data = []
    ) {
        $this->formRepo = $formRepository;
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
                $item['form_id'] = $this->getFormLink($item['form_id']);
            }
        }
        return $dataSource;
    }

    public function getFormLink($formId)
    {
        $url = $this->getEditUrl($formId);
        $name = $this->getFormTitle($formId);
        return "<a href='$url'>$name</a>";
    }

    private function getEditUrl($formId)
    {
        return $this->context
            ->getUrl(
                'plugincompany_contactforms/form/edit',
                ['form_id' => $formId]
            );
    }

    private function getFormTitle($formId)
    {
        $form = $this->formRepo->getByIdOrNew($formId);
        return $form->getTitle();
    }
}
