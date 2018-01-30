<?php

/**
 * onepass server sdk
 *
 * @author Tanxu and Parcuse Deng
 */
class GMessageLib {
    const GT_SDK_VERSION = 'php_1.0.0';

    public static $connectTimeout = 2;
    public static $socketTimeout  = 2;

    private $response;
    private $public_key = "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDB45NNFhRGWzMFPn9I7k7IexS5
XviJR3E9Je7L/350x5d9AtwdlFH3ndXRwQwprLaptNb7fQoCebZxnhdyVl8Jr2J3
FZGSIa75GJnK4IwNaG10iyCjYDviMYymvCtZcGWSqSGdC/Bcn2UCOiHSMwgHJSrg
Bm1Zzu+l8nSOqAurgQIDAQAB
-----END PUBLIC KEY-----";

    public function __construct($onepass_id, $private_key) {
        $this->onepass_id  = $onepass_id;
        $this->private_key = $private_key;
        $this->pubkey = openssl_pkey_get_public($this->public_key);
    }


    public function check_gateway($process_id, $accesscode, $phone, $user_id = "test", $ssl= false, $testbutton=true) {
        $query = array(
            "process_id" => $process_id,
            "timestamp"=>time(),
            "accesscode"=>$accesscode,
            "custom"=>$this->onepass_id,
            "phone"=>$phone,
            "user_id"=>$user_id,
            "sdk"=> self::GT_SDK_VERSION
        );
        if ($ssl == true) {
            $url = "https://onepass.geetest.com/check_gateway.php";
        }
        else{
            $url = "http://onepass.geetest.com/check_gateway.php";
        }
        if ($testbutton == false) {
            $sign_data = $this->onepass_id."&&".md5($this->private_key)."&&".(string)(time()*1000);
            $sign = $this->rsa_encrypt($sign_data);
            $query['sign'] = $sign;
        }
        $codevalidate = $this->post_request($url, $query);
        $obj = json_decode($codevalidate,true);
        if ($obj === false){
            return 0;
        }
        if ($obj['result'] == 0) {
            $content = $this->private_key."gtmessage".$process_id;
            if ($obj['content'] == md5($content)) {
                return 1;
            }
            else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function check_message($process_id, $message_id, $message_number, $phone, $user_id = "test",$ssl= false) {
        $query = array(
            "process_id" => $process_id,
            "timestamp"=>time(),
            "message_id"=>$message_id,
            "message_number"=>$message_number,
            "custom"=>$this->onepass_id,
            "phone"=>$phone,
            "user_id" =>$user_id,
            "sdk"=> self::GT_SDK_VERSION
        );
        if ($ssl == true) {
            $url = "https://onepass.geetest.com/check_message.php";
        }
        else{
            $url = "http://onepass.geetest.com/check_message.php";
        }
        $codevalidate = $this->post_request($url, $query);
        $obj = json_decode($codevalidate,true);
        if ($obj === false){
            return 0;
        }
        if ($obj['result'] == 0) {
            $content = $this->private_key."gtmessage".$process_id;
            if ($obj['content'] == md5($content)) {
                return 1;
            }
            else {
                return 0;
            }
        } else {
            return 0;
        }
    }


    /**
     *
     * @param       $url
     * @param array $postdata
     * @return mixed|string
     */
    private function post_request($url, $postdata = '') {
        if (!$postdata) {
            return false;
        }

        $data = http_build_query($postdata);
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connectTimeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, self::$socketTimeout);

            //不可能执行到的代码
            if (!$postdata) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            } else {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            $data = curl_exec($ch);

            if (curl_errno($ch)) {
                $err = sprintf("curl[%s] error[%s]", $url, curl_errno($ch) . ':' . curl_error($ch));
                $this->triggerError($err);
            }

            curl_close($ch);
        } else {
            if ($postdata) {
                $opts    = array(
                    'http' => array(
                        'method'  => 'POST',
                        'header'  => "Content-type:application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                        'content' => $data,
                        'timeout' => self::$connectTimeout + self::$socketTimeout
                    )
                );
                $context = stream_context_create($opts);
                $data    = file_get_contents($url, false, $context);
            }
        }

        return $data;
    }

    private function rsa_encrypt($data) {
        if (openssl_public_encrypt($data, $encrypted, $this->pubkey)){
          $data = base64_encode($encrypted);
        }
        else{
            throw new Exception('encrypt wrong');
        }
        return $data;
    }


    
    /**
     * @param $err
     */
    private function triggerError($err) {
        trigger_error($err);
    }
}
