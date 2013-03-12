<?php
/**
 * Jaws Model schema
 *
 * @category   Gadget
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Model
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  protected
     */
    var $gadget = null;

    /**
     * constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    function Jaws_Gadget_Model($gadget)
    {
        $this->gadget = $gadget;
    }

    /**
     * Loads the gadget model file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $type   Model type
     * @return  mixed   Model class object on successful, Jaws_Error otherwise
     */
    function &loadModel($type, $filename = '')
    {
        // filter non validate character
        $type = preg_replace('/[^[:alnum:]_]/', '', $type);
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        if (!isset($this->gadget->models[$type][$filename])) {
            switch ($type) {
                case 'Model':
                    if (empty($filename)) {
                        $type_class_name = $this->gadget->name. '_Model';
                        $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. '/Model.php';
                    } else {
                        $type_class_name = $this->gadget->name. "_Model_$filename";
                        $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Model/$filename.php";
                    }
                    break;

                case 'AdminModel':
                    if (empty($filename)) {
                        $type_class_name = $this->gadget->name. '_AdminModel';
                        $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. '/AdminModel.php';
                    } else {
                        $type_class_name = $this->gadget->name. "_Model_Admin_$filename";
                        $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Model/Admin/$filename.php";
                    }
                    break;
                default:
                    return Jaws_Error::raiseError("Gadget [$type] type not exists!", __FUNCTION__);
            }

            if (!@include_once($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            if (!Jaws::classExists($type_class_name)) {
                return Jaws_Error::raiseError("Class [$type_class_name] not exists!", __FUNCTION__);
            }

            $this->gadget->models[$type][$filename] = new $type_class_name($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget model: [$type_class_name]");
        }

        return $this->gadget->models[$type][$filename];
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
        $info = $GLOBALS['app']->LoadGadget($this->gadget->name, 'Info');
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
        $info = $GLOBALS['app']->LoadGadget($this->gadget->name, 'Info');
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

        $fast_url = preg_replace(array('#[^\p{L}[:digit:]_\.-\s]#u', '#[\s_-]#u', '#-+#u'),
                                 array('', '-', '-'),
                                 $GLOBALS['app']->UTF8->strtolower($fast_url));

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
        return $GLOBALS['app']->Session->GetPermission(empty($gadget)? $this->gadget->name : $gadget, $task);
    }

}