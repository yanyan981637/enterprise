<?php
/**
 * Created by:  Milan Simek
 * Company:     Plugin Company
 *
 * LICENSE: http://plugin.company/docs/magento-extensions/magento-extension-license-agreement
 *
 * YOU WILL ALSO FIND A PDF COPY OF THE LICENSE IN THE DOWNLOADED ZIP FILE
 *
 * FOR QUESTIONS AND SUPPORT
 * PLEASE DON'T HESITATE TO CONTACT US AT:
 *
 * SUPPORT@PLUGIN.COMPANY
 */
namespace PluginCompany\ContactForms\Model\Widget;

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\Dir\Reader;
use Magento\Widget\Model\Config\SchemaLocator;

class SchemaLocatorPlugin
{

    /** @var ProductMetadataInterface */
    protected $productMetaData;

    /** @var Reader */
    protected $dirReader;

    /**
     * SchemaLocatorPlugin constructor.
     * @param ProductMetadataInterface $productMetaData
     * @param Reader $dirReader
     */
    public function __construct(
        ProductMetadataInterface $productMetaData,
        Reader $dirReader
    ) {
        $this->productMetaData = $productMetaData;
        $this->dirReader = $dirReader;
        return $this;
    }
    public function afterGetSchema(SchemaLocator $subject, $result)
    {
        if(!$this->isMagento22plus()){
            return $this->getEtcDirFile('widget.xsd');
        }
        return $this->getEtcDirFile('widget_2.2.xsd');
    }

    public function afterGetPerFileSchema(SchemaLocator $subject, $result)
    {
        if(!$this->isMagento22plus()){
            return $this->getEtcDirFile('widget_file.xsd');
        }
        return $this->getEtcDirFile('widget_file_2.2.xsd');
    }

    private function isMagento22plus()
    {
        return version_compare(
            $this->productMetaData->getVersion(),
            '2.2',
            '>='
        );
    }

    private function getEtcDirFile($fileName)
    {
        return $this->getEtcDir() . '/' . $fileName;
    }

    private function getEtcDir()
    {
        return $this->dirReader->getModuleDir('etc', 'PluginCompany_ContactForms');
    }
}