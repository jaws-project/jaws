<?php
/**
 * Class that manages/saves the basic info of a gadget
 *
 * @category   Gadget
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_GadgetInfo
{
    /**
     * Language translate name of the gadget
     *
     * @var    string
     * @access private
     */
    var $_Name = '';

    /**
     * Language translate description of the gadget
     *
     * @var    string
     * @access private
     */
    var $_Description = '';

    /**
     * Gadget version
     *
     * @var    string
     * @access private
     */
    var $_Version = '';

    /**
     * Is this gadget core gadget?
     *
     * @var    boolean
     * @access private
     */
    var $_IsCore = false;

    /**
     * Section of the gadget(Gadget, Customers, etc..)
     *
     * @var    string
     * @access private
     */
    var $_Section = '';

    /**
     * Base URL of gadget's documents
     *
     * @var    string
     * @access private
     */
    var $_Wiki_URL = JAWS_WIKI;

    /**
     * Format of gadget's documents url
     *
     * @var    string
     * @access private
     */
    var $_Wiki_Format = JAWS_WIKI_FORMAT;

    /**
     * Required gadgets
     *
     * @var    array
     * @access private
     */
    var $_Requires = array();

    /**
     * Default ACL value of frontend gadget access
     *
     * @var     boolean
     * @access  private
     */
    var $_DefaultACL = true;

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array();

    /**
     * Attributes of the gadget
     *
     * @var    array
     * @access private
     */
    var $_Attributes = array();

    /**
     * Name of the gadget
     *
     * @var    string
     * @access private
     */
    var $_Gadget = '';

    /**
     * Constructor
     *
     * @access public
     * @param  string $gadget Gadget's name(same as the filesystem name)
     * @return void
     */
    function Jaws_GadgetInfo($gadget)
    {
        $this->_Gadget      = $gadget;
        $this->_Name        = _t(strtoupper($gadget).'_NAME');
        $this->_Description = _t(strtoupper($gadget).'_DESCRIPTION');
    }

    /**
     * Initializes the Info object
     *
     * @deprecated
     * @access public
     * @param  string Gadget's name(same as the filesystem name)
     * @return void
     */
    function Init($gadget)
    {
        $this->Jaws_GadgetInfo($gadget);
    }

    /**
     * Sets an attribute
     *
     * @access protected
     * @param string $key         Attribute name
     * @param string $value       Attribute value
     * @param string $description Attribute description
     * @return void
     */
    function SetAttribute($key, $value, $description = '')
    {
        // Deprecated: using core_gadget attribute is deprecated
        if ($key == 'core_gadget') {
            $this->_IsCore = (bool)$value;
        } else {
            $this->_Attributes[$key] = array(
                'value'       => $value,
                'description' => $description
            );
        }
    }

    /**
     * Returns the value of the given attribute key
     *
     * @access protected
     * @param  string $key Attribute name
     * @return mixed  value of the given attribute key
     */
    function GetAttribute($key)
    {
        // Deprecated: using core_gadget attrinute is deprecated
        if ($key == 'core_gadget') {
            return $this->_IsCore;
        } elseif (array_key_exists($key, $this->_Attributes)) {
            return $this->_Attributes[$key]['value'];
        }

        return null;
    }

    /**
     * Sets the gadget name
     *
     * @access protected
     * @param  string    Gadget name
     */
    function GadgetName($name)
    {
        $this->_Name = $name;
    }

    /**
     * Gets the gadget name
     *
     * @access protected
     */
    function GetName()
    {
        return $this->_Name;
    }

    /**
     * Sets the section of the gadget(Gadget, Customers, etc..)
     *
     * @access protected
     * @param  string   Gadget's section
     */
    function GadgetSection($section)
    {
        $this->_Section = $section;
    }

    /**
     * Gets the gadget's section
     *
     * @access public
     * @return string Gadget's section
     */
    function GetSection()
    {
        if ($this->_IsCore) {
            $this->_Section = 'General';
        } elseif (empty($this->_Section)) {
            $this->_Section = 'Gadgets';
        }

        return $this->_Section;
    }

    /**
     * Sets the Jaws version that the gadget's depends on
     *
     * @access protected
     * @param  string    Jaws's version
     */
    function RequiresJaws($version)
    {
        $this->SetAttribute('JawsVersion', $version, _t('GLOBAL_GI_GADGET_JAWSVERSION'));
    }
    
    /**
     * Gets the jaws version that the gadget requires
     *
     * @access protected
     */
    function GetRequiredJawsVersion()
    {
        $jawsVersion = $this->GetAttribute('JawsVersion');
        if (is_null($jawsVersion)) {
            $jawsVersion = $GLOBALS['app']->Registry->Get('/config/version');
        }

        return $jawsVersion;
    }

    /**
     * Sets the manual/doc URL
     *
     * @deprecated
     * @access protected
     * @param  string  $page   Gadget's name
     * @param  string  $url    Manual/Doc base URL
     * @param  string  $format Format of manual/doc url
     * @return void
     */
    function Doc($page, $url = JAWS_WIKI, $format = JAWS_WIKI_FORMAT)
    {
        $this->_Wiki_URL    = $url;
        $this->_Wiki_Format = $format;
    }

    /**
     * Gets the gadget doc/manual URL
     *
     * @access protected
     * @return string Gadget's manual/doc url
     */
    function GetDoc()
    {
        $lang = $GLOBALS['app']->GetLanguage();
        return str_replace(array('{url}', '{lang}', '{page}', '{lower-page}',
                                 '{type}', '{lower-type}', '{types}', '{lower-types}'),
                           array($this->_Wiki_URL, $lang, $this->_Gadget, strtolower($this->_Gadget),
                                 'Gadget', 'gadget', 'Gadgets', 'gadgets'),
                           $this->_Wiki_Format);
    }

    /**
     * Sets the gadget description
     *
     * @access protected
     * @param   string Gadget description
     */
    function GadgetDescription($desc)
    {
        $this->_Description = $desc;
    }

    /**
     * Gets the gadget description
     *
     * @access protected
     */
    function GetDescription()
    {
        return $this->_Description;
    }

    /**
     * Sets the gadget version
     *
     * @access protected
     * @param  string    Gadget version
     */
    function GadgetVersion($version)
    {
        $this->_Version = $version;
    }

    /**
     * Gets the gadget version
     *
     * @access public
     * @return string Gadget's version
     */
    function GetVersion()
    {
        return $this->_Version;
    }

    /**
     * Register required gadgets
     *
     * @param string Gadget's name as arguments
     */
    function Requires()
    {
        $this->_Requires = func_get_args();
    }

    /**
     * Get the requirements of the gadget
     * @return  array Gadget's Requirements
     */
    function GetRequirements()
    {
        return $this->_Requires;
    }

    /**
     * Get all attributres for the gadet
     * @return  array Attributes of the gadget
     */
    function GetAttributes()
    {
        return $this->_info['attributes'];
    }

    /**
     * Set value of front-end default ACL
     *
     * @access  public
     * @param   boolean $default
     * @return  void
     */
    function SetDefaultACL($default = true)
    {
        $this->_DefaultACL = $default;
    }

    /**
     * Loads an associative array as the ACL keys and descriptions
     * according with the gadget name
     *
     * @access public
     * @param  array The array
     */
    function PopulateACLs($acls)
    {
        $this->_ACLs = is_array($acls)? $acls : array();
    }

    /**
     * Gets the short description of a given ACL key
     *
     * @access protected
     * @param  string     $key ACL Key
     * @return string     The ACL description
     */
    function GetACLDescription($key)
    {
        $key = substr(strrchr($key, '/'), 1);
        if (in_array($key, array('default', 'default_admin', 'default_registry'))) {
            return _t(strtoupper('GLOBAL_ACL_'. $key));
        } else {
            return _t(strtoupper($this->_Gadget. '_ACL_'. $key));
        }
    }

    /**
     * Get all ACLs for the gadet
     *
     * @access public
     * @return  array ACLs of the gadget
     */
    function GetACLs()
    {
        $result = array();
        foreach ($this->_ACLs as $key => $value) {
            //ACL comes with a value?
            if ($value === 'true' || $value === 'false') {
                $default = $value;
                $acl     = $key;
            } else {
                //False by default
                $default = 'false';
                $acl     = $value;
            }
            $result['/ACL/gadgets/'. $this->_Gadget. '/'. $acl] = $default;
        }

        // Adding common ACL keys
        $result['/ACL/gadgets/'. $this->_Gadget. '/default'] = $this->_DefaultACL? 'true' : 'false';
        $result['/ACL/gadgets/'. $this->_Gadget. '/default_admin'] = 'false';
        $result['/ACL/gadgets/'. $this->_Gadget. '/default_registry'] = 'false';

        return result;
    }

}