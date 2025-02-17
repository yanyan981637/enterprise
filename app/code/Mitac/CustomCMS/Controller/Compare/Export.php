<?php
namespace Mitac\CustomCMS\Controller\Compare;

use Magento\Framework\App\Filesystem\DirectoryList;
class Export extends \Magento\Framework\App\Action\Action
{ 
	public function __construct
	(
		\Magento\Backend\App\Action\Context $context, 
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\App\Response\Http\FileFactory $fileFactory, 
		\Magento\Framework\Filesystem $filesystem
	)
	{
		$this->_fileFactory = $fileFactory;
		$this->resultFactory = $resultFactory;
		$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
		parent::__construct($context);
	}
	public function execute() { 
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$CompareBlock = $objectManager->get('\Mitac\CustomCMS\Block\Catalog\ListCompare');
		$compareProducts = $CompareBlock->getItems();
		$compareProductsAttributes = $CompareBlock->getAllAttr();

		if ($compareProducts->getSize() > 0) {
			$name = date('m_d_Y_H_i_s');
			$filepath = 'export/custom' . $name . '.csv';
			$this->directory->create('export');
			/* Open file */
			$stream = $this->directory->openFile($filepath, 'w+');
			$stream->lock();

			
			$header = array();
			$header[] = chr(239).chr(187).chr(191);
			$header[] = 'Specification';
			foreach ($compareProducts as $item) {
				$header[] = $item->getName();
			}
			/* Write Header */
			$stream->writeCsv($header);

			$helper = $objectManager->get('\Magento\Catalog\Helper\Output');
			foreach ($compareProductsAttributes as $attribute)
			{
				$itemData = array();
				$itemData[] = chr(239).chr(187).chr(191);
				$itemData[] =  $attribute->getStoreLabel() ? $attribute->getStoreLabel() : __($attribute->getFrontendLabel());
				if ($CompareBlock->hasAttributeValueForProductsCustomer($attribute) and  strtolower($attribute->getFrontendLabel()) !== 'sku') {
					foreach ($compareProducts as $item) {
						$value = strip_tags($helper->productAttribute($item,$CompareBlock->getProductAttributeValue($item,  $attribute), $attribute->getAttributeCode()));
						if ($value == 'Yes' || strtolower($value)=='true') {
							$value = "Yes";
						} elseif (preg_match("/^true/i",strtolower($value))) {
							$value = "Yes".substr($value,4);
						} elseif ($value == 'No' || strtolower($value)=='false') {
							$value = "No";
						} elseif (preg_match("/^false/i",strtolower($value))) {
							$value = "No".substr($value,5);
						} elseif (strtolower($value)=='-' || strtolower($value)=='n/a') {
							$value = $value;
						} else {
							$value = $value;
						}
						$itemData[] = $value;
					}
					$stream->writeCsv($itemData);
				}
			}
			$content = [];
			$content['type'] = 'filename'; // must keep filename
			$content['value'] = $filepath;
			$content['rm'] = '1'; //remove csv from var folder
			$csvfilename = 'Comparison.csv';
			return $this->_fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
		} else {
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$BaseUrl = $storeManager->getStore()->getBaseUrl();                    
			$resultRedirect = $this->resultRedirectFactory->create();
			return $resultRedirect->setUrl($BaseUrl, ['_secure' => false]);

		}

		
	}
}