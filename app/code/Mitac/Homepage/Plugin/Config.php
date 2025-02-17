<?php
namespace Mitac\Homepage\Plugin;

class Config
{
    protected $activeEditor;

    public function __construct(\Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor)
    {
        $this->activeEditor = $activeEditor;
    }

    public function afterGetConfig(
        \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface,
        \Magento\Framework\DataObject $result
    ) 
    {
        $editor = $this->activeEditor->getWysiwygAdapterPath();

        if(strpos($editor,'tinymce4Adapter'))
        {

            if (($result->getDataByPath('settings/menubar')) || ($result->getDataByPath('settings/toolbar')) || ($result->getDataByPath('settings/plugins')))
            {
                return $result;
            }

            $settings = $result->getData('settings');
            if (!is_array($settings)) 
            {
                $settings = [];
            }

            $settings['menubar'] = true;
            $settings['toolbar'] = 'undo redo | styleselect | fontsizeselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | code';
            $settings['plugins'] = 'textcolor image code';

            $result->setData('settings', $settings);
                return $result;
        }
        else
        {
            return $result;
        }
    }
}
