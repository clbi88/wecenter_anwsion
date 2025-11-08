<?php

if (!defined('IN_ANWSION'))
{
    exit();
}

/**
 * Prpcrypt class using OpenSSL (replaces deprecated Mcrypt)
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 * Updated for PHP 7.4+ compatibility
 */
class Services_Weixin_Prpcrypt
{
    public $key;

    /**
     * Constructor (PHP 7+ style)
     * @param string $k Base64 encoded encryption key
     */
    function __construct($k)
    {
        $this->key = base64_decode($k . "=");
    }

    /**
     * Legacy PHP4 constructor for backward compatibility
     * @param string $k Base64 encoded encryption key
     */
    function Services_Weixin_Prpcrypt($k)
    {
        $this->__construct($k);
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @param string $appid 应用ID
     * @return array 加密后的密文 [状态码, 密文]
     */
    public function encrypt($text, $appid)
    {
        try {
            // 获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();
            $text = $random . pack("N", strlen($text)) . $text . $appid;

            // 使用自定义的填充方式对明文进行补位填充
            $pkc_encoder = new Services_Weixin_PKCS7Encoder;
            $text = $pkc_encoder->encode($text);

            // IV = key的前16字节
            $iv = substr($this->key, 0, 16);

            // 使用OpenSSL加密 (RIJNDAEL_128 = AES-128-CBC)
            $encrypted = openssl_encrypt(
                $text,
                'AES-256-CBC',
                $this->key,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                $iv
            );

            if ($encrypted === false) {
                return array(Services_Weixin_ErrorCode::$EncryptAESError, null);
            }

            // 使用BASE64对加密后的字符串进行编码
            return array(Services_Weixin_ErrorCode::$OK, base64_encode($encrypted));
        } catch (Exception $e) {
            return array(Services_Weixin_ErrorCode::$EncryptAESError, null);
        }
    }

    /**
     * 对密文进行解密
     * @param string $encrypted 需要解密的密文
     * @param string $appid 应用ID
     * @return array 解密得到的明文 [状态码, 明文, AppID]
     */
    public function decrypt($encrypted, $appid)
    {
        try {
            // 使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);

            // IV = key的前16字节
            $iv = substr($this->key, 0, 16);

            // 使用OpenSSL解密 (RIJNDAEL_128 = AES-128-CBC)
            $decrypted = openssl_decrypt(
                $ciphertext_dec,
                'AES-256-CBC',
                $this->key,
                OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
                $iv
            );

            if ($decrypted === false) {
                return array(Services_Weixin_ErrorCode::$DecryptAESError, null);
            }
        } catch (Exception $e) {
            return array(Services_Weixin_ErrorCode::$DecryptAESError, null);
        }

        try {
            // 去除补位字符
            $pkc_encoder = new Services_Weixin_PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);

            // 去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16) {
                return array(Services_Weixin_ErrorCode::$IllegalBuffer, null);
            }

            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_appid = substr($content, $xml_len + 4);

            if (!$appid) {
                $appid = $from_appid;
            }
        } catch (Exception $e) {
            return array(Services_Weixin_ErrorCode::$IllegalBuffer, null);
        }

        if ($from_appid != $appid) {
            return array(Services_Weixin_ErrorCode::$ValidateAppidError, null);
        }

        return array(0, $xml_content, $appid);
    }

    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    function getRandomStr()
    {
        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
}

?>
