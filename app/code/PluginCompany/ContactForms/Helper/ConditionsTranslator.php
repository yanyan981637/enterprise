<?php
namespace PluginCompany\ContactForms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class ConditionsTranslator extends AbstractHelper
{
    const ALLOWED_FIELD_TYPES = ['options', 'dropdown', 'radios', 'checkboxes', 'multiple'];

    private $translations;
    /**
     * @var StoreManager
     */
    private $storeManager;
    private $conditions;
    private $optionTranslations;
    private $originalConditionsJson;

    /**
     * ConditionsTranslator constructor.
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

    public function loadTranslationJson($json)
    {
        $this->translations = json_decode($json, true);
        if(!is_array($this->translations)) {
            $this->translations = [];
        }
        $this->optionTranslations = null;
        return $this;
    }

    public function loadConditionsJson($json)
    {
        $this->originalConditionsJson = $json;
        $this->conditions = json_decode($json, true);
        if(!is_array($this->conditions)) {
            $this->conditions = [];
        }
        return $this;
    }

    public function getTranslatedConditionsJson()
    {
        $json = json_encode($this->getTranslatedConditions());
        return $json;
    }

    public function getTranslatedConditions()
    {
        foreach($this->conditions as &$condition) {
            $this->translateDependencies($condition['dependencies']);
        }
        return $this->conditions;
    }

    private function translateDependencies(&$dependencies)
    {
        foreach($dependencies as &$dependency) {
            if(!in_array($dependency['fieldType'], self::ALLOWED_FIELD_TYPES)) continue;
            $this->translateOptions($dependency['value'], $dependency['field']);
        }
    }

    private function translateOptions(&$options, $fieldId)
    {
        if(!is_array($options)) {
            $options = $this->getTranslatedValue($options, $fieldId);
            return;
        }
        foreach($options as &$option) {
            $option = $this->getTranslatedValue($option, $fieldId);
        }
    }

    private function getTranslatedValue($optionText, $fieldId)
    {
        $translations = $this->getOptionTranslations();
        if(!isset($translations[$fieldId])) {
            return $optionText;
        }
        if(empty($translations[$fieldId]['translations']['store_' . $this->getCurrentStoreId()])) {
            return $optionText;
        }
        $originalOptions = $translations[$fieldId]['original'];
        if(!is_array($originalOptions)) {
            return $optionText;
        }
        $optionKey = array_search($optionText, $originalOptions);
        if($optionKey === false) {
            return $optionText;
        }
        $translatedOptions = explode("\n", $translations[$fieldId]['translations']['store_' . $this->getCurrentStoreId()]);
        if(empty($translatedOptions[$optionKey])) {
            return $optionText;
        }
        return $translatedOptions[$optionKey];
    }

    private function getOptionTranslations()
    {
        if($this->optionTranslations) {
            return $this->optionTranslations;
        }
        $optionTranslations = [];
        foreach($this->translations as $translation) {
            if(in_array($translation['fieldType'], self::ALLOWED_FIELD_TYPES) && $translation['inputType'] == 'textarea-split') {
                $optionTranslations[$translation['id']] = $translation;
            }
        }
        $this->optionTranslations = $optionTranslations;
        return $this->optionTranslations;
    }
    private function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

}

