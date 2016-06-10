<?php
/**
 * Jaws Model schema
 *
 * @category    Gadget
 * @package     Core
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Pablo Fischer <pablo@pablo.com.mx>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_Gadget_Model
{
    /**
     * Jaws_Gadget object
     *
     * @var     object
     * @access  public
     */
    public $gadget = null;

    /**
     * Store models objects for later use so we aren't running around with multiple copies
     * @var     array
     * @access  private
     */
    private $objects = array();


    /**
     * constructor
     *
     * @access  public
     * @param   object $gadget Jaws_Gadget object
     * @return  void
     */
    public function __construct($gadget)
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
    public function &load($filename = '')
    {
        // filter non validate character
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        if (!isset($this->objects['Model'][$filename])) {
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
                return Jaws_Error::raiseError("File [$file] not exists!", __FUNCTION__, JAWS_ERROR_ERROR, 1);
            }

            include_once($file);
            if (!Jaws::classExists($classname)) {
                return Jaws_Error::raiseError("Class [$classname] not exists!", __FUNCTION__, JAWS_ERROR_ERROR, 1);
            }

            $this->objects['Model'][$filename] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget model: [$classname]");
        }

        return $this->objects['Model'][$filename];
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
    public function &loadAdmin($filename = '')
    {
        // filter non validate character
        $filename = preg_replace('/[^[:alnum:]_]/', '', $filename);

        if (!isset($this->objects['AdminModel'][$filename])) {
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

            $this->objects['AdminModel'][$filename] = new $classname($this->gadget);
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, "Loaded gadget model: [$classname]");
        }

        return $this->objects['AdminModel'][$filename];
    }


    /**
     * Gets gadgets bases on this gadget
     *
     * @access  public
     * @return  mixed   Array of gadgets otherwise Jaws_Error
     */
    public function requirementfor()
    {
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        return $tblReg->select('component')
            ->where('key_name', 'requirement')
            ->and()
            ->where('key_value', '%,'. $this->gadget->name. ',%', 'like')
            ->fetchColumn();
    }


    /**
     * Gets gadgets recommended this gadget
     *
     * @access  public
     * @return  mixed   Array of gadgets otherwise Jaws_Error
     */
    public function recommendedfor()
    {
        $tblReg = Jaws_ORM::getInstance()->table('registry');
        $result = $tblReg->select('component')
            ->openWhere('key_name', 'requirement')->or()->closeWhere('key_name', 'recommended')
            ->and()
            ->where('key_value', '%,'. $this->gadget->name. ',%', 'like')
            ->fetchColumn();
        return Jaws_Error::IsError($result)? $result : array_unique($result);
    }


    /**
     * Performs any actions required to finish installing a gadget.
     * Gadgets should override this method only if they need to perform actions to install.
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    public function InstallGadget()
    {
        return true;
    }


    /**
     * Updates the gadget
     *
     * @access  public
     * @return  bool    True on success and Jaws_Error on failure
     */
    public function UpdateGadget()
    {
        return true;
    }


    /**
     * Get the total of data we have in a table
     *
     * @access  public
     * @param   string  $table  Table's name to query
     * @param   string  $pKey   Optional. Primary key to use for counting
     * @return  int     Total of data we have
     */
    public function TotalOfData($table, $pKey = 'id')
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
    public function GetRealFastURL($fast_url, $table, $unique_check = true, $field = 'fast_url')
    {
        if (is_numeric($fast_url)) {
            $fast_url = '-' . $fast_url . '-';
        }

        $fast_url = Jaws_UTF8::trim(Jaws_XSS::defilter($fast_url));
        $fast_url = preg_replace(
            array('#[^\p{L}[:digit:]_\.\-\s]#u', '#[\s_\-]#u', '#\-\+#u'),
            array('', '-', '-'),
            Jaws_UTF8::strtolower($fast_url)
        );
        $fast_url = Jaws_UTF8::substr($fast_url, 0, 90);

        if (!$unique_check) {
            return $fast_url;
        }

        $tblReg = Jaws_ORM::getInstance()->table($table);
        $result = $tblReg->select("count($field)")->where($field, $fast_url.'%', 'like')->fetchOne();
        if (Jaws_Error::IsError($result) || empty($result)) {
            return $fast_url;
        }

        return $fast_url. '-'. $result;
    }

    /**
     * it starts looking for a 'valid' meta_title using the 'foobar-[1...n]' schema.
     *
     * @access  protected
     * @param   string     $meta_title     Meta title
     * @return  string     Correct fast URL
     */
    public function GetMetaTitleURL($meta_title)
    {
        if (is_numeric($meta_title)) {
            $meta_title = '-' . $meta_title . '-';
        }

        $meta_title = Jaws_UTF8::trim(Jaws_XSS::defilter($meta_title));
        $meta_title = preg_replace(
            array('#[^\p{L}[:digit:]_\.\-\s]#u', '#[\s_\-]#u', '#\-\+#u'),
            array('', '-', '-'),
            Jaws_UTF8::strtolower($meta_title)
        );
        $fast_url = Jaws_UTF8::substr($meta_title, 0, 90);

        return $meta_title;
    }

    /**
     * Get Id from meta title URL
     *
     * @access  public
     * @param   string  $metaTitleURL
     * @return  int     True or Redirect URL
     */
    function GetIdFromMetaTitleURL($metaTitleURL)
    {
        if (empty($metaTitleURL)) {
            return 0;
        }
        if (is_numeric($metaTitleURL)) {
            return (int)$metaTitleURL;
        }
        $items = explode('-', $metaTitleURL);
        return (is_array($items) && count($items) > 0) ? (int)$items[0] : 0;
    }

}