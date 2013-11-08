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
     * @param   string  $filename   Model class file name
     * @return  mixed   Model class object on successful, Jaws_Error otherwise
     */
    function &load($filename = '')
    {
        // filter non validate character
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        if (!isset($this->gadget->models['Model'][$filename])) {
            if (empty($filename)) {
                $classname = $this->gadget->name. '_Model';
                $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. '/Model.php';
                if (!file_exists($file)) {
                    return $this;
                }
            } else {
                $classname = $this->gadget->name. "_Model_$filename";
                $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Model/$filename.php";
            }

            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__);
            }

            $this->gadget->models['Model'][$filename] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget model: [$classname]");
        }

        return $this->gadget->models['Model'][$filename];
    }

    /**
     * Loads the gadget model file in question, makes a instance and
     * stores it globally for later use so we do not have duplicates
     * of the same instance around in our code.
     *
     * @access  public
     * @param   string  $filename   Model class file name
     * @return  mixed   Model class object on successful, Jaws_Error otherwise
     */
    function &loadAdmin($filename = '')
    {
        // filter non validate character
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        if (!isset($this->gadget->models['AdminModel'][$filename])) {
            if (empty($filename)) {
                $classname = $this->gadget->name. '_AdminModel';
                $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. '/AdminModel.php';
                if (!file_exists($file)) {
                    return $this;
                }
            } else {
                $classname = $this->gadget->name. "_Model_Admin_$filename";
                $file = JAWS_PATH. 'gadgets/'. $this->gadget->name. "/Model/Admin/$filename.php";
            }

            if (!file_exists($file)) {
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__);
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__);
            }

            $this->gadget->models['AdminModel'][$filename] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget model: [$classname]");
        }

        return $this->gadget->models['AdminModel'][$filename];
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
        $objORM = Jaws_ORM::getInstance()->table($table);
        $res = $objORM->select('count('.$pKey.')')->fetchOne();
        return Jaws_Error::IsError($res)? 0 : $res;
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

        $fast_url = $GLOBALS['app']->UTF8->trim(Jaws_XSS::defilter($fast_url, true));
        $fast_url = preg_replace(
            array('#[^\p{L}[:digit:]_\.-\s]#u', '#[\s_-]#u', '#-+#u'),
            array('', '-', '-'),
            $GLOBALS['app']->UTF8->strtolower($fast_url)
        );
        $fast_url = $GLOBALS['app']->UTF8->substr($fast_url, 0, 90);

        if (!$unique_check) {
            return $fast_url;
        }

        $params = array();
        $params['fast_url'] = $fast_url.'%';

        $sql = "
             SELECT [$field]
             FROM [[$table]]
             WHERE [$field] LIKE {fast_url}";

        $urlList = $GLOBALS['db']->queryAll($sql, $params);
        if (Jaws_Error::IsError($urlList) || empty($urlList)) {
            return $fast_url;
        }

        return $fast_url. '-'. (count($urlList) + 1);
    }

}