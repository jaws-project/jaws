<?php
/**
 * Firebase cloud messaging HTTP v1 notification class
 *
 * @category    Notification
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2024-2025 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Notification_FCMHTTPv1 extends Jaws_Notification
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'Firebase cloud messaging';

    /**
     * Driver type
     *
     * @access  protected
     * @var     int
     */
    protected $type = Jaws_Notification::APP_DRIVER;

    /**
     * Options
     * @var     array
     * @access  protected
     */
    protected $options = array(
        'project_id' => '',
        'service_account_json' => '',
    );

    /**
     * Store access token
     * @var     string
     * @access  private
     */
    private $accessToken;

    /**
     * constructor
     *
     * @access  protected
     * @param   array $options Associated options array
     */
    protected function __construct(array $options = array())
    {
        parent::__construct();
        $this->options = array_merge($this->options, $options);

        $this->accessToken = $this->getAccessToken();
    }

    /**
     * Generate a JWT for authenticating with Google APIs.
     *
     * @return string|null JWT token
     */
    function generateJWT($json)
    {
        $serviceAccount = json_decode($json, true);
        if (!$serviceAccount) {
            return false;
        }

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT',
        ];

        $now = time();
        $payload = [
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => $serviceAccount['token_uri'],
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $base64UrlHeader = base64_encode(json_encode($header));
        $base64UrlPayload = base64_encode(json_encode($payload));

        $signature = '';
        if (false === @openssl_sign(
                $base64UrlHeader . '.' . $base64UrlPayload,
                $signature,
                $serviceAccount['private_key'],
                OPENSSL_ALGO_SHA256
            )
        ) {
            return false;
        }

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . base64_encode($signature);
    }

    /**
     * Get an OAuth 2.0 access token using the generated JWT.
     *
     * @return string|null Access token
     */
    function getAccessToken()
    {
        $jwt = $this->generateJWT($this->options['service_account_json']);
        if (empty($jwt)) {
            return false;
        }

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $postFields = http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);

        if ($response === false) {
            return false;
            //die('Error fetching access token: ' . curl_error($ch));
        }

        $responseData = json_decode($response, true);
        curl_close($ch);

        return $responseData['access_token'] ?? null;
    }

    /**
     * Get options list
     *
     * @access  public
     * @return  mixed   Jaws_Error on failure
     */
    function getDriverOptions()
    {
        return array_merge(
            array(
                'project_id' => '',
                'service_account_json' => '',
            ),
            $this->options
        );
    }

    /**
     * Sends notify to user
     *
     * @access  public
     * @param   string  $shouter        Shouter(gadget) name
     * @param   string  $name           Notification type name
     * @param   array   $contacts       Contacts array
     * @param   string  $title          Title
     * @param   string  $summary        Summary
     * @param   string  $verbose        Verbose
     * @param   array   $variables      Variables
     * @param   integer $time           Time of notify(timestamps)
     * @param   string  $callback_url   Notification callback URL
     * @param   string  $image          Notification image
     * @return  mixed   Jaws_Error on failure
     */
    function notify(
        $shouter, $name, $contacts, $title, $summary, $verbose, $variables, $time, $callback_url, $image
    ) {
        try {
            if (empty($this->accessToken)) {
                throw new Exception('Failed to obtain access token.', 500);
            }

            foreach ($contacts as $deviceToken) {
                $headers = [
                    'Authorization: Bearer ' . $this->accessToken,
                    'Content-Type: application/json',
                ];

                $payload = [
                    'message' => [
                        'token' => $deviceToken,
                        'notification' => [
                            'title' => $title,
                            'body' => $summary,
                        ],
                        'data' => null,
                    ],
                ];

                $ch = curl_init();
                curl_setopt(
                    $ch,
                    CURLOPT_URL,
                    "https://fcm.googleapis.com/v1/projects/{$this->options['project_id']}/messages:send"
                );
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $response = curl_exec($ch);
                if ($response === false) {
                    throw new Exception(curl_error($ch), 500);
                } else {
                    $GLOBALS['log']->Log(JAWS_DEBUG, 'Response from FCM: ' . $response);
                }
                curl_close($ch);
            }

        } catch (Exception $error) {
            return Jaws_Error::raiseError(
                "FCM send failed({$error->getMessage()})",
                __CLASS__
            );
            return $error;
        }

        return true;
    }

}