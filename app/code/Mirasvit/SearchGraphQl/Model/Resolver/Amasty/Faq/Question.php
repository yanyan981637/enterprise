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


declare(strict_types=1);

namespace Mirasvit\SearchGraphQl\Model\Resolver\Amasty\Faq;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Question implements ResolverInterface
{
    private $size = 0;

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args)) {
            if ($field->getName() == 'size') {
                return $this->size;
            }
        }

        $collection = $value['instance']->getSearchCollection();
        $this->size = $collection->getSize();
        $collection->setPageSize($args['pageSize'])
            ->setCurPage($args['currentPage']);

        $items = [];

        foreach ($collection as $question) {
            $items[] = [
                'name' => $question->getTitle(),
                'url'  => $question->getUrl(),
            ];
        }

        return $items;
    }
}
