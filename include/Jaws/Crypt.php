<?php
/**
 * Class that deals like a wrapper between Jaws and pear/Crypt
 *
 * @category   Crypt
 * @package    Core
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Crypt
{
    /**
     * RSA private key
     *
     * @access  private
     * @var     resource
     */
    private $pvt_key = null;

    /**
     * RSA public key
     *
     * @access  private
     * @var     resource
     */
    private $pub_key = '';

    /**
     * Constructor
     *
     * @access  private
     * @param   array   $pkey   RSA key pair array
     * @return  void
     */
    private function __construct($pkey = array())
    {
        $this->pvt_key = openssl_pkey_get_private($pkey['pvt_key']);
        $this->pub_key = openssl_pkey_get_public($pkey['pub_key']);
    }


    /**
     * Creates the Jaws_Crypt instance if it doesn't exist else it returns the already created one
     *
     * @access  public
     * @param   array   $pkey   RSA key pair array
     * @return  mixed   Jaws_Error on failure otherwise instance of Jaws_Crypt
     */
    static function getInstance($pkey = array())
    {
        if (!extension_loaded('openssl')) {
            return Jaws_Error::raiseError(
                'openssl extension is not available.',
                __FUNCTION__,
                JAWS_ERROR_INFO
            );
        }

        static $objInstance = array();
        $instance = md5(serialize($pkey));
        if (!isset($objInstance[$instance])) {
            if (empty($pkey)) {
                $pkey = self::internalKey();
                if (Jaws_Error::IsError($pkey)) {
                    return $pkey;
                }
            }

            $objInstance[$instance] = new Jaws_Crypt($pkey);
        }

        return $objInstance[$instance];
    }


    /**
     * Get internal RSA key pair
     *
     * @access  private
     * @return  mixed   Jaws_Error on failure otherwise RSA key detail array
     */
    private static function internalKey() {
        // fetch all registry keys related to crypt
        $cryptPolicies = $GLOBALS['app']->Registry->fetchAll('Policy', false);
        if ($cryptPolicies['crypt_enabled'] != 'true') {
            return Jaws_Error::raiseError(
                'RSA encryption is not enabled.',
                __FUNCTION__,
                JAWS_ERROR_INFO
            );
        }

        if (time() > ($cryptPolicies['crypt_key_start_date'] + $cryptPolicies['crypt_key_age'])) {
            // generate new key pair
            $result = self::Generate_RSA_KeyPair($cryptPolicies['crypt_key_len']);
            if (Jaws_Error::isError($result)) {
                return $result;
            }

            $GLOBALS['app']->Registry->update('crypt_pvt_key', $result['pvt_key'], false, 'Policy');
            $GLOBALS['app']->Registry->update('crypt_pub_key', $result['pub_key'], false, 'Policy');
            $GLOBALS['app']->Registry->update('crypt_key_start_date', time(), false, 'Policy');
            $cryptPolicies['crypt_pvt_key'] = $result['pvt_key'];
            $cryptPolicies['crypt_pub_key'] = $result['pub_key'];
        }

        return array(
            'key_len' => $cryptPolicies['crypt_key_len'],
            'pvt_key' => $cryptPolicies['crypt_pvt_key'],
            'pub_key' => $cryptPolicies['crypt_pub_key'],
        );
    }


    /**
     * Generate new RSA key pair
     *
     * @access  public
     * @param   int     $key_len    RSA key length
     * @return  mixed   Jaws_Error on failure otherwise RSA key detail array
     */
    static function Generate_RSA_KeyPair($key_len)
    {
        $config = array(
            'digest_alg'  => 'sha512',
            'encrypt_key' => false,
            'private_key_bits' => (int)$key_len,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        );
        if (false === $pkey = openssl_pkey_new($config)) {
            return Jaws_Error::raiseError(openssl_error_string(),  __FUNCTION__);
        }

        if (false === openssl_pkey_export($pkey, $pvt_key)) {
            return Jaws_Error::raiseError(openssl_error_string(),  __FUNCTION__);
        }

        if (false === $pkey_details = openssl_pkey_get_details($pkey)) {
            return Jaws_Error::raiseError(openssl_error_string(),  __FUNCTION__);
        }
        
        return array(
            'key_len' => $key_len,
            'pvt_key' => $pvt_key,
            'pub_key' => $pkey_details['key'],
        );
    }


    /**
     * Get Public key in PAM format
     *
     * @access  public
     * @return  mixed   RSA public key or False on failure
     */
    function getPublic()
    {
        if (false !== $pub_details = openssl_pkey_get_details($this->pub_key)) {
            return $pub_details['key'];
        }

        return false;
    }


    /**
     * Get RSA key length
     *
     * @access  public
     * @param   mixed   $key    RSA public|private key
     * @return  mixed   RSA key length or False on failure
     */
    function length($key = null)
    {
        if (false !== $key_details = openssl_pkey_get_details(empty($key)? $this->pub_key : $key)) {
            return $key_details['bits'];
        }

        return false;
    }


    /**
     * Get RSA key modulus
     *
     * @access  public
     * @param   mixed   $key    RSA public|private key
     * @return  mixed   RSA key modulus or False on failure
     */
    function modulus($key = null)
    {
        if (false !== $key_details = openssl_pkey_get_details(empty($key)? $this->pub_key : $key)) {
            return bin2hex($key_details['rsa']['n']);
        }

        return false;
    }


    /**
     * Get RSA key exponent
     *
     * @access  public
     * @param   mixed   $key    RSA public|private key
     * @return  mixed   RSA key exponent or False on failure
     */
    function exponent($key = null)
    {
        if (false !== $key_details = openssl_pkey_get_details(empty($key)? $this->pub_key : $key)) {
            return bin2hex($key_details['rsa']['e']);
        }

        return false;
    }

    /**
     * Encrypt text by RSA algorithm
     *
     * @access  public
     * @param   string  $text   Plain text
     * @return  string  Encrypted text
     */
    function encrypt($text)
    {
        $result = '';
        $n = $this->length($this->pub_key)/8;
        $text = str_split(rawurlencode($text), n-11);
        foreach ($text as $chunk) {
            openssl_public_encrypt($chunk, $ctext, $this->pub_key, OPENSSL_PKCS1_PADDING);
            $result .= $ctext;
        }

        return base64_encode($result);
    }


    /**
     * Decrypt text by RSA algorithm
     *
     * @access  public
     * @param   string  $ctext  Encrypted text
     * @return  string  Plain text
     */
    function decrypt($ctext)
    {
        $result = '';
        $ctext = base64_decode($ctext);
        $n = $this->length($this->pvt_key)/8;
        $ctext = str_split($ctext, $n*2);
        foreach ($ctext as $chunk) {
            openssl_private_decrypt($chunk, $text, $this->pvt_key, OPENSSL_PKCS1_PADDING);
            $result .= $text;
        }

        return rawurldecode($result);
    }

}