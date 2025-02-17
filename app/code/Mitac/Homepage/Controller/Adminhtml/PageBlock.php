<?php
namespace Mitac\Homepage\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Mitac\Homepage\Api\BlockRepositoryInterface;

abstract class PageBlock extends Action
{
    /**
     * Image repository
     *
     * @var ImageRepositoryInterface
     */
    protected $pageblockRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Date filter
     *
     * @var Date
     */
    protected $dateFilter;

    /**
     * Sliders constructor.
     *
     * @param Registry $registry
     * @param FeaturesRepositoryInterface $featuresRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        BlockRepositoryInterface $blockRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context
    ) 
    {
        parent::__construct($context);
        $this->coreRegistry         = $registry;
        $this->pageblockRepository  = $blockRepository;
        $this->resultPageFactory    = $resultPageFactory;
        $this->dateFilter = $dateFilter;
    }
}
