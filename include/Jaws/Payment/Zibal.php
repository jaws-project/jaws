<?php
/**
 * Payment Zibal driver
 *
 * @category    Payment
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Payment_Zibal extends Jaws_Payment
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'Zibal';

    /**
     * Store Jaws HTTP Request object instance
     * @var     array
     * @access  private
     */
    private $httpRequest;

    /**
     * Constructor
     *
     * @access  protected
     * @param   array   $options    Associated options array
     * @param   string  $callback   Callback URL
     * @return  mixed   
     */
    protected function __construct($options, $callback = '')
    {
        $this->options  = $options;
        $this->callback = $callback;
        $this->httpRequest = new Jaws_HTTPRequest();
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
                'merchant' => '',
                'callback' => '',
            ),
            $this->options
        );
    }

    /**
     * Sends payment request to gateway
     *
     * @access  public
     * @param   int     $invoice    Invoice ID
     * @param   int     $price      Invoice price(Iran Toman)
     * @param   string  $title      Invoice title
     * @param   string  $arguments  JSON-ed transaction arguments
     * @return  mixed   Jaws_Error on failure
     */
    function paymentRequest($invoice, $price, $title, $arguments)
    {
        $params = array(
            'merchant'    => $this->options['merchant'],
            'callbackUrl' => $this->callback,
            'description' => $title,
            'amount'      => $price * 10, // Toman to Rial 
            'orderId'     => $invoice,
        );

        /*
        $arguments = json_decode($arguments, true);
        if (array_key_exists('allowedCards', $arguments) && !empty($arguments['allowedCards'])) {
            $params['allowedCards'] = $arguments['allowedCards'];
        }
        */

        $this->httpRequest->setHeader('Content-Type', 'application/json');
        $result = $this->httpRequest->rawPostData('https://gateway.zibal.ir/v1/request', json_encode($params));
        //response
        $response = json_decode($result['body'], true);
        if ($response['result'] == 100) {
            // save trackId for later use
            $result = Jaws_Gadget::getInstance('Payment')
                ->action
                ->load('Payment')
                ->callbackRequest($invoice, $response['trackId']);
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
            // redirect to gateway
            Jaws_Header::Location('https://gateway.zibal.ir/start/'. $response['trackId']);
        } else {
            return Jaws_Error::raiseError($response['message'], $response['result']);
        }
    }

    /**
     * Verifies payment request
     *
     * @access  public
     * @param   int     $invoice        Invoice ID
     * @param   int     $price          Invoice price(Iran Toman)
     * @param   string  $token          Transaction token
     * @param   string  $arguments      JSON-ed transaction arguments
     * @param   array   $feedbackData   Feedback/Callback data
     * @return  mixed   Transaction identifier or Jaws_Error on failure  
     */
    function paymentVerify($invoice, $price, $token, $arguments, $feedbackData = null)
    {
        if(!empty($feedbackData) && $feedbackData['success'] != 1) {
            return Jaws_Error::raiseError("Payment failed", 402);
        }
        $arguments = json_decode($arguments, true);

        $params = array(
            'trackId'  => $token,
            'merchant' => $this->options['merchant'],
        );

        $this->httpRequest->setHeader('Content-Type', 'application/json');
        $result = $this->httpRequest->rawPostData('https://gateway.zibal.ir/v1/verify', json_encode($params));
        if ($result['status'] != 200) {
            return Jaws_Error::raiseError('Request error!', 500);
        }
        $response = json_decode($result['body'], true);

        if ($response['result'] == 100) {
            // check cardNumber with allowed bank cards if available 
            if (array_key_exists('allowedCards', $arguments)) {
                $matched = false;
                foreach ($arguments['allowedCards'] as $cardNumber) {
                    if (substr_replace($cardNumber, '******', 6, 6) == $response['cardNumber']) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    // payment is OK but need card checking
                    return array(
                        'code' => $response['refNumber'],
                        'card_number' => $response['cardNumber'],
                        'status' => Payment_Info::STATUS_CHECKING
                    );
                }
            }

            // approved
            return array(
                'code' => $response['refNumber'],
                'card_number' => $response['cardNumber'],
                'status' => Payment_Info::STATUS_APPROVED
            );
        }

        // previously verifed
        if ($response['result'] == 201) {
            return Jaws_Error::raiseError('previously verifed!', 500, JAWS_ERROR_WARNING);
        }

        /*
        returned result not compatible with documents
        if ($response['result'] == 202 && in_array($response['status'], [-1, -2])) {
            // -1: still payment in gateway in progress
            // -2: gateway internal error
            // https://help.zibal.ir/IPG/API/#status-codes
            return Jaws_Error::raiseError('Request error!', 500);
        }
        */

        return Jaws_Error::raiseError('Payment verification failed', $response['result']);
    }

}