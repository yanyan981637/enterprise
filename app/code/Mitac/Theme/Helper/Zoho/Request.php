<?php

namespace Mitac\Theme\Helper\Zoho;

use Exception;

class Request
{
    /**
     * @var Config $config
     * */
    protected $config;


    private $access_token;

    public function __construct(
        Config $config,
    )
    {
        $this->config = $config;
    }

    /**
     * @throws Exception
     */
    protected function checkEnable(){
        $isEnable = $this->config->getEnable();
        if($isEnable){
            throw new Exception(__('Sorry! This feature is not currently enabled.'));
        }
    }

    public function getAccessToken()
    {
        if($this->access_token){
            return $this->access_token;
        }

        try {
            $ci = curl_init();
            curl_setopt_array($ci, array(

            ));
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }

        return $this->access_token;
    }

    public function subscribeRequest($email){
        try {
            $this->checkEnable();
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    public function unsubscribeRequest($email){
        try {
            $this->checkEnable();
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
