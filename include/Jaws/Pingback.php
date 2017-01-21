<?php
/**
 * Manages the pinkback, sends and receives
 *
 * @category   Services
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2006-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Pingback
{
    /**
     * PEAR pingback object
     *
     * @access  private
     * @type    Pingback
     */
    var $_pingback;

    /**
     * Public constructor
     *
     * @access  public
     */
    function __construct()
    {
        $options = array(
            'timeout' => 5,
            'debug'   => false
        );

        require_once PEAR_PATH. 'Services/Pingback.php';
        $this->_pingback = new Services_Pingback(null, $options);
    }

    /**
     * Public constructor using singleton
     *
     * @access  public
     */
    static function getInstance()
    {
        static $instances;
        if (!isset($instances)) {
            $instances = array();
        }

        $signature = serialize(array('Jaws_Pingback'));
        if (!isset($instances[$signature])) {
            $instances[$signature] = new Jaws_Pingback();
        }
        return $instances[$signature];
    }

    /**
     * Reads a string, gets the links (with a regexp) and sends pingbacks to each link
     *
     * @access  public
     * @param   string   $source  Source URL (blog's URL post for example, a permalink)
     * @param   string   $message Message to parse
     * @return  bool     Success/Failure
     */
    function sendFromString($source, $message)
    {
        static $validateAdded;

        if (!isset($validateAdded)) {
            require PEAR_PATH. 'Validate.php';
        }
        $matches = array();
        preg_match_all("/<a[^>]*href=[\"']([^\"']*)[\"'][^>]*>(.*?)<\/a>/i", $message, $matches);
        $count = count($matches[0]);
        for ($i = 0; $i < $count; $i++) {
            $url = $matches[1][$i];
            if (Validate::URI($url) === true) {
                $this->send($source, $url);
            }
        }
    }

    /**
     * Print the basic headers:
     *
     *  - X-pingback
     *  - Adds a link to Layout (if we are running it)
     *
     * @access  public
     * @param   string  $uriListener  URI that listens
     */
    function showHeaders($uriListener)
    {
        header('X-Pingback: '.$uriListener);
        if (isset($GLOBALS['app']->Layout)) {
            $GLOBALS['app']->Layout->AddHeadLink(
                $uriListener,
                'pingback',
                ''
            );
        }
    }

    /**
     * Listen for pingbacks
     *
     * @access  public
     * @return  mixed   An array with basic data or Jaws_Error on failure
     *
     * Returned basic data:
     *
     *  - sourceURI: Who's pinging?
     *  - targetURI: Our permalink
     *  - title:     Title of post who's pinging
     *  - content:   It has the context, from exact target link position (optional)
     */
    function listen()
    {
        $this->_pingback->receive();
        $context = $this->_pingback->getSourceContext();
        if (!is_array($context)) {
            return new Jaws_Error('Unable to listen pingback',
                                  __FUNCTION__);
        }

        $response = array();
        $response['sourceURI'] = $this->_pingback->get('sourceURI');
        $response['targetURI'] = $this->_pingback->get('targetURI');
        $response['title']     = isset($context['title']) ? $context['title'] : '';
        $response['content']   = isset($context['content']) ? $context['content'] : '';
        return $response;
    }

    /**
     * Sends a pingback
     *
     * @access  public
     * @param   string   $source   Source URL (blog's URL post for example, a permalink)
     * @param   string   $target   Target URL
     * @return  bool     Success/Failure
     */
    function send($source, $target)
    {
        $send = array(
            'sourceURI' => $source,
            'targetURI' => $target
        );
        $this->_pingback->send($send);
    }
}