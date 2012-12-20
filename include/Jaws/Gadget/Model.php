<?php
/**
 * Jaws Model schema
 *
 * @category   Gadget
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Model extends Jaws_Gadget
{
    /**
     * Refactor Init, Jaws_Model::Init()
     *
     * @access  public
     * @param   string $gadget Gadget's name(same as the filesystem name)
     * @return  void
     */
    function Jaws_Gadget_Model($gadget = '')
    {
        parent::Jaws_Gadget($gadget);
    }

    /**
     * Performs any actions required to finish installing a gadget.
     * Gadgets should override this method only if they need to perform actions to install.
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function InstallGadget()
    {
        return true;
    }

    /**
     * Updates the gadget
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    function UpdateGadget()
    {
        return true;
    }

    /**
     * @access  public
     */
    function InstallSchema($main_schema, $variables = array(), $base_schema = false, $data = false, $create = true, $debug = false)
    {
        $info = $GLOBALS['app']->LoadGadget($this->name, 'Info');
        $main_file = JAWS_PATH . 'gadgets/'. $this->name . '/schema/' . $main_schema;
        if (!file_exists($main_file)) {
            return new Jaws_Error (_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', $main_schema),
                                   $info->GetAttribute('Name'),
                                   JAWS_ERROR_ERROR,
                                   1);
        }

        $base_file = false;
        if (!empty($base_schema)) {
            $base_file = JAWS_PATH . 'gadgets/'. $this->name . '/schema/' . $base_schema;
            if (!file_exists($base_file)) {
                return new Jaws_Error(_t('GLOBAL_ERROR_SQLFILE_NOT_EXISTS', $base_schema),
                                      $info->GetAttribute('Name'),
                                      JAWS_ERROR_ERROR,
                                      1);
            }
        }

        $result = $GLOBALS['db']->installSchema($main_file, $variables, $base_file, $data, $create, $debug);
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error(_t('GLOBAL_ERROR_FAILED_QUERY_FILE',
                                     $main_schema . (empty($base_schema)? '': "/$base_schema")),
                                  $info->GetAttribute('Name'),
                                  JAWS_ERROR_ERROR,
                                  1);
        }

        return true;
    }

    /**
     * Wrapper of $GLOBALS['app']->Shouter->Shout() for models
     *
     * @access  protected
     */
    function Shout($call, $param, $time = null)
    {
        $GLOBALS['app']->loadClass('Shouter', 'Jaws_EventShouter');
        return $GLOBALS['app']->Shouter->Shout($call, $param, $time);
    }

    /**
     * Return an array with the Search Results
     * This method must be implemented by each model
     *
     * Struct spec:
     * title - Title of the resource
     * url - URL to resource found
     * image - URL to image(can be relative or absolute, suggested size: 133x100)
     * snippet - Snippet of the result(can be null)
     * date - Insert or update date(can be null)
     */
    function Search($string)
    {
        return false;
    }

    /**
     * Returns the fast URL of an entry
     *
     * @access  public
     * @param   string   $fastUrl  FastUrl string
     * @return  array    Entry info or false
     */
    function GetFastURL($fastUrl)
    {
        return false;
    }

    /**
     * Get the total of data we have in a table
     *
     * @access  public
     * @param   string  $table  Table's name to query
     * @param   string  $pKey   Optional. Primary key to use for counting
     * @return  int     Total of data we have
     */
    function TotalOfData($table, $pKey = 'id')
    {
        $sql = 'SELECT COUNT(['.$pKey.']) FROM [['. $table . ']]';
        $res = $GLOBALS['db']->queryOne($sql);
        return Jaws_Error::IsError($res) ? 0 : $res;
    }

    /**
     * Installs the ACLs defined in the Info
     *
     * @access  public
     */
    function InstallACLs()
    {
        $acls = array();
        $info = $GLOBALS['app']->LoadGadget($this->name, 'Info');
        foreach ($info->GetACLs() as $acl => $default) {
            if (false === stripos(serialize($acls), "\"{$acl}\"")) {
                $acls[] = array($acl, $default);
            }
        }

        $GLOBALS['app']->ACL->NewKeyEx($acls);
    }

    /**
     * Installs the ACLs defined in the Info
     *
     * @access  public
     */
    function UninstallACLs()
    {
        $info = $GLOBALS['app']->LoadGadget($this->name, 'Info');
        foreach($info->GetACLs() as $acl => $opts){
            $GLOBALS['app']->ACL->DeleteKey($acl);
        }
    }

    /**
     * Checks if fast_url already exists in a table, if it doesn't then it returns
     * the original fast_url (the param value). However, if it already exists then 
     * it starts looking for a 'valid' fast_url using the 'foobar-[1...n]' schema.
     *
     * @access  protected
     * @param   string     $fast_url     Fast URL
     * @param   string     $table        DB table name (without [[ ]])
     * @param   bool       $unique_check must be false in update methods
     * @param   string     $field        Table field where fast_url is stored
     * @return  string     Correct fast URL
     */
    function GetRealFastURL($fast_url, $table, $unique_check = true, $field = 'fast_url')
    {
        if (is_numeric($fast_url)) {
            $fast_url = '-' . $fast_url . '-';
        }

        $xss = $GLOBALS['app']->loadClass('XSS', 'Jaws_XSS');
        $fast_url = $xss->defilter($fast_url, true);

        if (version_compare(PHP_VERSION, '5.1.0', '<')) {
            $fast_url = preg_replace(array('#[^[:alnum:]_\.-\s]#u', '#[\s_-]#u', '#-+#u'),
                                     array('', '-', '-'),
                                     $GLOBALS['app']->UTF8->strtolower($fast_url));
        } else {
            $fast_url = preg_replace(array('#[^\p{L}[:digit:]_\.-\s]#u', '#[\s_-]#u', '#-+#u'),
                                     array('', '-', '-'),
                                     $GLOBALS['app']->UTF8->strtolower($fast_url));
        }

        if (!$unique_check) {
            return $fast_url;
        }

        $params = array();
        $params['fast_url'] = $fast_url;

        $sql = "
             SELECT COUNT(*)
             FROM [[$table]]
             WHERE [$field] = {fast_url}";

        $total = $GLOBALS['db']->queryOne($sql, $params);
        if (Jaws_Error::isError($total) || ($total == '0')) {
            return $fast_url;
        }

        //Get a list of fast_url's that match the current entry
        $params['fast_url'] = $GLOBALS['app']->UTF8->trim($fast_url).'%';

        $sql = "
             SELECT [$field]
             FROM [[$table]]
             WHERE [$field] LIKE {fast_url}";

        $urlList = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($urlList)) {
            return $fast_url;
        }

        $counter = 1;
        $numbers = array();
        foreach($urlList as $url) {
            //Matches the foo-\d?
            if (preg_match("/(.+?)-([0-9]{0,})/", $url[$field], $matches)) {
                $numbers[] = (int)$matches[2];
            }
        }
        if (count($numbers) == 0) {
            return $fast_url . '-1';
        }
        $lastNumber = $numbers[count($numbers)-1];
        return $fast_url.'-'.($lastNumber+1);
    }

    /**
     * Get permission on a gadget/task
     *
     * @param   string  $task   Task name
     * @param   string  $gadget Gadget name
     * @return  bool    True if granted, else False
     */
    function GetPermission($task, $gadget = false)
    {
        return $GLOBALS['app']->Session->GetPermission(empty($gadget)? $this->name : $gadget, $task);
    }

}