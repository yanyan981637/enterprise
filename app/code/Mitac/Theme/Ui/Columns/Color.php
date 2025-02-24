<?php
namespace Mitac\Theme\Ui\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Color extends Column {

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['color'])) {
                    $originalColor = $item['color'];
                    $item['color'] = "<div style=\"background-color: $originalColor;width: 20px;height: 20px;\"></div>";
                }
            }
        }
        return $dataSource;
    }

}
