<?php
/**
 * Payment ParsPal driver
 *
 * @category    Payment
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2014-2017 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Payment
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title;

    /**
     * Driver configuration options
     *
     * @access  protected
     * @var     array
     */
    protected $options;

    /**
     * Callback url
     *
     * @access  protected
     * @var     string
     */
    protected $callback;


    /**
     * An interface for available drivers
     *
     * @access  public
     * @param   string  $payDriver  Payment driver name
     * @param   array   $options    Associated options array
     * @param   string  $callback   Callback URL
     * @return  object  Jaws_Payment type object or Jaws_Error on failure
     */
    static function factory($payDriver, $options, $callback = '')
    {
        $payDriver = preg_replace('/[^[:alnum:]_-]/', '', $payDriver);
        $payDriverFile = JAWS_PATH . "include/Jaws/Payment/$payDriver.php";
        if (!file_exists($payDriverFile)) {
            return Jaws_Error::raiseError("[$payDriver]: Loading payment driver failed.", __CLASS__);
        }

        include_once $payDriverFile;
        $className = 'Jaws_Payment_' . $payDriver;
        $obj = new $className($options, $callback);
        return $obj;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sends payment request to gateway
     *
     * @access  public
     * @param   int     $invoice    Invoice ID
     * @param   int     $price      Invoice price(Iran Rial)
     * @param   string  $title      Invoice title
     * @return  mixed   Jaws_Error on failure
     */
    function paymentRequest($invoice, $price, $title)
    {
        return Jaws_Error::raiseError('paymentRequest() method not supported by this driver.', __CLASS__);
    }


    /**
     * Verifies payment request
     *
     * @access  public
     * @param   int     $invoice        Invoice ID
     * @param   int     $price          Invoice price(Iran Rial)
     * @param   array   $feedbackData   Feedback data
     * @return  mixed   Transaction identifier or Jaws_Error on failure  
     */
    function paymentVerify($invoice, $price, $feedbackData)
    {
        return Jaws_Error::raiseError('paymentVerify() method not supported by this driver.', __CLASS__);
    }

}