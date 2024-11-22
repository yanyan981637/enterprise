<?php
namespace PluginCompany\ContactForms\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class FormHtmlTranslator extends AbstractHelper
{
    private $html;
    private $translations;
    /**
     * @var StoreManager
     */
    private $storeManager;

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

    public function loadTranslationJson($json)
    {
        $this->translations = json_decode($json, true);
        return $this;
    }

    /**
     * @param $html
     * @return mixed
     */
    public function translate($html)
    {
        $this->html = $html;
        if(!$this->translations){
            return $html;
        }
        $storeId = $this->getCurrentStoreId();
        foreach($this->translations as $translation) {
            if(empty($translation['translations']['store_' . $storeId])){
                continue;
            }
            if(!in_array($translation['fieldType'], ['prevtext', 'nexttext', 'pagetitle', 'label', 'label_start', 'label_end', 'placeholder', 'defaultval', 'radios', 'checkboxes', 'options', 'contents', 'helptext', 'content', 'droptext', 'buttonlabel', 'emptyoption', 'notloggedintext'])) {
                continue;
            }
            $translation = $this->prepareFieldForReplace($translation);
            $replaceMethod = 'replace' . str_replace('_', '', ucwords($translation['fieldType'], '_'));
            $this->{$replaceMethod}($translation['id'], $this->getNewText($translation), $translation);
        }
        return $this->html;
    }

    private function prepareFieldForReplace($field)
    {
        if($field['fieldType'] == 'content')
        {
            $field['original'] = preg_replace("/(\r\n\t|\n|\r\t)/","", $field['original']);
        }
        return $field;
    }

    private function replaceLabelStart($id, $newText, $translation)
    {
        $this->replaceLabel($id . '_start', $newText, $translation);
    }

    private function replaceLabelEnd($id, $newText, $translation)
    {
        $this->replaceLabel($id . '_end', $newText, $translation);
    }

    private function replaceLabel($id, $newText, $translation)
    {
        if($translation['parentFieldType'] == 'Upload') {
            preg_match('/label.{0,120}id="' . $id . '"/', $this->html,$uploadTag);
            if(!count($uploadTag)) return;
            $newTag = str_replace($translation['original'], $newText, $uploadTag);
            $this->html = str_replace($uploadTag, $newTag, $this->html);
        }else{
            $this->html = preg_replace("/(for=\"{$id}\">)(.*?)(<\/label)/", '${1}' . $newText . '$3', $this->html);
        }
    }

    private function replacePlaceholder($id, $newText)
    {
        $this->html = preg_replace("/(id=\"{$id}\")(.*?)(placeholder=\")(.*?)(\")/", '$1$2${3}' . $newText . '$5', $this->html);
    }

    // (id="contact-0")(.*?)(value=")(.*?)(")
    private function replaceDefaultval($id, $newText)
    {
        $this->html = preg_replace("/(id=\"{$id}\")(.*?)(value=\")(.*?)(\")/", '$1$2${3}' . $newText . '$5', $this->html);
    }

    private function replaceCheckboxes($id, $newText)
    {
        $this->replaceRadios($id, $newText);
    }

    private function replaceRadios($id, $newText)
    {
        $translations = explode("\n", $newText);
        foreach($translations as $k => $translation)
        {
            $this->replaceDefaultval("{$id}-{$k}", $translation);
            $this->replaceRadioLabel("{$id}-{$k}", $translation);
        }
    }

    /**
     * (id="contact-0".*?value=".*?>)(.*?)(<)
     */
    private function replaceRadioLabel($id, $newText)
    {
        $this->html = preg_replace("/(id=\"{$id}\".*?value=\".*?>)(.*?)(<)/", '${1}' . $newText . '$3', $this->html);
    }

    private function replaceOptions($id, $newText)
    {
        preg_match("/id=\"{$id}\".*?<\/select/", $this->html, $selectTag);
        $selectTag = $selectTag[0];

        preg_match_all("/(<option value=\")(.*?)(\">)(.*?)(<\/option)/", $selectTag, $options, PREG_SET_ORDER);

        $newSelectTag = $selectTag;
        $translations = explode("\n", $newText);
        foreach($translations as $k => $translation) {
            if(!isset($options[$k])) break;
            $oldTag = array_shift($options[$k]);
            $newTag = $options[$k];
            if($this->isTranslationOptionDefaultSelected($translation)) {
                $translation = substr($translation, 1);
                $newTag[2] = '" selected="selected">';
            }
            $newTag[1] = $translation;
            $newTag[3] = $translation;
            $newTag = implode('', $newTag);
            $newSelectTag = str_replace($oldTag, $newTag, $newSelectTag);
        }
        $this->html = str_replace($selectTag, $newSelectTag, $this->html);
    }

    private function isTranslationOptionDefaultSelected($translationText)
    {
        return substr($translationText, 0, 1) == '*';
    }

