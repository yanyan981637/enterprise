<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-search-ultimate
 * @version   2.1.8
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Search\Ui\ScoreRule\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mirasvit\Search\Api\Data\ScoreRuleInterface;

class Actions extends Column
{
    private $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')] = [
                    'edit'   => [
                        'href'  => $this->urlBuilder->getUrl('search/scoreRule/edit', [
                            ScoreRuleInterface::ID => $item[ScoreRuleInterface::ID],
                        ]),
                        'label' => __('Edit'),
                    ],
                    'apply'  => [
                        'href'  => $this->urlBuilder->getUrl('search/scoreRule/apply', [
                            ScoreRuleInterface::ID => $item[ScoreRuleInterface::ID],
                        ]),
                        'label' => __('Apply'),
                    ],
                    'delete' => [
                        'href'    => $this->urlBuilder->getUrl('search/scoreRule/delete', [
                            ScoreRuleInterface::ID => $item[ScoreRuleInterface::ID],
                        ]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete rule?'),
                        ],
                    ],
                ];
            }
        }

        return $dataSource;
    }
}
