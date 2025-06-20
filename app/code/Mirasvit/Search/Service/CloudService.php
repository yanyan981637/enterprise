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



namespace Mirasvit\Search\Service;

use Magento\Framework\Serialize\Serializer\Json;

class CloudService
{
    const ENDPOINT = 'http://mirasvit.com/media/cloud/';

    protected $serializer;

    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(string $module, string $entity)
    {
        $list = $this->request($module, $entity, 'list');

        $result = [];
        if ($list) {
            foreach ($list as $item) {
                $result[] = [
                    'value' => $item['identifier'],
                    'label' => $item['name'],
                ];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $module, string $entity, string $identifier)
    {
        return $this->request($module, $entity, 'get', ['identifier' => $identifier]);
    }

    /**
     * @param string $module
     * @param string $entity
     * @param string $action
     * @param array  $optional
     *
     * @return array|string|null
     */
    private function request(string $module, string $entity, string $action, array $optional = [])
    {
        $args = [
            'module' => $module,
            'entity' => $entity,
            'action' => $action,
        ];

        $args = array_merge_recursive($args, $optional);

        $query = http_build_query($args);

        try {
            $result = $this->serializer->unserialize(file_get_contents(self::ENDPOINT . '?' . $query));

            if ($result['success']) {
                return $result['data'];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