    private function replaceEmptyoption($id, $newText)
    {
        preg_match("/id=\"{$id}\".*?<\/select/", $this->html, $selectTag);
        $selectTag = $selectTag[0];

        $newSelectTag = $selectTag;
        $newSelectTag = preg_replace('/(optionsCaption: \')(.*?)\'/', '${1}' . $newText . "'", $newSelectTag);
        $newSelectTag = preg_replace('/(<option value="">)(.*?)(<\/option>)/', '${1}' . $newText . '${3}', $newSelectTag);

        $this->html = str_replace($selectTag, $newSelectTag, $this->html);
    }

    private function replaceNotloggedintext($id, $newText)
    {
        $this->html = preg_replace('/(pc-not-logged-in-text" for="' . $id . '">)(.*?)(<\/div>)/', '${1}' . $newText . '${3}', $this->html);
    }

    /**
     * @param $id
     * @param $newText
     * (id="message".*?>)(.*?)(<)
     */
    private function replaceContents($id, $newText)
    {
        $this->html = preg_replace("/(id=\"{$id}\".*?>)(.*?)(<\/textarea)/", '${1}' . $newText . '$3', $this->html);
    }

    /**
     * @param $id
     * @param $newText
     * Match:
     * (id="name".*?help-block">)(.*?)(<\/p>)
     * Replace:
     * (?<=id="selectbasic")(.*?help-block">)(.*?)(<\/p>)
     */
    private function replaceHelptext($id, $newText)
    {
        preg_match("/(id=\"{$id}-?0?\".*?help-block\">)(.*?)(<\/p>)/", $this->html, $field);
        if(empty($field) || stristr($field[0], 'form-group')){
            return;
        }
        $oldTag = $field[0];
        $newTag = $field[1] . $newText . $field[3];
        $this->html = str_replace($oldTag, $newTag, $this->html);
    }

    /**
     * text content
     * @param $id
     * @param $newText
     */
    private function replaceContent($id, $newText, $translation)
    {
        if($translation['parentFieldType'] == 'Form Section') {
            $this->html = preg_replace(
                "/(sectiontitle.*? {11})({$translation['original']})( {11})/",
                '${1}' . $newText . '$3',
                $this->html
            );
        }else{
            $this->html = str_replace(">{$translation['original']}<", ">{$newText}<", $this->html);
        }
    }

    /**
     * Upload message
     * @param $id
     * @param $newText
     */
    private function replaceDroptext($id, $newText)
    {
        $this->html = preg_replace("/(id=\"{$id}\".*?fs-upload-target.*?>)(.*?)(<)/", '${1}' . $newText . '$3', $this->html);
    }

    /**
     * @param $id
     * @param $newText
     * (id="submitform".*?>)(.*?)(<)
     */
    private function replaceButtonlabel($id, $newText)
    {
        $this->html = preg_replace("/(id=\"{$id}\".*?>)(.*?)(<)/", '${1}' . $newText . '$3', $this->html);
    }

    /**
     * @param $id
     * @param $newText
     * (pagetitle=")(New Form Page)(".*?)(New Form Page)
     */
    private function replacePagetitle($id, $newText)
    {
        $this->html = preg_replace(
            "/(pagetitle=\")({$id})(\".*?)({$id})/",
            '${1}' . $newText . '${3}' . $newText,
            $this->html
        );
    }

    private function replaceNexttext($id, $newText)
    {
        $this->replaceFormPageButton($id, $newText, 'next');
    }

    private function replacePrevtext($id, $newText)
    {
        $this->replaceFormPageButton($id, $newText, 'prev');
    }

    /**
     * @param $id
     * @param $newText
     * (pagetitle="New Form Page".*?nexttext=")(.*?)(")
     * @param $type 'prev' or 'next'
     */
    private function replaceFormPageButton($id, $newText, $type)
    {

        $formPage = $this->getTranslationById($id);
        if($this->getNewText($formPage)){
            $id = $this->getNewText($formPage);
        }

        $this->html = preg_replace(
            "/(pagetitle=\"{$id}\".*?{$type}text=\")(.*?)(\")/",
            '${1}' . $newText . '${3}',
            $this->html
        );
    }

    private function getTranslationById($id)
    {
        $key = array_search($id, array_column($this->translations, 'id'));
        return $this->translations[$key];
    }

    private function getNewText($translation)
    {
        if(!empty($translation['translations'][$this->getStoreIdTranslationKey()])) {
            return $translation['translations'][$this->getStoreIdTranslationKey()];
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

    public function translateFormTitle($html)
    {
        $translations = $this->translations;
        if(!$translations || empty($translations['frontend_title'])) {
            return $html;
        }
        $newText = $this->getNewText($translations['frontend_title']);
        if(!$newText){
            return $html;
        }
        return preg_replace("/(<legend.*?>)(.*?)(<)/", '${1}' . $newText . '$3', $html);
    }

    public function getTranslatedSuccessMessage()
    {
        $translations = $this->translations;
        if(!$translations || empty($translations['frontend_success_message'])) {
            return '';
        }
        return $this->getNewText($translations['frontend_success_message']);
    }

    private function getGeneralTranslation()
    {
        return json_decode($this->getCurrentForm()->getGeneralTranslation(), true);
    }


}