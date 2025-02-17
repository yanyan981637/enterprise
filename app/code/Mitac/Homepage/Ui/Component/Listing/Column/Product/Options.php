<?php
namespace Mitac\Homepage\Ui\Component\Listing\Column\Product;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Store Options for Cms Pages and Products
 */
class Options implements OptionSourceInterface
{
    protected $helper;
    protected $options;

    public function __construct(
        \Mitac\Homepage\Helper\ProductData $helper
    )
    {
        $this->_helper = $helper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $storeId = 1;
        $OptionPage = [];
        //----------------------------------------------------------------------------------------------------//
	    $OptionPage[] = ['value' => '','label' => 'Please select', '__disableTmpl' => true];
        //----------------------------------------------------------------------------------------------------//
        $PageDataArr = $this->_helper->getPageList($storeId);
        if ($PageDataArr !== null)
        {
            $groups = [];
            foreach ($PageDataArr as $PageData)
            {
                $pageId = $PageData['page_id'];
                $identifier = $PageData['identifier'];
                $title = $PageData['title'].' (ID: '.$pageId.')';
                if (!isset($groups[$pageId]))
                {
                    $groups[$pageId] = [
                        'value' => $pageId.'<=>'.$identifier
                    ];
                    $groups[$pageId]['label'] = $title;
                }
            }
            $OptionPage[] = ['value' => array_values($groups), 'label' => 'Pages', '__disableTmpl' => true];
        }
        //----------------------------------------------------------------------------------------------------//
	    $CategoryDataArr = $this->_helper->getCategoryList($storeId);
        if ($CategoryDataArr !== null)
        {
            $groups = [];
            foreach ($CategoryDataArr as $CategoryData)
            {
                $pageId = $CategoryData['entity_id'];
                $identifier = $CategoryData['value'];
                $title = $CategoryData['title'].' (ID: '.$pageId.')';
                if (!isset($groups[$pageId]))
                {
                    $groups[$pageId] = [
                        'value' => $pageId.'<=>'.$identifier
                    ];
                    $groups[$pageId]['label'] = $title;
                }
            }
            $OptionPage[] = ['value' => array_values($groups), 'label' => 'Category', '__disableTmpl' => true];
        }
        //----------------------------------------------------------------------------------------------------//
        $BlogCategoryDataArr = $this->_helper->getBlogCategoryList($storeId);
        if ($BlogCategoryDataArr !== null)
        {
            $groups = [];
            foreach ($BlogCategoryDataArr as $BlogCategoryData)
            {
                $pageId = $BlogCategoryData['category_id'];
                $identifier = 'mpblog_category_view/'.$pageId;
                $title = $BlogCategoryData['name'].' (ID: '.$pageId.')';
                if (!isset($groups[$pageId]))
                {
                    $groups[$pageId] = [
                        'value' => $pageId.'<=>'.$identifier
                    ];
                    $groups[$pageId]['label'] = $title;
                }
            }
            $OptionPage[] = ['value' => array_values($groups), 'label' => 'Blog Category', '__disableTmpl' => true];
        }
        //----------------------------------------------------------------------------------------------------//
        $SecialPageArr = ['customer/account/create/'=>'Create an Account','catalogsearch/result/'=>'Search results'];
        if(!empty($SecialPageArr))
        {
            $groups = []; $count = 1; 
            foreach($SecialPageArr as $SecialPageUrl=>$SecialPageName)
            {
                $groups[$count] = [
                    'value' => $count.'<=>'.$SecialPageUrl,
                    'label' => $SecialPageName.' (ID: '.$count.')'
                ];
                $count++;
            }
            $OptionPage[] = ['value' => array_values($groups), 'label' => 'SpecialPage', '__disableTmpl' => true];
        }
        //----------------------------------------------------------------------------------------------------//
        $this->options = array_values($OptionPage);


        return $this->options;
    }
}
