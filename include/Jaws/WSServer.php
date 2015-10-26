<?php
/**
 * Class to manage the webservice - server side
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_WSServer
{
    /**
     * Return true or false if the webservice($GLOBALS['server'] flag) is running
     *
     * @return  bool     True if server is running, false if not.
     * @access  public
     */
    function IsActive()
    {
        return $GLOBALS['server'];
    }

    /**
     * Initializes the webservice server
     *
     * @param   string  $wsdlclass  Name of the WSDL class
     * @param   string  $namespace  Namespace of the class.
     * @return  object  The soap server object
     * @access  public
     */
    function Init($wsdlclass = 'JawsWS', $namespace = 'urn:jawsws')
    {
        if (!Jaws_WSServer::IsActive()) {
            $GLOBALS['server'] = new soap_server();
            $GLOBALS['server']->configureWSDL($wsdlclass, $namespace);
            $GLOBALS['server']->wsdl->schemaTargetNamespace = $namespace;
        }

        return $GLOBALS['server'];
    }

    /**
     * Prints the WSDL stuff
     *
     * @access  public
     */
    function Dump()
    {
        if (Jaws_WSServer::IsActive()) {
            if ($GLOBALS['server']->wsdl->serviceName == 'admin') {
                return false;
            }

            $GLOBALS['server']->service($GLOBALS['HTTP_RAW_POST_DATA']);
        }

        return false;
    }

}