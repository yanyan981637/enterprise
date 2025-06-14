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



namespace Mirasvit\SearchSphinx\SearchAdapter\Field;

use Magento\Framework\ObjectManagerInterface;

class FieldFactory
{
    protected $objectManager = null;

    protected $instanceName  = null;

    public function __construct(
        ObjectManagerInterface $objectManager,
        string $instanceName = \Mirasvit\SearchSphinx\SearchAdapter\Field\Field::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName  = $instanceName;
    }

    public function create(array $data = []): FieldInterface
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
