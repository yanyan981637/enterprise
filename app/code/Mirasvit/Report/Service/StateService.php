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
 * @package   mirasvit/module-report
 * @version   1.4.13
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Report\Service;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Mirasvit\Report\Api\Data\StateInterface;
use Mirasvit\Report\Repository\StateRepository;

class StateService
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var StateRepository
     */
    private $stateRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    /**
     * StateService constructor.
     *
     * @param UserContextInterface    $userContext
     * @param SessionManagerInterface $sessionManager
     * @param StateRepository         $stateRepository
     */
    public function __construct(
        UserContextInterface $userContext,
        SessionManagerInterface $sessionManager,
        StateRepository $stateRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->userContext     = $userContext;
        $this->sessionManager  = $sessionManager;
        $this->stateRepository = $stateRepository;
        $this->serializer    = $serializer;
    }

    /**
     * @param string $namespace
     * @param array  $config
     */
    public function saveState($namespace, array $config)
    {
        $state = $this->loadState($namespace);

        if (!$state) {
            $state = $this->stateRepository->create();

            $state->setNamespace($namespace . $this->sessionManager->getSessionId())
                ->setUserId($this->userContext->getUserId())
                ->setIdentifier('current')
                ->setCurrent(true)
                ->setCreatedAt(date('Y-m-d'))
                ->setUpdatedAt(date('Y-m-d'));
        }

        $state->setConfig($this->serializer->serialize($config));

        $this->stateRepository->save($state);
    }

    /**
     * @param string $namespace
     * @param mixed  $defaultConfig
     *
     * @return mixed
     */
    public function mergeState($namespace, $defaultConfig)
    {
        $state = $this->loadState($namespace);

        if (!$state) {
            return $defaultConfig;
        }

        foreach ($state->getConfig() as $key => $value) {
            if (is_array($value) && $key == 'filters') {
                foreach ($value as $item) {
                    if (isset($item['column']) && $item['column'] !== 'sales_order|status') {
                        $defaultConfig[$key][] = $item;
                    }
                }
            } else {
                $defaultConfig[$key] = $value;
            }
        }

        return $defaultConfig;
    }

    /**
     * @param string $namespace
     *
     * @return StateInterface|false
     */
    private function loadState($namespace)
    {
        $namespace = $namespace . $this->sessionManager->getSessionId();

        $model = $this->stateRepository->getCollection()
            ->addFieldToFilter(StateInterface::USER_ID, $this->userContext->getUserId())
            ->addFieldToFilter(StateInterface::BOOKMARKSPACE, $namespace)
            ->getFirstItem();

        if ($model->getId()) {
            $state = $this->stateRepository->create();
            $state->setData($model->getData());

            return $state;
        }

        return false;
    }
}
