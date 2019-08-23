<?php
/**
 * JavaScript Web Token
 *
 * @category    Application
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_JWT
{
    /**
     * Supported Signing algorithms
     *
     * @access  private
     * @var     array
     */
    private $algos = array(
        // HMAC digest algorithms
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
        // RSA digest algorithms
        'RS256' => OPENSSL_ALGO_SHA256,
        'RS384' => OPENSSL_ALGO_SHA384,
        'RS512' => OPENSSL_ALGO_SHA512,
        // ECDSA digest algorithms
        'ES224' => OPENSSL_ALGO_SHA224,
        'ES256' => OPENSSL_ALGO_SHA256,
        'ES384' => OPENSSL_ALGO_SHA384,
        'ES512' => OPENSSL_ALGO_SHA512,
    );

    /**
     * Supported claim time identifies
     *
     * @access  private
     * @var     array
     */
    private $hkeyTimes = array('exp', 'iat', 'nbf');

    /**
     * JWT signing algorithm
     *
     * @access  private
     * @var     string
     */
    private $algo = 'HS256';

    /**
     * JWT TTL in seconds
     *
     * @access  private
     * @var     int
     */
    private $maxAge = 3600;

    /**
     * Grace period in seconds to allow for clock skew
     *
     * @access  private
     * @var     int
     */
    private $leeway = 0;

    /**
     * Constructor
     *
     * @param   string  $key    The signature secret key
     * @param   string  $algo   The algorithm to sign/verify the token
     * @param   int     $maxAge The TTL of token to be used to determine expiry if `iat` claim is present
     * @param   int     $leeway Leeway for clock skew
     * @param   string  $pass   The passphrase (only for RSA algorithms)
     * @return  void
     */
    function __construct($key, $algo = 'HS256', $maxAge = 3600, $leeway = 0, $passphrase = null)
    {
        if (!array_key_exists($algo, $this->algos)) {
            return Jaws_Error::raiseError('Unsupported algo '. $algo, __FUNCTION__);
        }

        if (in_array(substr($algo, 0, 2), array('RS', 'ES'))) {
            $this->key = array();
            if (array_key_exists('private', $key)) {
                $this->key['private'] = openssl_pkey_get_private($key['private'], $passphrase?: '');
                if ($this->key['private'] === false) {
                    return Jaws_Error::raiseError(openssl_error_string(), __FUNCTION__);
                }

                // fetch pem format of public key from private key
                $key['public'] = openssl_pkey_get_details($this->key['private'])['key'];
            }

            if (array_key_exists('public', $key)) {
                $this->key['public'] = openssl_pkey_get_public($key['public']);
                if ($this->key['public'] === false) {
                    return Jaws_Error::raiseError(openssl_error_string(), __FUNCTION__);
                }
            } else {
                return Jaws_Error::raiseError('Private|Public key not found', __FUNCTION__);
            }

        } else {
            $this->key = $key;
        }

        $this->algo   = $algo;
        $this->maxAge = $maxAge;
        $this->leeway = $leeway;
    }

    /**
     * DER to ECDSA
     *
     * @param   string  $signature  DER formated signature
     * @param   int     $algo       Digest algorithm
     *
     * @return  string  ECDSA formated signature
     */
    public static function toECDSA(string $signature, int $algo): string
    {
        /**
         *
         */
        function retrievePositiveInteger(string $data): string
        {
            while (ord($data[0]) == 0 && ord($data[1]) > 127) {
                $data = substr($data, 1);
            }

            return $data;
        } // end function


        // retrieve part length by digest algorithm
        switch ($algo) {
            case OPENSSL_ALGO_SHA224:
                $partLength = 28;
                break;

            case OPENSSL_ALGO_SHA256:
                $partLength = 32;
                break;

            case OPENSSL_ALGO_SHA384:
                $partLength = 48;
                break;

            case OPENSSL_ALGO_SHA512:
                $partLength = 66;
                break;

            default:
                return '';
        }

        try {
            if (ord($signature[0]) != 48) {
                throw new RuntimeException();
            }
            if (ord($signature[1]) == 129) {
                $signature = substr($signature, 3);
            } else {
                $signature = substr($signature, 2);
            }

            if (ord($signature[0]) != 2) {
                throw new RuntimeException();
            }

            $rlen = ord($signature[1]);
            $r = retrievePositiveInteger(substr($signature, 2, $rlen));
            $r = str_pad($r, $partLength, chr(0), STR_PAD_LEFT);

            $signature = substr($signature, 2 + $rlen);
            if (ord($signature[0]) != 2) {
                throw new RuntimeException();
            }
            $slen = ord($signature[1]);
            $s = retrievePositiveInteger(substr($signature, 2, $slen));
            $s = str_pad($s, $partLength, chr(0), STR_PAD_LEFT);

            return $r . $s;

        } catch (RuntimeException $error) {
            return '';
        }
    }

    /**
     * ECDSA to DER
     *
     * @param   string  $signature  ECDSA formated signature
     * @param   int     $algo       Digest algorithm
     *
     * @return  string  DER formated signature
     */
    public static function fromECDSA(string $signature, int $algo): string
    {
        /**
         *
         */
        function preparePositiveInteger(string $data): string
        {
            if (ord($data[0]) > 127) {
                return chr(0) . $data;
            }

            while (ord($data[0]) == 0 && ord($data[1]) <= 127) {
                $data = substr($data, 1);
            }

            return $data;
        } // end function


        // retrieve part length by digest algorithm
        switch ($algo) {
            case OPENSSL_ALGO_SHA224:
                $partLength = 28;
                break;

            case OPENSSL_ALGO_SHA256:
                $partLength = 32;
                break;

            case OPENSSL_ALGO_SHA384:
                $partLength = 48;
                break;

            case OPENSSL_ALGO_SHA512:
                $partLength = 66;
                break;

            default:
                return '';
        }

        try {
            if (strlen($signature) != 2 * $partLength) {
                throw new RuntimeException();
            }

            $r = substr($signature, 0, $partLength);
            $s = substr($signature, $partLength);

            $r = preparePositiveInteger($r);
            $rlen = strlen($r);
            $s = preparePositiveInteger($s);
            $slen = strlen($s);

            return
                chr(48). (($rlen + $slen + 4) > 128 ? chr(129) : '') . chr($rlen + $slen + 4).
                chr(2) . chr($rlen) . $r.
                chr(2) . chr($slen) . $s;

        } catch (RuntimeException $error) {
            return '';
        }
    }

    /**
     * Sign the input string
     *
     * @access  private
     * @param string $input
     * @return string   return the signature
     */
    private function sign($input)
    {
        switch ($this->algo) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                $signature = hash_hmac($this->algos[$this->algo], $input, $this->key, true);
                break;

            case 'RS256':
            case 'RS384':
            case 'RS512':
                openssl_sign($input, $signature, $this->key['private'], $this->algos[$this->algo]);
                break;

            case 'ES224':
            case 'ES256':
            case 'ES384':
            case 'ES512':
                openssl_sign($input, $signature, $this->key['private'], $this->algos[$this->algo]);
                $signature = self::toECDSA($signature, $this->algos[$this->algo]);
                break;

            default:
                $signature = '';
        }

        return $signature;
    }

    /**
     * Verify the signature of given input.
     *
     * @param string $input
     * @param string $signature
     *
     * @throws JWTException When key is invalid.
     *
     * @return bool
     */
    private function verify($input, $signature)
    {
        switch ($this->algo) {
            case 'HS256':
            case 'HS384':
            case 'HS512':
                $result = hash_hmac($this->algos[$this->algo], $input, $this->key, true) === $signature;
                break;

            case 'RS256':
            case 'RS384':
            case 'RS512':
                $result = openssl_verify($input, $signature, $this->key['public'], $this->algos[$this->algo]) === 1;
                break;

            case 'ES224':
            case 'ES256':
            case 'ES384':
            case 'ES512':
                $signature = self::fromECDSA($signature, $this->algos[$this->algo]);
                $result = openssl_verify($input, $signature, $this->key['public'], $this->algos[$this->algo]) === 1;
                break;

            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Base64 URL encode(also serialize the payload by json encoder if it is an array before base64 encoding)
     *
     * @access  private
     * @param   array|string    $data   Input data
     * @return  string
     */
    public static function base64URLEncode($data)
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        }

        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode(also unserialize the payload by json decoder if it is json encoded)
     *
     * @access  private
     * @param   array|string    $data   Input data
     * @return  string
     */
    public static function base64URLDecode($data)
    {
        $data = base64_decode(strtr($data, '-_', '+/'));
        $result = json_decode($data, true);
        return (json_last_error() == JSON_ERROR_NONE)? $result : $data;
    }

    /**
     * Encode payload as JWT token
     *
     * @access  public
     * @param   array   $payload    Payload
     * @param   array   $header     Extra header to append
     *
     * @return  string  JWT token
     */
    public function encode($payload, $header = array())
    {
        $header = array('typ' => 'JWT', 'alg' => $this->algo) + $header;

        $header    = self::base64URLEncode($header);
        $payload   = self::base64URLEncode($payload);
        $signature = self::base64URLEncode($this->sign($header . '.' . $payload));

        return $header . '.' . $payload . '.' . $signature;
    }

    /**
     * Decode JWT token and return original payload
     *
     * @access  public
     * @param   string  $token  JWT token
     *
     * @return  mixed   Payload, otherwise Jaws_Error
     */
    public function decode($token)
    {
        $token = explode('.', $token);
        if (count($token) != 3) {
            return Jaws_Error::raiseError('Invalid JWT token: Wrong number of segments', __FUNCTION__);
        }

        list($header64b, $payload64b, $signature64b) = $token;

        // verify header
        $header = self::base64URLDecode($header64b);
        if (empty($header['alg'])) {
            return Jaws_Error::raiseError('Invalid JWT token: Missing header typo of algorithm', __FUNCTION__);
        }
        if ($header['alg'] != $this->algo) {
            return Jaws_Error::raiseError('Invalid JWT token: Unsupported header algorithm', __FUNCTION__);
        }

        // verify signature
        $signature = self::base64URLDecode($signature64b);
        if (!$this->verify($header64b . '.' . $payload64b, $signature)) {
            return Jaws_Error::raiseError('Invalid JWT token: Signature failed', __FUNCTION__);
        }

        $timestamp = time();
        $payload = self::base64URLDecode($payload64b);
        foreach ($this->hkeyTimes as $key) {
            if (isset($payload[$key])) {
                switch ($key) {
                    case 'exp':
                        if ($timestamp >= ($payload[$key] + $this->leeway)) {
                            return Jaws_Error::raiseError('Invalid JWT token: Expired', __FUNCTION__);
                        }
                        break;

                    case 'iat':
                        if ($timestamp >= ($payload[$key] + $this->maxAge - $this->leeway)) {
                            return Jaws_Error::raiseError('Invalid JWT token: Expired', __FUNCTION__);
                        }
                        break;

                    case 'nbf':
                        if ($timestamp <= ($payload[$key] + $this->maxAge - $this->leeway)) {
                            return Jaws_Error::raiseError('Invalid JWT token: Not now', __FUNCTION__);
                        }
                        break;
                }
            }
        }

        return $payload;
    }

}