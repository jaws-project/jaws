<?php
/**
* @package JPSpan
* @subpackage Server
* @version $Id: PostOffice.php,v 1.14 2005/04/28 09:58:58 harryf Exp $
*/
//--------------------------------------------------------------------------------
/**
* Define
*/
if ( !defined('JPSPAN') ) {
    define ('JPSPAN',dirname(__FILE__).'/../');
}
/**
* Include
*/
require_once JPSPAN . 'Server.php';
//--------------------------------------------------------------------------------

/**
* Class and method name passed in the URL with params passed
* as url-encoded POST data. Urls like
* http://localhost/server.php/Class/Method
* @package JPSpan
* @subpackage Server
* @public
*/
class JPSpan_Server_PostOffice extends JPSpan_Server {

    /**
    * Name of user defined handler that was called
    * @param string
    * @access private
    */
    var $calledClass = NULL;
    
    /**
    * Name of method in handler
    * @param string
    * @access private
    */
    var $calledMethod = NULL;
    
    /**
    * @access public
    */
    function JPSpan_Server_PostOffice() {
        parent::JPSpan_Server();
    }
    
    /**
    * Resolve the call - identify the handler class and method and store
    * locally
    * @return boolean FALSE if failed (invalid request - see errors)
    * @access private
    */
    function resolveCall() {
        // Hack between server.php?class/method and server.php/class/method
        $uriPath = $_SERVER['QUERY_STRING'];

        if ( $uriPath ) {
            if ( preg_match('/\/$/',$uriPath) ) {
                $uriPath = substr($uriPath,0, strlen($uriPath)-1);
            }
        } else {
            $uriPath = JPSpan_Server::getUriPath();
        }

        $uriPath = explode('/',$uriPath);

        if ( !isset($_GET['object']) || !isset($_GET['method']) ) {
            trigger_error('Invalid call syntax',E_USER_ERROR);
            return FALSE;
        }

        if ( preg_match('/^[a-z]+[0-9a-z_]*$/', $_GET['object']) != 1 ) {
            trigger_error('Invalid handler name: '.$_GET['object'],E_USER_ERROR);
            return FALSE;
        }

        if ( preg_match('/^[a-z]+[0-9a-z_]*$/', $_GET['method']) != 1 ) {
            trigger_error('Invalid handler method: '.$_GET['method'],E_USER_ERROR);
            return FALSE;
        }

        if ( !array_key_exists($_GET['object'],$this->descriptions) ) {
            trigger_error('Unknown handler: '.$_GET['object'],E_USER_ERROR);
            return FALSE;
        }

        if ( !in_array($_GET['method'],$this->descriptions[$_GET['object']]->methods) ) {
            trigger_error('Unknown handler method: '.$_GET['method'],E_USER_ERROR);
            return FALSE;
        }

        $this->calledClass = $_GET['object'];
        $this->calledMethod = $_GET['method'];
        
        return TRUE;
        
    }

    /**
    * Get the Javascript client generator
    * @return JPSpan_Generator
    * @access public
    */
    function & getGenerator() {
        require_once JPSPAN . 'Generator.php';
        $G = new JPSpan_Generator();
        $G->init(
            new JPSpan_PostOffice_Generator(),
            $this->descriptions,
            $this->serverUrl
            );
        return $G;
    }
}

//--------------------------------------------------------------------------------
/**
* Generator for the JPSpan_Server_PostOffice
* @todo Much refactoring need to make code generation "pluggable"
* @see JPSpan_Server_PostOffice
* @package JPSpan
* @subpackage Server
* @access public
*/
class JPSpan_PostOffice_Generator {

    /**
    * @var array list of JPSpan_HandleDescription objects
    * @access public
    */
    var $descriptions;
    
    /**
    * @var string URL or server
    * @access public
    */
    var $serverUrl;
    
    /**
    * Invokes code generator
    * @param JPSpan_CodeWriter
    * @return void
    * @access public
    */
    function generate(& $Code) {

        $this->generateScriptHeader($Code);
        foreach ( array_keys($this->descriptions) as $key ) {
            $this->generateHandleClient($Code, $this->descriptions[$key]);
        }
    }
    
    /**
    * Generate the starting includes section of the script
    * @param JPSpan_CodeWriter
    * @return void
    * @access private
    */
    function generateScriptHeader(& $Code) {
        ob_start();
?>
/**@
* include 'remoteobject.js';
* include 'request/rawpost.js';
* include 'util/json.js';
* include 'encode/json.js';
*/
<?php
        $Code->append(ob_get_contents());
        ob_end_clean();
    }
    
    /**
    * Generate code for a single description (a single PHP class)
    * @param JPSpan_CodeWriter
    * @param JPSpan_HandleDescription
    * @return void
    * @access private
    */
    function generateHandleClient(& $Code, & $Description) {
        $url = $this->serverUrl;
        $url .= strpos($url, '?') ? '&' : '?';
        $url .= 'object='.$Description->Class;

        ob_start();
?>

function <?php echo $Description->Class; ?>() {
    
    var oParent = new JPSpan_RemoteObject();
    
    if ( arguments[0] ) {
        oParent.Async(arguments[0]);
    }
    
    oParent.__serverurl = '<?php 
        echo $url; ?>';
    
    oParent.__remoteClass = '<?php echo $Description->Class; ?>';
    oParent.__request = new JPSpan_Request_RawPost(new JPSpan_Encode_JSON());
    
<?php
foreach ( $Description->methods as $method ) {
?>
    
    // @access public
    oParent.<?php echo $method; ?> = function() {
        var url = this.__serverurl+'&method=<?php echo $method; ?>';
        return this.__call(url,arguments,'<?php echo $method; ?>');
    };
<?php
}
?>
    
    return oParent;
}

<?php
        $Code->append(ob_get_contents());
        ob_end_clean();
    }
}
