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



namespace Mirasvit\Search\Ui\ScoreRule\Form\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Mirasvit\Search\Model\ScoreRule\Rule;

class ConditionsRenderer implements RendererInterface
{
    public function render(AbstractElement $element): string
    {
        /** @var Rule $rule */
        $rule = $element->getRule();
        if ($rule && $rule->getConditions()) {
            return $rule->getConditions()->asHtmlRecursive();
        }

        return '';
    }
}
