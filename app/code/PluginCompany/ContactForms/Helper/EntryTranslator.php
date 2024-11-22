<?php
namespace PluginCompany\ContactForms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class EntryTranslator extends AbstractHelper
{
    /**
     * @var StoreManager
     */
    private $storeManager;
    private $generalTranslations;
    private $fieldTranslations;

    /**
     * Translator constructor.
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function loadGeneralTranslationJson($json)
    {
        $this->generalTranslations = json_decode($json, true);
        return $this;
    }

    public function loadFieldTranslationJson($json)
    {
        $this->fieldTranslations = json_decode($json, true);
        return $this;
    }

    public function translateLabel($fieldId, $originalLabel)
    {
        return $this->translateField($fieldId, 'label', $originalLabel);
    }

    public function translateField($fieldId, $fieldType, $original = '')
    {
        $newValue = $this->getFieldTranslation($fieldId, $fieldType);
        if($newValue) return $newValue;
        return $original;
    }

    private function getFieldTranslation($fieldId, $fieldType)
    {
        if(empty($this->fieldTranslations)){
            return '';
        }
        foreach($this->fieldTranslations as $translation) {
            if($translation['id'] == $fieldId && $translation['fieldType'] == $fieldType) {
                return $this->getTranslationForCurrentStore($translation);
            }
        }
        return '';
    }

    private function getTranslationForCurrentStore($translation)
    {
        if(!empty($translation['translations'][$this->getStoreIdTranslationKey()])) {
            return $translation['translations'][$this->getStoreIdTranslationKey()];
        }
        return '';
    }

    public function getGeneralFieldTranslation($fieldId)
    {
        if(!empty($this->generalTranslations[$fieldId]['translations'][$this->getStoreIdTranslationKey()])) {
            return $this->generalTranslations[$fieldId]['translations'][$this->getStoreIdTranslationKey()];
        }
        return '';
    }

    private function getStoreIdTranslationKey()
    {
        return 'store_' . $this->getCurrentStoreId();
    }

    private function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return mixed
     */
    public function getFieldTranslations()
    {
        return $this->fieldTranslations;
    }

    /**
     * @return mixed
     */
    public function getGeneralTranslations()
    {
        return $this->generalTranslations;
    }


}

