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
namespace PluginCompany\ContactForms\Model\Email\Source;

use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Templates implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $templates = [
            [
                'label' => 'Default Templates',
                'value' => [
                    [
                        'label' => 'Notification Template (HTML)',
                        'value' => 'custom_contact_forms_notification'
                    ],
                    [
                        'label' => 'Notification Template (Plain Text)',
                        'value' => 'custom_contact_forms_notification_plain'
                    ]
                ]
            ]
        ];
        $customTemplates = $this->getCustomTemplates();
        if(!empty($customTemplates)) {
            $templates[] = [
                'label' => 'Custom Templates',
                'value' => $customTemplates
            ];
        }
        return $templates;
    }

    private function getCustomTemplates()
    {
        return $this->collectionFactory
            ->create()
            ->toOptionArray();
    }
}
