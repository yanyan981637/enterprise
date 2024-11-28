<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\TranslationPlus\Model\Config\Source;

class UsedInArea implements \Magento\Framework\Data\OptionSourceInterface
{
    const UNDEFINED = 0;
    const STOREFRONT_AND_ADMIN_PANEL = 1;
    const ADMIN_PANEL = 2;
    const STOREFRONT = 3;
    const TESTS = 4;
    const GRAPHQL = 5;

    /**
     * @return array[]
     */
    public function toOptionArray() : array
    {
        $result = [];

        foreach ($this->getValueLabelsArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    public function getValueLabelsArray(): array
    {
        return [
            self::UNDEFINED => __('Undefined'),
            self::STOREFRONT_AND_ADMIN_PANEL => __('Storefront & Admin Panel'),
            self::ADMIN_PANEL => __('Admin Panel'),
            self::STOREFRONT => __('Storefront'),
            self::GRAPHQL => __('GraphQL Endpoints'),
            self::TESTS => __('Tests'),
        ];
    }
}
