<?php

namespace Mitac\Theme\Helper\Zoho;
use Mitac\Theme\Helper\Logger;
use Exception;

class Request
{
    /**
     * @var Config $config
     * */
    protected $config;

    /**
     * @var Logger $logger
     */
    protected $logger;
    public function __construct(
        Config $config,
    )
    {
        $this->config = $config;
        $this->logger = new Logger('zoho_subscribe.log');
    }

    /**
     * @throws Exception
     */
    public function checkEnable(){
        $isEnable = $this->config->getEnable();
        if(!$isEnable){
            throw new Exception(__('Sorry! This feature is not currently enabled.'));
        }
    }

    public function request($type, $email)
    {
        $this->logger->info('-----------start--------------');
        try {
            $access_token = $this->getAccessToken();
            $result = null;
            switch ($type) {
                case 1:
                    $result = $this->subscribeRequest($access_token, $email);
                break;
                case 2:
                    $result = $this->unsubscribeRequest($access_token, $email);
                break;
                default:
                    throw new Exception(__('fail'));
            }
            $this->logger->info('result: ');
            $this->logger->info(print_r($result, true));
            if(isset($result['status']) && $result['status'] == 'success'){
                return [
                    'status' => 'success',
                    'code' => 200,
                    'msg' => $result['message'] ?? 'fail'
                ];
            }

            if(isset($result['status']) && $result['status'] == 'error'){
                return [
                    'status' => 'error',
                    'code' => 201,
                    'msg' => $result['message'] ?? "fail"
                ];
            }
            throw new Exception('fail');

        }catch (Exception $e){
            $this->logger->info($e->getMessage());
            throw new Exception($e->getMessage());
        } finally {
            $this->logger->info('-----------end--------------');
        }
    }

    private function getAccessToken()
    {

        try {
            $ci = curl_init();
            curl_setopt_array($ci, array(
                CURLOPT_URL => $this->config->getApiAuthPath(),
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query([
                    'client_id' => $this->config->getClientId(),
                    'client_secret' => $this->config->getClientSecret(),
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $this->config->getRefreshToken(),
                ]),
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
            ));
            $response = curl_exec($ci);
            $result = json_decode($response, true);

            if (!isset($result['access_token'])) {
                throw new Exception("Access token could not be generated");
            }
            return $result['access_token'];
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function subscribeRequest($access_token, $email){

        try {

            $cs = curl_init();
            curl_setopt_array($cs, array(
                CURLOPT_URL => $this->config->getApiSubscribePath(),
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query([
                    'resfmt' => 'JSON',
                    'listkey' => $this->config->getlistkey(),
                    'wfTrigger' => true,
                    'contactinfo' => "{Contact Email: {$email}}",
                ]),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token,
                ]
            ));
            $response = curl_exec($cs);
            $result = json_decode($response, true);
            return $result;
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    private function unsubscribeRequest($access_token, $email){
        try {

            $cs = curl_init();
            curl_setopt_array($cs, array(
                CURLOPT_URL => $this->config->getApiUnsubscribePath(),
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query([
                    'resfmt' => 'JSON',
                    'listkey' => $this->config->getlistkey(),
                    'wfTrigger' => true,
                    'contactinfo' => "{Contact Email: {$email}}",
                ]),
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => 1,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $access_token,
                ]
            ));
            $response = curl_exec($cs);
            $result = json_decode($response, true);
            return $result;
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

}
