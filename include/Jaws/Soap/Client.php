<?php
/**
 * Class that deals like a wrapper between Jaws and PHP/SoapClient
 *
 * @category    Application
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2019 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Soap_Client extends Jaws_Soap
{
    /**
     * @access  private
     * @var int $expires    Cache expires time(second)
     */
    private $expires = 0;

    /**
     * @access  private
     * @var int $refresh    Refresh/Update cache
     */
    private $refresh = false;

    /**
     * @access  private
     * @var int $request_cache_key  Cache key
     */
    private $request_cache_key = 0;

    /**
     * Constructor
     *
     * @access  public
     * @return  void
     */
    function __construct($wsdl, $options = array())
    {
        if (!extension_loaded('soap')) {
            return Jaws_Error::raiseError('SOAP extension is not available.', __CLASS__);
        }

        // call parent constructor
        parent::__construct();

        try {
            $this->SoapClient = new SoapClient($wsdl, $options);
        } catch (Exception $error) {
            return Jaws_Error::raiseError($error->getMessage(), __CLASS__);
        }
    }

    /**
     * Overloading __call magic method
     *
     * @access  private
     * @param   string  $method     Method name
     * @param   string  $arguments  Method parameters
     * @return  mixed   Requested object otherwise Jaws_Error
     */
    function __call($method, $arguments)
    {
        // request cache key
        $this->request_cache_key = Jaws_Cache::key($method, $arguments);

        try {
            if ($this->refresh ||
                false === $result = @unserialize($this->app->cache->get($this->request_cache_key))
            ) {
                $result = call_user_func_array(array($this->SoapClient, $method), $arguments);
                if (is_soap_fault($result)) {
                    throw new Exception($result->faultstring, $result->faultcode);
                }

                // set cache
                $this->app->cache->set(
                    $this->request_cache_key,
                    serialize($result),
                    $this->expires
                );
            }

            return $result;
        } catch (Exception $error) {
            return Jaws_Error::raiseError(
                $error->getMessage(),
                __FUNCTION__,
                JAWS_ERROR_ERROR,
                1
            );
        }
    }

    /**
     * Set cache options
     *
     * @access  private
     * @param   int     $expires    Cache expires time(second)
     * @param   bool    $refresh    Refresh/Update cache
     * @return  void
     */
    function setCacheOptions($expires = 0, $refresh = false)
    {
        $this->expires = $expires;
        $this->refresh = $refresh;
    }

    /**
     * Delete cache
     *
     * @access  public
     * @return  mixed
     */
    function deleteCache()
    {
        return $this->app->cache->delete($this->request_cache_key);
    }

}