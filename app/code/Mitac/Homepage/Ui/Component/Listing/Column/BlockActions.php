<?php
namespace Mitac\Homepage\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

use Mitac\Homepage\Api\Data\BlockInterface;
use Mitac\Homepage\Helper\StoreData;

class BlockActions extends Column
{
    const PATH_EDIT = 'homepage/index/edit';
    const PATH_DELETE = 'homepage/index/delete';

    /** @var UrlInterface */
    protected $urlBuilder;
    protected $storeBuilder;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        StoreData $storeBuilder,
        array $components = [],
        array $data = []
    ) 
    {
        $this->urlBuilder = $urlBuilder;
        $this->storeBuilder = $storeBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items']))
        {
            foreach ($dataSource['data']['items'] as $key =>& $item)
            {
                $name = $this->getData('name');
                $stores_id = $item['stores_id'];
                $PageIdentifier =  $item['PageIdentifier'];

                if(isset($stores_id))
                {
                    $storesArr = $this->storeBuilder->getStores($item['banners_id']);

                    //echo $item['banners_id'].' <=> '.count($storesArr).'<br>';

                    if (count($storesArr) > 0) 
                    {
                        $dataSource['data']['items'][$key]['wbname'] = '';
                        $dataSource['data']['items'][$key]['groupname'] = '';
                        $dataSource['data']['items'][$key]['stores_id'] = '';

                        foreach($storesArr as $rows) 
                        {
                            foreach($rows as $keys => $values)
                            {
                                $StoreDataArr = $this->storeBuilder->getStoreData($values);
                                if(!empty($StoreDataArr))
                                {
                                    $dataSource['data']['items'][$key]['wbname'] .= $StoreDataArr[0]['wbname'].'<br>';
                                    $dataSource['data']['items'][$key]['groupname'] .= $StoreDataArr[0]['groupname'].'<br>';
                                    $dataSource['data']['items'][$key]['stores_id'] .= $StoreDataArr[0]['stroename'].'<br>';
                                }
                                if ($values == 0)
                                {
                                    $dataSource['data']['items'][$key]['wbname'] .= 'ALL WebSite';
                                    $dataSource['data']['items'][$key]['groupname'] .= 'ALL MainWebsite';
                                    $dataSource['data']['items'][$key]['stores_id'] .= 'ALL StoreView';

                                    break;
                                }
                            }
                        }
                        $dataSource['data']['items'][$key]['wbname'] = substr($dataSource['data']['items'][$key]['wbname'], 0, -4);
                        $dataSource['data']['items'][$key]['groupname'] = substr($dataSource['data']['items'][$key]['groupname'], 0, -4);
                        $dataSource['data']['items'][$key]['stores_id'] = substr($dataSource['data']['items'][$key]['stores_id'], 0, -4);
                    } 
                    else 
                    {
                        $StoreDataArr = $this->storeBuilder->getStoreData($stores_id);

                        if(!empty($StoreDataArr))
                        {
                            $dataSource['data']['items'][$key]['wbname'] = $StoreDataArr[0]['wbname'];
                            $dataSource['data']['items'][$key]['groupname'] = $StoreDataArr[0]['groupname'];
                            $dataSource['data']['items'][$key]['stores_id'] = $StoreDataArr[0]['stroename'];
                        }
                    }
                }

                if (isset($PageIdentifier))
                {
                    $IdentifierArr = $this->storeBuilder->getPageIdenifier($item['banners_id']);

                    if (count($IdentifierArr) > 0) 
                    {
                        $dataSource['data']['items'][$key]['pages'] = '';
                        $sqlArr = [];
                        foreach($IdentifierArr as $rows => $RowsInfo)
                        {
                            $sqlArr[] = '"'.$RowsInfo['identifier'].'"';
                        }
                        $sqlString = implode(",", $sqlArr);
                        $PageInfoArr = $this->storeBuilder->getPagesData($sqlString);

                        $typeflage = '';
                        //-------------------------------------------------------------------------------------------------//
                        if(empty($PageInfoArr))
                        {
                            $SecialPageArr = Array(
                                            '"customer/account/create/"' => Array(Array('pagetype'=>'Special Page','title'=>'Create an Account')),
                                            '"catalogsearch/result/"' => Array(Array('pagetype'=>'Special Page','title'=>'Search results')),
                                            );

                            if(!empty($SecialPageArr[$sqlString]))
                            {
                                $PageInfoArr = $SecialPageArr[$sqlString];
                            }
                        }
                        //-------------------------------------------------------------------------------------------------//
                        if(empty($PageInfoArr))
                        {
                            $SPIdentifier = str_replace('mpblog_category_view/',"",$sqlArr[0]);
                            $PageInfoArr = $this->storeBuilder->getBlogData($SPIdentifier);
                        }
                        //-------------------------------------------------------------------------------------------------//
                        if(!empty($PageInfoArr))
                        {
                            foreach ($PageInfoArr as $rows => $infos)
                            {
                                if ($infos['pagetype'] != $typeflage)
                                {
                                    if (strlen($dataSource['data']['items'][$key]['pages']) > 4)
                                    {
                                        $dataSource['data']['items'][$key]['pages'] = substr($dataSource['data']['items'][$key]['pages'], 0, -4).'<br>';
                                    }
                                    $dataSource['data']['items'][$key]['pages'] .= '<strong>'.$infos['pagetype'].'</strong><br>';
                                    $typeflage = $infos['pagetype'];
                                }
                                $dataSource['data']['items'][$key]['pages'] .= '<samp style="padding-left: 10px">'.$infos['title'].'</samp><br>';
                            }
                        }
                        $dataSource['data']['items'][$key]['pages'] = substr($dataSource['data']['items'][$key]['pages'], 0, -4);
                        //-------------------------------------------------------------------------------------------------//
                    }
                }

                if (isset($item[BlockInterface::KEY_ID])) 
                {
                    if ($item['type'] == 'block') 
                    {
                        $editURL = 'index';
                    } 
                    else 
                    {
                        $editURL = $item['type'];
                    }
                    //$item['title'] = $item['title'];
                    
                    $item[$name]['edit'] = [
                        'href'  => $this->urlBuilder->getUrl('homepage/'.$editURL.'/edit', ['id' => $item[BlockInterface::KEY_ID]]),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href'    => $this->urlBuilder->getUrl(self::PATH_DELETE, ['id' => $item[BlockInterface::KEY_ID]]),
                        'label'   => __('Delete'),
                        'confirm' => [
                            'title'   => __('Delete') . ' ' . $item[BlockInterface::KEY_ID],
                            'message' => __(
                                'Are you sure you wan\'t to delete a record?'
                            )
                        ]
                    ];
                }
            }
            unset($item); unset($stores_id); unset($StoreDataArr);
        }

        return $dataSource;
    }
}
