<?php
/**
 * JSON Web Token
 *
 * @category    Application
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @BasedOn     https://github.com/adhocore/php-jwt
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
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
        'RS256' => OPENSSL_ALGO_SHA256,
        'RS384' => OPENSSL_ALGO_SHA384,
        'RS512' => OPENSSL_ALGO_SHA512,
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

        if (substr($algo, 0, 2) === 'RS') {
            $this->key = openssl_pkey_get_private($key, $passphrase?: '');
            if ($this->key === false) {
                return Jaws_Error::raiseError(openssl_error_string(), __FUNCTION__);
            }
        } else {
            $this->key = $key;
        }

        $this->algo   = $algo;
        $this->maxAge = $maxAge;
        $this->leeway = $leeway;
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
                openssl_sign($input, $signature, $this->key, $this->algos[$this->algo]);
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
                $pkeyDetails = openssl_pkey_get_details($this->key);
                $result = openssl_verify($input, $signature, $pkeyDetails['key'], $this->algos[$this->algo]) === 1;
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