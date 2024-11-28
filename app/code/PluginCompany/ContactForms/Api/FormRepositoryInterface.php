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

namespace PluginCompany\ContactForms\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface FormRepositoryInterface
{


    /**
     * Save Form
     * @param \PluginCompany\ContactForms\Api\Data\FormInterface $form
     * @return \PluginCompany\ContactForms\Api\Data\FormInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function save(
        \PluginCompany\ContactForms\Api\Data\FormInterface $form
    );

    /**
     * Retrieve Form
     * @param string $formId
     * @return \PluginCompany\ContactForms\Api\Data\FormInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getById($formId);

    /**
     * Retrieve Form matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \PluginCompany\ContactForms\Api\Data\FormSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Form
     * @param \PluginCompany\ContactForms\Api\Data\FormInterface $form
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function delete(
        \PluginCompany\ContactForms\Api\Data\FormInterface $form
    );

    /**
     * Delete Form by ID
     * @param string $formId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    
    public function deleteById($formId);
}
