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
 * @package   mirasvit/module-navigation
 * @version   2.4.5
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Brand\Model\Brand\PostData;

use Mirasvit\Brand\Api\Data\BrandPageInterface;
use Mirasvit\Brand\Api\Data\PostData\ProcessorInterface;

class ImageProcessor implements ProcessorInterface
{

    public function preparePostData(array $data): array
    {
        $data = $this->prepareImageData($data, BrandPageInterface::LOGO);
        $data = $this->prepareImageData($data, BrandPageInterface::BANNER);

        return $data;
    }

    private function prepareImageData(array $data, string $imageKey): array
    {
        if (isset($data[$imageKey])) {
            $image = $data[$imageKey];

            $data[$imageKey] = isset($image[0]['name']) ? $image[0]['name'] : '';
        } else {
            $data[$imageKey] = null;
        }

        return $data;
    }
}
