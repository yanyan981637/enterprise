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



namespace Mirasvit\SearchLanding\Ui\Page\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\SearchLanding\Repository\PageRepository;

class DataProvider extends AbstractDataProvider
{
    private $pageRepository;

    public function __construct(
        PageRepository $pageRepository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->pageRepository = $pageRepository;
        $this->collection     = $this->pageRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        $result = [];

        foreach ($this->pageRepository->getCollection() as $page) {
            $pageData = $page->getData();

            $result[$page->getId()] = $pageData;
        }

        return $result;
    }
}
