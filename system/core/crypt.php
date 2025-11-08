<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2014 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/

/**
 * Encryption class using OpenSSL (replaces deprecated Mcrypt)
 * Compatible with PHP 7.4+
 */
class core_crypt
{
    private $cipher = 'AES-256-ECB';

    public function __construct()
    {
        if (!function_exists('openssl_encrypt'))
        {
            exit('Error: OpenSSL extension not available. Please enable OpenSSL in your PHP configuration.');
        }
    }

    /**
     * Encode/Encrypt data
     * @param string $data Data to encrypt
     * @param string|null $key Encryption key (uses G_COOKIE_HASH_KEY if null)
     * @return string Encrypted data with cipher prefix
     */
    public function encode($data, $key = null)
    {
        $key = $this->get_key($key);
        $cipher = $this->get_cipher();

        // Compress and base64 encode data (same as original)
        $data = base64_encode(gzcompress($data));

        // Encrypt using OpenSSL
        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA);

        if ($encrypted === false) {
            exit('Error: Encryption failed');
        }

        // Return with cipher identifier for future compatibility
        return $cipher . '|' . $this->str_to_hex($encrypted);
    }

    /**
     * Decode/Decrypt data
     * @param string $data Encrypted data
     * @param string|null $key Decryption key (uses G_COOKIE_HASH_KEY if null)
     * @return string|false Decrypted data or false if decryption fails
     */
    public function decode($data, $key = null)
    {
        // Handle empty or invalid data
        if (empty($data)) {
            return false;
        }

        $key = $this->get_key($key);

        // Check if data has cipher prefix
        if ($cipher = strstr($data, '|', true))
        {
            $data = str_replace($cipher . '|', '', $data);
            $data = $this->hex_to_str($data);

            // Handle legacy mcrypt data
            if (strpos($cipher, 'rijndael') !== false || strpos($cipher, 'blowfish') !== false) {
                $result = $this->decode_legacy_mcrypt($data, $cipher, $key);
                // If legacy decrypt fails, return false (cookie/session will be invalidated)
                if ($result === false) {
                    return false;
                }
                return $result;
            }
        }
        else
        {
            // Legacy format without cipher prefix
            $cipher = $this->get_cipher();
            $data = base64_decode($data);
        }

        // Decrypt using OpenSSL
        $decrypted = openssl_decrypt($data, $cipher, $key, OPENSSL_RAW_DATA);

        if ($decrypted === false) {
            // Try legacy mcrypt decode if OpenSSL fails
            $result = $this->decode_legacy_mcrypt($data, $cipher, $key);
            // If legacy decrypt also fails, return false
            if ($result === false) {
                return false;
            }
            return $result;
        }

        // Decompress
        if ($_result = base64_decode($decrypted))
        {
            return gzuncompress($_result);
        }

        return gzuncompress($decrypted);
    }

    /**
     * Decode legacy mcrypt encrypted data (for backward compatibility)
     * This method will only work if mcrypt extension is still available
     * @param string $data Encrypted data
     * @param string $algorithm Mcrypt algorithm used
     * @param string $key Decryption key
     * @return string|false Decrypted data or false if mcrypt not available
     */
    private function decode_legacy_mcrypt($data, $algorithm, $key)
    {
        // If mcrypt is not available, cannot decode legacy data
        if (!function_exists('mcrypt_module_open')) {
            // Log the error but don't exit - let caller handle it
            if (defined('IN_DEBUG') && IN_DEBUG) {
                error_log('Cannot decrypt legacy mcrypt data. Mcrypt extension not available. Old encrypted data cannot be decrypted.');
            }
            return false;
        }

        try {
            $mcrypt = mcrypt_module_open($algorithm, '', MCRYPT_MODE_ECB, '');
            $key = substr($key, 0, mcrypt_enc_get_key_size($mcrypt));

            mcrypt_generic_init($mcrypt, $key, mcrypt_create_iv(mcrypt_enc_get_iv_size($mcrypt), MCRYPT_RAND));

            $result = trim(mdecrypt_generic($mcrypt, $data));

            mcrypt_generic_deinit($mcrypt);
            mcrypt_module_close($mcrypt);

            if ($_result = base64_decode($result))
            {
                return gzuncompress($_result);
            }

            return gzuncompress($result);
        } catch (Exception $e) {
            if (defined('IN_DEBUG') && IN_DEBUG) {
                error_log('Legacy mcrypt decryption failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Get encryption key
     * @param string|null $key Custom key or null to use default
     * @return string Encryption key (padded or truncated to cipher key size)
     */
    private function get_key($key = null)
    {
        if (!$key)
        {
            $key = G_COOKIE_HASH_KEY;
        }

        // Get the key size for current cipher
        $keySize = $this->get_key_size();

        // Pad or truncate key to required size
        if (strlen($key) > $keySize) {
            return substr($key, 0, $keySize);
        } elseif (strlen($key) < $keySize) {
            return str_pad($key, $keySize, "\0");
        }

        return $key;
    }

    /**
     * Get cipher to use
     * @return string OpenSSL cipher method
     */
    private function get_cipher()
    {
        // Try to use AES-256-ECB, fallback to AES-128-ECB
        $ciphers = openssl_get_cipher_methods();

        if (in_array('AES-256-ECB', $ciphers)) {
            return 'AES-256-ECB';
        } elseif (in_array('aes-256-ecb', $ciphers)) {
            return 'aes-256-ecb';
        } elseif (in_array('AES-128-ECB', $ciphers)) {
            return 'AES-128-ECB';
        } elseif (in_array('aes-128-ecb', $ciphers)) {
            return 'aes-128-ecb';
        }

        // Default
        return 'AES-256-ECB';
    }

    /**
     * Get key size for current cipher
     * @return int Key size in bytes
     */
    private function get_key_size()
    {
        $cipher = $this->get_cipher();

        // AES-256 uses 32 bytes key, AES-128 uses 16 bytes key
        if (stripos($cipher, '256') !== false) {
            return 32;
        } elseif (stripos($cipher, '128') !== false) {
            return 16;
        }

        return 32; // Default to 256-bit
    }

    /**
     * Convert string to hexadecimal
     * @param string $string Input string
     * @return string Hexadecimal representation
     */
    private function str_to_hex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++)
        {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }

        return strtoupper($hex);
    }

    /**
     * Convert hexadecimal to string
     * @param string $hex Hexadecimal string
     * @return string Decoded string
     */
    private function hex_to_str($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex)-1; $i += 2)
        {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }
}
