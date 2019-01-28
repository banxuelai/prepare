<?php
/**
 * AES 加解密类
 *
 */
class CI_Aes
{
    //密钥
//    private $_secrect_key = ''; # {546Q2E435721-8V6A2E546UI2}
    private $hex_iv = '00000000000000000000000000000000';//'1RQ2OH3E3QW029RQ7TQ2OH3E6QW8O9IQ'; # converted JAVA byte code in to HEX and placed it here
    //private $key = '';//'2PQ2OH3E4QW029RE'; # Same as in JAVA
    private $base64_iv = "4H/W+z0MX8RMYbuUTZhshA==";

    function __construct()
    {
        //$this->key = hash('sha256', $this->key, true);
        //echo $this->key.'<br/>';
    }

    /**
     * 加密方法
     * @param string $str
     * @return string
     */
    public function encrypt($str, $key)
    {
        //AES, 128 ECB模式加密数据
//        $screct_key = $this->_secrect_key;
//        $screct_key = base64_encode($screct_key);
//        $str = trim($str);
//        $str = $this->addPKCS7Padding($str);
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
//        $encrypt_str =  mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
//        return base64_encode($encrypt_str);

        $key = hash('sha256', $key, true);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $key, $this->hexToStr($this->hex_iv));
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $pad = $block - (strlen($str) % $block);
        $str .= str_repeat(chr($pad), $pad);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($encrypted);
    }

    function hexToStr($hex)
    {
        $string='';
        for ($i=0; $i < strlen($hex)-1; $i+=2)
        {
            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }
        return $string;
    }
      
    /**
     * 解密方法
     * @param string $str
     * @return string
     */
//    public function decrypt1($str)
//    {
//        //AES, 128 ECB模式加密数据
//        $screct_key = $this->_secrect_key;
//        $str = base64_decode($str);
//        $screct_key = base64_encode($screct_key);
//        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128,MCRYPT_MODE_ECB),MCRYPT_RAND);
//        $encrypt_str =  mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $screct_key, $str, MCRYPT_MODE_ECB, $iv);
//        $encrypt_str = trim($encrypt_str);
//        $encrypt_str = $this->stripPKSC7Padding($encrypt_str);
//        return $encrypt_str;
//    }

    public function decrypt($str, $key)
    {
        $key = hash('sha256', $key, true);
        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $key, $this->hexToStr($this->hex_iv));
        $encryptedData = base64_decode($str);
        $encryptedData = mdecrypt_generic($td, $encryptedData);
        $encryptedData = $this->stripPKSC7Padding($encryptedData);
        return $encryptedData;
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    function addPKCS7Padding($source){
        $source = trim($source);
        $block = mcrypt_get_block_size('rijndael-128', 'ecb');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * 移去填充算法
     * @param string $source
     * @return string
     */
    function stripPKSC7Padding($string)
    {
//        $source = trim($source);
//        $char = substr($source, -1);
//        $num = ord($char);
//        if($num==62)return $source;
//        $source = substr($source,0,-$num);
//        return $source;

        $slast = ord(substr($string, -1));
        return substr($string, 0, strlen($string) - $slast);
        # no need.
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);
            return $string;
        } else {
            return false;
        }
    }

    /*
     * openssl_encrypt
     */
    public function opensslEncrypt($str, $key)
    {
        $iv = base64_decode($this->base64_iv);
        if(empty($key)) {
            $key = $this->key;
        }
        $str = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return  base64_encode($str);
    }

    /*
     * openssl_decrypt
     */
    public function opensslDecrypt($str, $key)
    {
        $iv = base64_decode($this->base64_iv);

        $str = base64_decode($str);

        if(empty($key)) {
            $key = $this->key;
        }

        $str = openssl_decrypt($str, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return $str;
    }

}


