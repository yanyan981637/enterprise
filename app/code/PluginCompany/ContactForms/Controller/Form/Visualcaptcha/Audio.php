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
namespace PluginCompany\ContactForms\Controller\Form\Visualcaptcha;

class Audio extends AbstractCaptcha
{
    public function runExecute()
    {
        $captcha = $this->getCaptcha();
        $audio = $captcha->streamAudio( array(), 'mp3');
        $this->streamFile($audio);
    }
}