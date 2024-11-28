<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://www.magezon.com/license
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Blog
 * @copyright Copyright (C) 2019 Magezon (https://www.magezon.com)
 */

namespace Magezon\Blog\Model\Author\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magezon\Blog\Model\Author;

class DisplayNameType implements OptionSourceInterface
{
    /**
     * @var Author
     */
    protected $blogAuthor;

    /**
     * @param Author $blogAuthor
     */
    public function __construct(
        Author $blogAuthor
    )
    {
        $this->blogAuthor = $blogAuthor;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->blogAuthor->getDisplayTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key
            ];
        }
        return $options;
    }
}
