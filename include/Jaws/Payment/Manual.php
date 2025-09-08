<?php
/**
 * Payment Parsian driver
 *
 * @category    Payment
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Payment_Manual extends Jaws_Payment
{
    /**
     * Driver title
     *
     * @access  protected
     * @var     string
     */
    protected $title = 'Manual';

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
    }

    /**
     * Get options list
     *
     * @access  public
     * @return  mixed   Jaws_Error on failure
     */
    function getDriverOptions()
    {
        return array();
    }

    /**
     * Sends payment request to gateway
     *
     * @access  public
     * @param   int     $invoice    Invoice ID
     * @param   int     $price      Invoice price
     * @param   string  $title      Invoice title
     * @param   string  $arguments  JSON-ed transaction arguments
     * @return  mixed   Jaws_Error on failure
     */
    function paymentRequest($invoice, $price, $title, $arguments)
    {
    }


    /**
     * Verifies payment request
     *
     * @access  public
     * @param   int     $invoice        Invoice ID
     * @param   int     $price          Invoice price
     * @param   string  $token          Transaction token
     * @param   string  $arguments      JSON-ed transaction arguments
     * @param   array   $feedbackData   Feedback/Callback data
     * @return  mixed   Transaction identifier or Jaws_Error on failure  
     */
    function paymentVerify($invoice, $price, $token, $arguments, $feedbackData = null)
    {
    }

}