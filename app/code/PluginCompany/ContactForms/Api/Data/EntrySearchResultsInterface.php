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

namespace PluginCompany\ContactForms\Api\Data;

interface EntrySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Entry list.
     * @return \PluginCompany\ContactForms\Api\Data\EntryInterface[]
     */
    
    public function getItems();

    /**
     * Set form_id list.
     * @param \PluginCompany\ContactForms\Api\Data\EntryInterface[] $items
     * @return $this
     */
    
    public function setItems(array $items);
}
