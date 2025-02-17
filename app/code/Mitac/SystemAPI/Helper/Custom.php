<?php

namespace Mitac\SystemAPI\Helper;

use PhpParser\Node\Expr\Cast\Array_;

class Custom extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CHECK_PASSWD_STATUS = [
        '200' => ['flag' => true, 'msg' => "The call to API is successful"],
        '433' => ['flag' => false, 'msg' => "old password or password error"],
        '434' => ['flag' => false, 'msg' => "User cannot sign in without a confirmed email"],
        '435' => ['flag' => false, 'msg' => "User is currently locked out"],
        '436' => ['flag' => false, 'msg' => "The password encryption method is outdated. Please reset your password."],
        '501' => [
            'flag' => false,
            'msg' => 'The account sign-in was incorrect or your account is disabled temporarily. '
                . 'Please wait and try again later.'
        ],
    ];

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var StoreManagerInterface
     */
    protected $_store;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scope;

    /**
     * @var \Mitac\SystemAPI\Model\Log
     */
    protected $logger;

    protected $ScopeStore;

    /**
     * @var string
     */
    protected $api_type;
    protected $api_auth_path;
    protected $endpoint_prefix;

	public function __construct
	(
        \Magento\Framework\App\ResourceConnection $_resource,
        \Magento\Store\Model\StoreManagerInterface $_store,
		\Magento\Framework\App\Config\ScopeConfigInterface $_scope,
        \Mitac\SystemAPI\Model\Log $logger
	)
	{
        $this->_resource = $_resource;
        $this->_store = $_store;
        $this->_scope = $_scope;
        $this->logger = $logger;
        $this->api_type = $this->_scope->getValue("mdt/general/api_type");
        $this->api_auth_path = $this->_scope->getValue("mdt/general/api_auth_path");
        $this->endpoint_prefix = $this->_scope->getValue("mdt/general/endpoint_prefix");

        $this->ScopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /*
	 * Get Customer Token
	 */
    public function getCustomToken($apiURL, $account, $password)
    {
        if ($this->api_type == 'ID4') {
            return $this->getCustomTokenID4($apiURL, $account, $password);
        } else {
            return $this->getCustomTokenCAS($apiURL, $account, $password);
        }
    }

    /*
	 * Get Customer Token CAS
	 */
    public function getCustomTokenCAS($apiURL, $account, $password)
    {
        $sysdata = ["account" => $account, "pswd" => $password];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->api_auth_path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf8",
        ]);
        $output = curl_exec($ch);

        $headers = [];
        $data = explode("\n", rtrim($output));
        $headers["status"] = $data[0];
        array_shift($data);
        foreach ($data as $part) {
            $middle = explode(":", $part, 2);
            if (!isset($middle[1])) {
                $middle[1] = null;
            }
            $headers[trim($middle[0])] = trim($middle[1]);
        }
        curl_close($ch);

        if (!empty($headers["Authorization"])) {
            $token = $headers["Authorization"];
        } else {
            $token = "";
        }

        return $token;
    }

    /*
	 * Get Customer Token ID4
	 */
    public function getCustomTokenID4($apiURL, $account, $password)
    {
        $sysdata = ["account" => $account, "pswd" => $password];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->api_auth_path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
        ]);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        $data = json_decode($response);

        if ($info['http_code'] == 200) {
            $token = $data->access_token;
        } else {
            $token = "";
        }

        $token = 'Bearer ' . $token;

        return $token;
    }

    /*
	 * Create Customer Account
	 */
    public function addCustom($apiURL, $token, $CustData)
    {
        $email = trim($CustData["email"]);
        $pswd = trim($CustData["pswd"]);
        $is_agree = $CustData["is_agree"];
        $is_news = $CustData["is_news"];
        $first_name = trim($CustData["first_name"]);
        $last_name = trim($CustData["last_name"]);
        $mobile_phone = null;
        $region = trim($CustData["region"]);
        $from_system = trim($CustData["from_system"]);

        $sysdata = [
            "email" => $email,
            "pswd" => $pswd,
            "is_agree" => $is_agree,
            "is_agree_time" => date("Y-m-d H:i:s"),
            "is_news" => $is_news,
            "is_news_time" => date("Y-m-d H:i:s"),
            "customerInfo" => [
                "first_name" => $first_name,
                "last_name" => $last_name,
                "mobile_phone" => $mobile_phone,
                "region" => $region,
                "from_system" => $from_system
            ],
        ];
        if (isset($CustData["is_residing"])) {
            $is_residing = $CustData["is_residing"];
            $sysdata["is_residing"] = $is_residing;
        }

        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/create");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);

        $Custresult = json_decode($result, 1);
        curl_close($ch);

        $customid = "";

        $this->logger->info('---- create customer result - addCustom ----');

        if (!empty($Custresult["status"])) {
            $this->logger->info(print_r($Custresult, 1));
            if (
                $Custresult["status"] == "200" ||
                $Custresult["status"] == "448"
            ) {
                $customid = $Custresult["data"]["customer_sys_id"];
            }
        } else {
            $this->logger->info('Cannot create customer with email: ' . $email);
            $customid = "";
        }

        return $customid;
    }

    /*
	 * Create Customer Account
	 */
    public function getCustomer($apiURL, $token, $customid)
    {
        $sysdata = ["customer_sys_id" => $customid];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/get");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Custresult["status"])) {
            if ($Custresult["status"] == "200") {
                $customdata = $Custresult["data"];
            } else {
                $customdata = "";
            }
        } else {
            $customdata = "";
        }

        return $customdata;
    }

    /*
	 * Check Customer Account
	 */
    public function checkExistCustomer($apiURL, $token, $email)
    {
        $sysdata = ["email" => $email];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/isExist");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Custresult["status"])) {
            if (
                $Custresult["status"] == "200" ||
                $Custresult["status"] == "448"
            ) {
                $customerId = $Custresult["data"];
            } else {
                $customerId = false;
            }
        } else {
            $customerId = false;
        }

        return $customerId;
    }

    /*
	 * Get Customer Devices List
	 */
    public function getCustomerDevices($apiURL, $token, $customid)
    {
        $sysdata = ["customer_sys_id" => $customid];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customerDevice/get");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Custresult["status"])) {
            if (
                $Custresult["status"] == "200" ||
                $Custresult["status"] == "448"
            ) {
                $devices = $Custresult["data"];
            } else {
                $devices = "error";
            }
        } else {
            $devices = "error";
        }

        return $devices;
    }

    /*
	 * Create Customer Devices
	 */
    public function addCustomerDevices($apiURL, $token, $customid, $devices)
    {
        $sysdata = [
            "customer_sys_id" => $customid,
            "device" => [
                "device_id" => $devices["device_id"],
                "device_name" => $devices["device_name"],
                "sn" => $devices["sn"],
                "purchase_date" => $devices["purchase_date"],
                "creation_time" => $devices["creation_time"],
            ],
        ];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customerDevice/add");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = "success";
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Delete Customer Devices
	 */
    public function delCustomerDevices($apiURL, $token, $customid, $sn)
    {
        $sysdata = [
            "customer_sys_id" => $customid,
            "sn" => $sn,
        ];
        $data_string = json_encode($sysdata);

        if ($this->api_type == 'ID4') {
            $method_type = "DELETE";
        } else {
            $method_type = "PUT";
        }

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customerDevice/delete");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method_type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = "success";
            } elseif ($Deviceresult["status"] == "445") {
                $Processresult = "success";
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Get Devices list
	 */
    public function getDevicesAllList($apiURL, $token)
    {
        $data_string = "";

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/devices/getAllList");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = $Deviceresult["data"];
            } else {
                $Processresult = "";
            }
        } else {
            $Processresult = "";
        }

        return $Processresult;
    }

    /*
	 * Update Customer Account
	 */
    public function updCustom($apiURL, $token, $customid, $CustData)
    {
        //---------------------------------------------------------------------------------------//
        $first_name = trim($CustData["first_name"]);
        $last_name = trim($CustData["last_name"]);

        if (array_key_exists("is_residing", $CustData)) {
            $is_residing = $CustData["is_residing"];
        } else {
            $is_residing = null;
        }

        if (array_key_exists("mobile_phone", $CustData)) {
            $mobile_phone =
                trim($CustData["mobile_phone"]) == "" ? null : trim($CustData["mobile_phone"]);
        } else {
            $mobile_phone = null;
        }

        //---------------------------------------------------------------------------------------//
        $sysdata = [
            "customer_sys_id" => $customid,
            "is_residing" => $is_residing,
            "customerInfo" => [
                "first_name" => $first_name,
                "last_name" => $last_name,
                "mobile_phone" => $mobile_phone
            ],
        ];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/update");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Updresult = json_decode($result, 1);
        curl_close($ch);

        $Processresult = "error";

        if (!empty($Updresult["status"])) {
            if ($Updresult["status"] == "200") {
                $Processresult = "success";
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /**
     * Update Customer Account
     *
     * @param string $customid
     * @param array $CustData
     *
     * @return string
     */
    public function updCustomDirect($customid, $CustData)
    {
        $apiURL = $this->_scope->getValue("mdt/general/url");
        $apiaccount = $this->_scope->getValue("mdt/general/account");
        $apipassword = $this->_scope->getValue("mdt/general/password");
        $token = $this->getCustomToken($apiURL, $apiaccount, $apipassword);

        //---------------------------------------------------------------------------------------//
        $first_name = trim($CustData["first_name"]);
        $last_name = trim($CustData["last_name"]);

        $sysdataPre = [
            "customer_sys_id" => $customid,
        ];

        if (array_key_exists("is_news", $CustData)) {
            $is_news = $CustData["is_news"];
			$sysdataPre["is_news"]	= $is_news;
        }

        $customerInfo["customerInfo"] = [
            "first_name" => $first_name,
            "last_name" => $last_name
        ];
        //---------------------------------------------------------------------------------------//

        $sysdata = array_merge($sysdataPre, $customerInfo);
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/update");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Updresult = json_decode($result, 1);
        curl_close($ch);

        $Processresult = "error";

        if (!empty($Updresult["status"])) {
            if ($Updresult["status"] == "200") {
                $Processresult = "success";
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Update Customer Newsletter
	 */
    public function updCustomNewsletter($apiURL, $token, $customid, $CustData)
    {
        if (isset($CustData["is_news"])) {
            $is_news = $CustData["is_news"];
            $sysdata = [
                "customer_sys_id" => $customid,
                "is_news" => $is_news,
                "customerInfo" => new \ArrayObject()
            ];
            $data_string = json_encode($sysdata);
            $data_string = json_encode($sysdata);

            $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/update");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json; charset=utf-8",
                "Authorization:" . $token,
            ]);
            $result = curl_exec($ch);
            $Updresult = json_decode($result, 1);
            curl_close($ch);

            $Processresult = "error";

            if (!empty($Updresult["status"])) {
                if ($Updresult["status"] == "200") {
                    $Processresult = "success";
                } else {
                    $Processresult = "error";
                }
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Update Customer Newsletter Direct
	 */
    public function updCustomNewsletterDirect($adcustomid, $CustData)
    {
        $apiurl = $this->_scope->getValue("mdt/general/url");
        $apiaccount = $this->_scope->getValue("mdt/general/account");
        $apipassword = $this->_scope->getValue("mdt/general/password");
        $token = $this->getCustomToken($apiurl, $apiaccount, $apipassword);

        // Update Customer Email
        $CASCustomData = $this->updCustomNewsletter($apiurl, $token, $adcustomid, $CustData);

        return $CASCustomData;
    }

    /*
	 * Check Customer Password
	 */
    public function checkCustomerPassword($apiURL, $token, $customid, $password)
    {
        $sysdata = ["customer_sys_id" => $customid, "pswd" => $password];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/checkPassword");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
		curl_close($ch);

		if (!empty($Custresult["status"])) {
            $customData = isset(self::CHECK_PASSWD_STATUS[$Custresult["status"]])
                ? self::CHECK_PASSWD_STATUS[$Custresult["status"]]
                : self::CHECK_PASSWD_STATUS['501'];

		} else {
            $customData = self::CHECK_PASSWD_STATUS['501'];
		}

		return $customData;
    }

    /*
	 * Reset Customer Password
	 */
    public function resetCustomerPassword($apiURL, $token, $customid, $password)
    {
        $sysdata = ["customer_sys_id" => $customid, "new_pswd" => $password];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/resetPassword");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Custresult["status"])) {
            if ($Custresult["status"] == "200") {
                $customdata = true;
            } else {
                $customdata = false;
            }
        } else {
            $customdata = false;
        }

        return $customdata;
    }

    /*
	 * Update Customer Password
	 */
    public function updCustomerPassword($apiURL, $token, $customid, $oldpassword, $newpassword)
    {
        $sysdata = [
            "customer_sys_id" => $customid,
            "old_pswd" => $oldpassword,
            "new_pswd" => $newpassword,
        ];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/updatePassword");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Custresult["status"])) {
            if ($Custresult["status"] == "200") {
                $customdata = true;
            } else {
                $customdata = false;
            }
        } else {
            $customdata = false;
        }

        return $customdata;
    }

    /*
	 * Update Customer Email
	 */
    public function updCustomerEmail($apiURL, $token, $customid, $newemail)
    {
        $sysdata = [
            "customer_sys_id" => $customid,
            "new_email" => $newemail,
        ];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/customer/updateEmail");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Custresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Custresult["status"])) {
            if ($Custresult["status"] == "200") {
                $customdata = true;
            } else {
                $customdata = false;
            }
        } else {
            $customdata = false;
        }

        return $customdata;
    }

    /*
	 * Region List
	 */
    public function getRegionList($apiURL, $token)
    {
        $ch = curl_init($apiURL . $this->endpoint_prefix . "/region/getList");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = $Deviceresult["data"];
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Category List
	 */
    public function getCategoryList($apiURL, $token, $brand)
    {
        $sysdata = ["brand" => $brand];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/category/getList");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = $Deviceresult["data"];
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Devices List
	 */
    public function getDevicesList($apiURL, $token, $brand, $category_id, $region, $status = true)
    {
        $sysdata = [
            "brand" => $brand,
            "category_id" => $brand,
            "region" => $region,
            "status" => $status,
        ];
        $data_string = json_encode($sysdata);

        $ch = curl_init($apiURL . $this->endpoint_prefix . "/devices/getList");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = $Deviceresult["data"];
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /*
	 * Device Region List
	 */
    public function getDeviceRegionList($apiURL, $token)
    {
        $ch = curl_init($apiURL . $this->endpoint_prefix . "/devices/getDeviceRegionList");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json; charset=utf-8",
            "Authorization:" . $token,
        ]);
        $result = curl_exec($ch);
        $Deviceresult = json_decode($result, 1);
        curl_close($ch);

        if (!empty($Deviceresult["status"])) {
            if ($Deviceresult["status"] == "200") {
                $Processresult = $Deviceresult["data"];
            } else {
                $Processresult = "error";
            }
        } else {
            $Processresult = "error";
        }

        return $Processresult;
    }

    /**
     * @example http://magento.test/index.php/rest/V1/mitacproduct/register/
     * @param string adcustomid
     * @param int customid
     * @param int deviceid
     * @param string serialnumber
     * @param string purchase_date
     * @return string[]
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function addProduct($adcustomid, $customid, $deviceid, $serialnumber, $purchase_date)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //--------------------------------------------------------------------//
        $apiURL = $this->_scope->getValue("mdt/general/url");
        $apiAccount = $this->_scope->getValue("mdt/general/account");
        $apiPassword = $this->_scope->getValue("mdt/general/password");
        //--------------------------------------------------------------------//
        $adcustomid = trim($adcustomid);
        $customid = trim($customid);
        $deviceid = trim($deviceid);
        $serialnumber = trim($serialnumber);
        $buypurchase_date = trim($purchase_date);
        //--------------------------------------------------------------------//
        //Check Data
        #region
        if (
            empty($adcustomid) ||
            empty($customid) ||
            empty($deviceid) ||
            empty($serialnumber) ||
            empty($buypurchase_date)
        ) {
            $process = [
                "status" => "505",
                "message" => "Data Error",
            ];
            return [$process];
        }
        #end
        //--------------------------------------------------------------------//
        //購買日期驗證
        #region
        if (
            !preg_match(
                "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",
                $buypurchase_date
            )
        ) {
            $process = [
                "status" => "505",
                "message" => "Purchase Date format is error",
            ];
            return [$process];
        }
        #end
        //--------------------------------------------------------------------//
        //開始註冊
        #region
        $token = $this->getCustomToken($apiURL, $apiAccount, $apiPassword);

        if (!empty($token)) {
            $customData = $this->getCustomer($apiURL, $token, $adcustomid);

            if (!empty($customData)) {
                //--------------------------------------------------------------------//
                //檢查註冊平台上產品是否註冊過
                #region
				$custdevices = $this->getCustomerDevices( $apiURL, $token, $adcustomid);

                if (!empty($custdevices)) {
                    foreach ($custdevices as $key => $value) {
                        if ($value["device_id"] == $deviceid) {
                            if ($value["sn"] == $serialnumber) {
                                $process = [
                                    "status" => "505",
                                    "message" => "This device sn is exists.",
                                ];
                                return [$process];
                            }
                        }
                    }
                }
                #end
                //--------------------------------------------------------------------//
                //撈取使用者產品註冊清單
                #region
                $alldevices = $this->getDevicesAllList($apiURL, $token);

                if (!empty($alldevices)) {
                    $TempChenk = [];
                    foreach ($alldevices as $deviceData) {
                        $TempChenk[$deviceData["device_id"]] = $deviceData["device_name"];
                    }

                    if (empty($TempChenk[$deviceid])) {
                        $process = [
                            "status" => "505",
                            "message" => "Sorry, there's a problem. If you keep seeing this, please contact us dircetly.",
                        ];
                        return [$process];
                    }
                } else {
                    $process = [
                        "status" => "505",
                        "message" => "Sorry, there's a problem. If you keep seeing this, please contact us dircetly.",
                    ];
                    return [$process];
                }
                #end
                //--------------------------------------------------------------------//
                //Create Custom Device
                #region
                $devicedata = [
                    "device_id" => $deviceid,
                    "device_name" => $TempChenk[$deviceid],
                    "sn" => $serialnumber,
                    "purchase_date" => $buypurchase_date,
                    "creation_time" => date("Y-m-d"),
                ];
				$addresult = $this->addCustomerDevices($apiURL,$token,$adcustomid,$devicedata);

                if ($addresult == "success") {
                    $process = [
                        "status" => "200",
                        "message" => "This device has been registed.",
                    ];
                    return [$process];
                } else {
                    $process = [
                        "status" => "505",
                        "message" => "There is a problem with the Serial Number you entered, please re-enter.",
                    ];
                    return [$process];
                }
                #end
                //--------------------------------------------------------------------//
            } else {
                $process = [
                    "status" => "505",
                    "message" => "Custom Data not found",
                ];
                return [$process];
            }
        } else {
            $process = [
                "status" => "505",
                "message" => "Error Customer Token",
            ];
            return [$process];
        }
        #end
        //--------------------------------------------------------------------//
    }

    /**
     * getCustomerSysIdDirect
     * @return string or
     */
    public function getCustomerSysIdDirect($email)
    {
        $apiurl = $this->_scope->getValue("mdt/general/url");
        $apiaccount = $this->_scope->getValue("mdt/general/account");
        $apipassword = $this->_scope->getValue("mdt/general/password");
        $token = $this->getCustomToken($apiurl, $apiaccount, $apipassword);

        //Check Cas Email
        $CASCustomData = $this->checkExistCustomer($apiurl, $token, $email);

        if ($CASCustomData) {
            return $CASCustomData["customer_sys_id"];
        } else {
            return false;
        }
    }

    /**
     * getCustomerSysIdDirect
     * @return string|bool
     */
    public function updCustomerEmailDirect($adcustomid, $email)
    {
        $apiurl = $this->_scope->getValue("mdt/general/url");
        $apiaccount = $this->_scope->getValue("mdt/general/account");
        $apipassword = $this->_scope->getValue("mdt/general/password");
        $token = $this->getCustomToken($apiurl, $apiaccount, $apipassword);

        // Update Customer Email
        $CASCustomData = $this->updCustomerEmail($apiurl, $token, $adcustomid, $email);

        return $CASCustomData;
    }

 /**
  * @description: 得到同步cas的region, 同sso. 因为 eshop & omo & mionext 三个站点region一样
  * @return {*}
  */
	public function getRegion(){
		return $this->_scope->getValue('mdt/general/api_region', $this->ScopeStore);
	}

    /**
     * get SSO Configuration
     * @return array
     */
    public function getSSOConfiguration()
    {
        $sso_url = $this->_scope->getValue('mdt/sso_setting/provider_url');
        $sso_client_id = $this->_scope->getValue('mdt/sso_setting/sso_client_id');
        $sso_client_secret = $this->_scope->getValue('mdt/sso_setting/sso_client_secret');
        $sso_from_system = $this->_scope->getValue('mdt/general/api_from_system');
        $sso_lang = $this->_scope->getValue('mdt/general/api_lang', $this->ScopeStore);
        $sso_region = $this->_scope->getValue('mdt/general/api_region', $this->ScopeStore);
        $m2_login_path = $this->_scope->getValue('mdt/sso_setting/m2_login_path', $this->ScopeStore);
        $m2_logout_redirect_path = $this->_scope->getValue('mdt/sso_setting/m2_logout_redirect_path', $this->ScopeStore);
        $sso_enabled = $this->_scope->getValue('mdt/sso_setting/sso_enabled', $this->ScopeStore);

        $base_url = $this->_store->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        if ($sso_enabled) {
            return [
                'sso_url' => $sso_url,
                'sso_client_id' => $sso_client_id,
                'sso_client_secret' => $sso_client_secret,
                'sso_from_system' => $sso_from_system,
                'sso_lang' => $sso_lang,
                'sso_region' => $sso_region,
                'm2_login_path' => $base_url . $m2_login_path,
                'm2_logout_redirect_path' => $base_url . $m2_logout_redirect_path,
                'sso_enabled' => $sso_enabled
            ];
        } else {
            return [
                'sso_enabled' => $sso_enabled
            ];
        }
    }

}