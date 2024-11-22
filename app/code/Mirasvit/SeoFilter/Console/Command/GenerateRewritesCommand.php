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
 * @package   mirasvit/module-seo-filter
 * @version   1.3.2
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\SeoFilter\Console\Command;


use Magento\Catalog\Model\Layer\Category\FilterableAttributeList;
use Magento\Framework\App\State;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SeoFilter\Api\Data\RewriteInterface;
use Mirasvit\SeoFilter\Repository\RewriteRepository;
use Mirasvit\SeoFilter\Service\RewriteService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateRewritesCommand extends Command
{
    private $objectManager;

    private $appState;

    private $storeManager;

    private $filterableAttributeList;

    private $rewriteService;

    private $rewriteRepository;

    public function __construct(
        ObjectManagerInterface $objectManager,
        State $appState,
        StoreManagerInterface $storeManager,
        FilterableAttributeList $filterableAttributeList,
        RewriteService $rewriteService,
        RewriteRepository $rewriteRepository
    ) {
        $this->objectManager           = $objectManager;
        $this->appState                = $appState;
        $this->storeManager            = $storeManager;
        $this->filterableAttributeList = $filterableAttributeList;
        $this->rewriteService          = $rewriteService;
        $this->rewriteRepository       = $rewriteRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mirasvit:seo-filter:rewrites')
            ->setDescription('Generate SEO-friendly rewrites for filter options');

        $this->addOption('generate', null, null, 'Generate Rewrites');
        $this->addOption('remove', null, null, 'Remove All Rewrites');

        parent::configure();
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->setAreaCode('frontend');
        } catch (\Exception $e) {
        }

        if ($input->getOption('generate')) {
            $output->writeln('Generating filters rewrites for attributes:');

            foreach ($this->storeManager->getStores() as $store) {
                $this->storeManager->setCurrentStore($store);

                $attributes = $this->filterableAttributeList->getList();

                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                foreach ($attributes as $attribute) {
                    $output->writeln($attribute->getStoreLabel($store) . ' (' . $attribute->getAttributeCode() . ')...');

                    $attributeCode = $attribute->getAttributeCode();
                    $this->rewriteService->getAttributeRewrite($attributeCode, (int)$store->getId(), false);

                    if (!in_array($attribute->getFrontendInput(), ['select', 'multiselect', 'boolean'])) {
                        continue;
                    }

                    foreach ($attribute->getOptions() as $option) {
                        if ($option->getValue() || (string)$option->getValue() === '0') {
                            $this->rewriteService->getOptionRewrite(
                                $attributeCode,
                                (string)$option->getValue(),
                                (int)$store->getId(),
                                false
                            );
                        }
                    }
                }
            }

            $output->writeln('Done!');

            return 0;
        }

        if ($input->getOption('remove')) {
            $output->writeln('Removing existing filters rewrites...');
            $resource = $this->rewriteRepository->create()->getResource();
            $resource->getConnection()->query('TRUNCATE TABLE ' . $resource->getTable(RewriteInterface::TABLE_NAME));
            $output->writeln('Done!');

            return 0;
        }

        $help = new HelpCommand();
        $help->setCommand($this);

        $help->run($input, $output);

        return 0;
    }
}
