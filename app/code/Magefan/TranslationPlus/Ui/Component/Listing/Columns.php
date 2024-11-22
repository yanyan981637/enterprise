<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\TranslationPlus\Ui\Component\Listing;

use Magefan\TranslationPlus\Model\TranslationIndex;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Customer\Ui\Component\ColumnFactory;
use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    /**
     * @var int
     */
    protected $columnSortOrder;

    /**
     * @var ColumnFactory
     */
    private $columnFactory;

    /**
     * @var array
     */
    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];
    private $translation;

    /**
     * Columns constructor.
     *
     * @param ContextInterface $context
     * @param ColumnFactory    $columnFactory
     * @param TranslationIndex $translation
     * @param array            $components
     * @param array            $data
     */
    public function __construct(
        ContextInterface $context,
        ColumnFactory $columnFactory,
        TranslationIndex $translation,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
        $this->translation = $translation;
    }

    /**
     * Return default sort order
     *
     * @return int
     */
    protected function getDefaultSortOrder()
    {
        $max = 0;
        foreach ($this->components as $component) {
            $config = $component->getData('config');
            if (isset($config['sortOrder']) && $config['sortOrder'] > $max) {
                $max = $config['sortOrder'];
            }
        }
        return ++$max;
    }

    /**
     * Update actions column sort order
     *
     * @return void
     */
    protected function updateActionColumnSortOrder()
    {
        if (isset($this->components['actions'])) {
            $component = $this->components['actions'];
            $component->setData(
                'config',
                array_merge($component->getData('config'), ['sortOrder' => ++$this->columnSortOrder])
            );
        }
    }

    /**
     * Prepare translation Plus columns
     */
    public function prepare()
    {
        $this->columnSortOrder = $this->getDefaultSortOrder();
        $locales = $this->translation->getAllStoreLocale();
        foreach ($locales as $locale) {
            $locales[] = $locale . '_translated';
        }
        $columns = [];
        foreach ($locales as $locale) {
            $lowerLocale = strtolower($locale);
            $columns[$lowerLocale] = [
                'label' => $locale,
                'attribute_code' => $lowerLocale,
                'frontend_label' => $locale,
                'entity_type_code' => $lowerLocale,
                'backend_type' => 'input' ,
                'frontend_input' => (strpos($locale, '_translated') === false) ? 'text' : 'select',
                'visible' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => true,
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => true,
                'options' => [],
                'validation_rules' => [],
                'required' => false,
                'editor' => [
                  'editorType' => 'textarea'
                ],
            ];
        }
        foreach ($columns as $newAttributeCode => $attributeData) {
            if (isset($this->components[$newAttributeCode])) {
                //do nothing
            } elseif (!$attributeData[AttributeMetadata::BACKEND_TYPE] != 'static'
                && $attributeData[AttributeMetadata::IS_USED_IN_GRID]
            ) {
                $this->addColumn($attributeData, $newAttributeCode);
            }
        }
        $this->updateActionColumnSortOrder();
        parent::prepare();
    }

    /**
     * Add column to the component
     *
     * @param  array  $attributeData
     * @param  string $columnName
     * @return void
     */
    public function addColumn(array $attributeData, $columnName)
    {
        $config['sortOrder'] = ++$this->columnSortOrder;
        if ($attributeData[AttributeMetadata::IS_FILTERABLE_IN_GRID]) {
            $config['filter'] = $this->getFilterType($attributeData[AttributeMetadata::FRONTEND_INPUT]);
        }

        if (isset($attributeData['editor']) && false === strpos($columnName, '_translated')) {
            $config['editor'] = $attributeData['editor'];
        } else {
            $config['editor'] = '';
        }

        $config['options'] = [
            ['value' => 0, 'label' => __('No') ],
            ['value' => 1, 'label' => __('Yes') ]
        ];

        $column = $this->columnFactory->create($attributeData, $columnName, $this->getContext(), $config);
        $column->prepare();
        $this->addComponent($attributeData[AttributeMetadata::ATTRIBUTE_CODE], $column);
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param  string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return $this->filterMap[$frontendInput] ?? $this->filterMap['default'];
    }
}
