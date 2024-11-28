<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\TranslationPlus\Ui\Component\Listing\Columns\Column;

use Magefan\TranslationPlus\Model\Config\Source\UsedInArea as UsedInAreaSource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class UsedInArea extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var UsedInAreaSource
     */
    private $usedInArea;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UsedInAreaSource $usedInArea,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->usedInArea = $usedInArea;
    }

    public function prepareDataSource(array $dataSource)
    {
        $usedInAreaValuesLabels =  $this->usedInArea->getValueLabelsArray();

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['used_in_area'] = $usedInAreaValuesLabels[(int)$item['used_in_area']];
            }
        }

        return $dataSource;
    }
}
