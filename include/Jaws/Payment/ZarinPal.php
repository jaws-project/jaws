<?php
/**
 * Payment ZarinPal driver
 *
 * @category    Payment
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Payment_ZarinPal extends Jaws_Payment
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'ZarinPal';

    /**
     * Store soap object instance
     * @var     array
     * @access  private
     */
    private $soapClient;

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
        if (!extension_loaded('soap')) {
            return Jaws_Error::raiseError('SOAP extension is not available.', __CLASS__);
        }

        if (!extension_loaded('openssl')) {
            return Jaws_Error::raiseError('OpenSSL extension is not available.', __CLASS__);
        }

        $this->options  = $options;
        $this->callback = $callback;
        $this->soapClient = new SoapClient(
//            'https://de.zarinpal.com/pg/services/WebGate/wsdl',
            ROOT_JAWS_PATH . 'include/Jaws/Payment/ZarinPal.wsdl',
            array('encoding'=>'UTF-8', 'exceptions' => 0)
        );
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
                'MerchantID' => '',
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
        $params = $this->options + array(
            'CallbackURL' => $this->callback,
            'Description' => $title,
            'Amount'      => $price,
            'Email'       => '',
            'Mobile'      => '',
        );

        $result = $this->soapClient->__soapCall('PaymentRequest', array($params));
        if (is_soap_fault($result)) {
            return Jaws_Error::raiseError($result->faultstring, $result->faultcode);
        }

        // check result status
        if (100 != $status = $result->Status) {
            return Jaws_Error::raiseError("Payment request failed($status)", __CLASS__);
        }

        Jaws_Header::Location('https://www.zarinpal.com/pg/StartPay/'. $result->Authority);
    }

    /**
     * Verifies payment request
     *
     * @access  public
     * @param   int     $invoice        Invoice ID
     * @param   int     $price          Invoice price(Iran Rial)
     * @param   string  $token          Transaction token
     * @param   string  $arguments      JSON-ed transaction arguments
     * @param   array   $feedbackData   Feedback/Callback data
     * @return  mixed   Transaction identifier or Jaws_Error on failure  
     */
    function paymentVerify($invoice, $price, $token, $arguments, $feedbackData = null)
    {
        if(!empty($feedbackData) && $feedbackData['Status'] != 'OK') {
            return Jaws_Error::raiseError("Payment request feedback failed({$feedbackData['status']})", 402);
        }

        $price = ceil($price/10); // convert Rial to Tooman
        $params = $this->options + array(
            'Authority' => $token,
            'Amount'    => $price,
        );

        $result = $this->soapClient->__soapCall('PaymentVerification', array($params));
        if (is_soap_fault($result)) {
            return Jaws_Error::raiseError($result->faultstring, $result->faultcode);
        }

        // check result status
        if (100 != $status = $result->Status) {
            return Jaws_Error::raiseError("Payment verification failed($status)", 402);
        }

        return $result->RefID;
    }

}