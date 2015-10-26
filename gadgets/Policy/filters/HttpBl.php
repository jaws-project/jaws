<?php
/**
 * Class that uses the HttpBL tool (a Projecthoneypot.org project) 
 * which takes the IP address of the user and checks if it's valid, if 
 * it is then the comment is validated as approved otherwise we mark it
 * as spam.
 *
 * @category   AntiSpamFilters
 * @package    AntiSpam
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class HttpBl 
{
    /**
     * Filter's version
     *
     * @access  private
     * @var     string
     */
    var $Version = '0.1';
    
    /**
     * HttpBl (projecthoneypot's API key)
     *
     * @access  private
     * @var     string
     */
    var $ApiKey;
    
    /**
     * Is ApiKey valid?
     *
     * We just check this property in two cases:
     *
     *  - If ApiKey isn't empty
     *  - Requesting httpbl returns true if key is valid
     *
     * @access  private
     */
    var $IsKeyValid;
    
    /**
     * HttpBl antispam filter constructor
     *
     * @access  public
     */    
    function HttpBl()
    {
        $this->Version = '0.1';
        if (!$this->VerifyKey()) {
            $this->IsKeyValid = false;
            $GLOBALS['log']->Log(JAWS_LOG_ERROR, 'Invalid ProjectHoneyPot Key, please check your Registry: '.
                                 '/gadgets/Policy/prjhoneypot_key');
        } else {
            $this->IsKeyValid = true;
        }
    }
    
    /**
     * Verify if key exists, and if it exists if it's not empty
     *
     * @access  private
     * @return  bool    Is key valid?
     */
    function VerifyKey()
    {
        if (is_null($GLOBALS['app']->Registry->fetch('prjhoneypot_key', 'Policy'))) {
            $GLOBALS['app']->Registry->insert('prjhoneypot_key', 'UNDEFINED', false, 'Policy');
        } 
        $value = $GLOBALS['app']->Registry->fetch('prjhoneypot_key', 'Policy');
        return (!empty($value) && $value !== 'UNDEFINED');
    }
    
    /**
     * Checks if user IP is marked as spam at HttpBl
     *
     * @param   string $permalink  Permalink of post
     * @param   string $type       Component's name
     * @param   string $name       Author's name
     * @param   string $email      Author's email
     * @param   string $message    Author's message
     *
     * As a note: Any of the params are really taken since
     * we need the user's IP
     *
     * @return  bool    Is it spam returns true otherwise we return false
     */
    function IsSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        if ($this->IsKeyValid === false) {
            return false;
        }
        //Take author's IP
        $ip     = $_SERVER['REMOTE_ADDR'];
        //Prepare the 'query'
        $query  = $this->ApiKey . '.' . 
            implode('.', array_reverse(explode('.', $ip))) . 
            '.dnsbl.httpbl.org';
        //Get the hostbyname value to see if IP is not banned
        $result = explode('.', gethostbyname($query));
        if ($result[0] == 127) {
            /**
             * Query was successful, check if IP comes from a spammer
             * or harvester
             */
            if ($result[3] == 4 ||
                $result[3] == 2) {
                return true; //It's spam
            }
        }
        return false;
    }

    /**
     * Since there's no real 'server' way to tell HttpBl a comment was a 
     * spam then we do nothing in this one
     *
     * @param   string $permalink  Permalink of post
     * @param   string $type       Component's name
     * @param   string $name       Author's name
     * @param   string $email      Author's email
     * @param   string $message    Author's message
     *
     * @access  public
     */
    function SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        return true;
    }
    
    /**
     * Since there's no real 'server' way to tell HttpBl a comment was ham
     * then we do nothing in this one
     *
     * @param   string $permalink  Permalink of post
     * @param   string $type       Component's name
     * @param   string $name       Author's name
     * @param   string $email      Author's email
     * @param   string $message    Author's message
     *
     * @access  public
     */
    function SubmitHam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        return true;
    }

}