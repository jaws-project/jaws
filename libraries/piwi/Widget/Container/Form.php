<?php
/**
 * Form.php - Form Class
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
require_once PIWI_PATH . '/Widget/Container/Container.php';

define('FORM_REQ_PARAMS', 1);
class Form extends Container
{
    /**
     * Action to execute
     *
     * @var      string $_action
     * @access   private
     * @see      setAction(), getAction()
     */
    var $_action;

    /**
     * Use Method
     *
     * @var      string $_method
     * @access   private
     * @see      setMethod(), getMethod()
     */
    var $_method;

    /**
     * Form target
     *
     * @var    string 
     * @access private
     * @see    setTarget
     */
    var $_target;

    /**
     * Table Class
     *
     * @var      string $_tableClass
     * @access   private
     * @see      setTableClass(), getTableClass()
     */
    var $_tableClass;

    /**
     * Determinates of we should encode the action
     *
     * @var      string $_encodeUrl
     * @access   private
     * @see      encodeUrl(), isUrlEncoded()
     */
    var $_encodeUrl;

    /**
     * Hidden items will be saved in another array
     *
     * @var      array    $_hiddenItems
     * @access   private
     */
    var $_hiddenItems;

    /**
     * Set the encoding type
     *
     * @var      string   $_encodingType
     * @access   private
     */
    var $_encodingType;

    /**
     * Flag that tells the widget if validation should be available or no
     *
     * @var      boolean  $_shouldValidate
     * @access   private
     * @see      shouldValidate()
     */
    var $_shouldValidate;
    var $_customValidate;

    /**
     * The javascript code to validate the form
     *
     * @var      string   $_JSValidCode
     * @access   private
     */
    var $_JSValidCode;

    /**
     * The submitted values
     *
     * @var mixed string/array the values passed
     * @access private
     */
    var $_submitValues;

    /**
     * Public constructor
     *
     * @param    string  $action Action to use
     * @param    string  $method Method to use
     * @param    string  $encoding Encoding to use
     * @param    string  $id     Id of the form
     *
     * @access   public
     */
    function __construct($action, $method = 'get', $encoding = '', $id = '')
    {
        $this->_action         = $action;
        $this->_method         = strtolower($method);
        $this->_hiddenItems    = array();
        $this->_shouldValidate = false;
        $this->_customValidate = false;
        $this->encodeUrl(false);
        $this->setEncodingType($encoding);

        $this->setTableClass('tableform');

        if (empty($id)) {
            $this->_id = 'form_' . rand(1, 100);
        } else {
            $this->_id = $id;
        }

        parent::init();
    }

    /**
     * Set the form target 
     *
     * @access   public
     * @param    string $target Form target
     */
    function setTarget($target)
    {
        $this->_target = $target;
    }

    /**
     * Turns on or off the form validation
     *
     * @param    boolean $flag  Status of validation (on or off)
     * @access   public
     */
    function shouldValidate($flag = true, $custom = false)
    {
        $this->_shouldValidate = $flag;
        $this->_customValidate = $custom;
    }

    /**
     * Set the class name of the table form
     *
     * @param    string  $class Table Class
     * @access   public
     */
    function setTableClass($class)
    {
        $this->_tableClass = $class;
    }

    /**
     * Get the class name of the table form
     *
     * @access   public
     */
    function getTableClass()
    {
        return $this->_tableClass;
    }

    /**
     * Set the encoding type of the table form
     *
     * @param    string  $encoding Encoding Type
     * @access   public
     */
    function setEncodingType($encoding)
    {
        $this->_encodingType = $encoding;
    }

    /**
     * Get the encoding type of the table form
     *
     * @access   public
     * @return   string  The encoding type
     */
    function getEncodingType()
    {
        return $this->_encodingType;
    }

    /**
     * Should we encode the url?
     *
     * @param    string  $encode_url Flag that determinates if it's true
     * @access   public
     */
    function encodeUrl($encode_url = true)
    {
        $this->_encodeUrl = $encode_url;
    }

    /**
     * Returns true if we are encoding the url, otherwise, returns false
     *
     * @access   public
     */
    function isUrlEncoded()
    {
        return $this->_encodeUrl;
    }

    /**
     * Add an item, but just bin widgets, fieldsets or form boxes
     *
     * @param     object  $item     The item
     * @param     string  $comment  If item needs a comment (down)
     * @param     string  $warning  If item needs a warning (up)
     * @access    public
     */
    function add(&$item, $comment = '', $warning = '')
    {
        $familyWidget = $item->getFamilyWidget();
        if ($familyWidget == 'container' || $familyWidget == 'bin') {
            if ($item->getClassName() != 'hiddenentry') {
                array_push($this->_items, array('control' => &$item,
                                                'comment' => $comment,
                                                'warning' => $warning));
            } else {
                array_push($this->_hiddenItems, array('control' => &$item));
            }

            if ($familyWidget == 'container') {
                $item->_useTitles = true;
            }
        } else {
            die("Sorry, you can only add bin widgets, fieldsets or form boxes");
        }
    }

    /**
     * Get Child items
     *
     * @access    public
     */
    function getChildItems(&$items)
    {
        $xhtml = '';
        foreach ($items as $item) {
            if ($item->getFamilyWidget() == 'container') {
                //ok.. is a container, what's the size?
                $items = $item->getItems();
                $size = count($items);

                if ($size > 0) {
                    $xhtml .= $this->getChildItems($items);
                } else {
                    $xhtml .= $item->getItemsWithTitles();
                }
            }
        }

        return $xhtml;
    }

    function applyFilter($element, $filter)
    {
        if (!is_callable($filter)) {
            return Piwi::raiseError(PIWI_INVALID_FILTER, null, null, 'invalid filter passed');
        }
        if ($element == '__ALL__') {
            $this->_submitValues = $this->_recurisiveFilter($filter, $this->submitValues);
        } else {
            if (!is_array($element)) {
                $element = array($element);
            }

            foreach ($element as $elem) {
                // DO soemthing here..
            }
        }
    }

    /**
      * Recursively apply a filter function
      *
      * @param     string   $filter  filter to apply
      * @param     mixed    $value   submitted values
      * @access    private
      * @return    cleaned values
      */
    function _recurisiveFilter($filter, $value)
    {
        if (is_array($value)) {
            $cleanValues = array();
            foreach ($value as $k => $v) {
                $cleanValues[$k] = $this->_recursiveFilter($filter,  $value[$k]);
            }
            return $cleanValues;
        } else {
            return call_user_func($filter, $value);
        }
    }

    /**
     * Build the XHTML data
     *
     * @access    public
     */
    function buildXHTML()
    {
        $this->_XHTML = "<form ";
        if (!empty($this->_name)) {
            $this->_XHTML .= "name=\"".$this->_name."\" ";
        }

        if (!empty($this->_encodingType)) {
            $this->_XHTML .= "enctype=\"".$this->_encodingType."\" ";
        }

        if ($this->_encodeUrl) {
            $this->_XHTML .= "action=\"".urlencode($this->_action)."\" ";
        } else {
            $this->_XHTML .= "action=\"".$this->_action."\" ";
        }

        if (!empty($this->_target)) {
            $this->_XHTML.= " target=\"{$this->_target}\" ";
        }

        $this->_XHTML .= "method=\"".$this->_method."\"";
        $this->_XHTML .= $this->buildBasicXHTML();

        if ($this->_shouldValidate) {
            $this->_XHTML .= " onsubmit=\"return ValidateForm_".$this->_id."(this);\"";
        }

        $this->_XHTML .= ">\n";

        foreach ($this->_hiddenItems as $item) {
            $this->_XHTML .= $item['control']->get();
        }

        $this->_JSCode = "<script type=\"text/javascript\">\n";
        $this->_JSCode.= "//<![CDATA[\n";
        $this->_JSCode.= "function ValidateForm_".$this->_id."(form) {\n";

        $this->_XHTML .= '<table';
        if (!empty($this->_tableClass)) {
            $this->_XHTML .= ' class="' . $this->_tableClass . '"';
        }
        $this->_XHTML .= "><tr><td>\n";

        foreach ($this->_items as $item) {
            $this->addJS($item['control']->getJS());
            $this->addFiles($item['control']->getFiles());


            if ($item['control']->isPackable()) {
                $this->_XHTML .= $item['control']->getItemsWithTitles();
                $this->_JSCode.= $item['control']->getItemValidators();
            } else {
                $title = $item['control']->getTitle();
                $control = $item['control']->get(false);
                $comment = $item['comment'];
                $warning = $item['warning'];
                $name    = $item['control']->getID();
                $usetwocolons = $item['control']->requiresTwoColons();
                $validators = $item['control']->getValidators();

                foreach ($validators as $validator) {
                    $this->_JSCode.= $validator->getCode();
                }

                switch ($item['control']->getClassName()) {
                    case 'button':
                        $this->_XHTML .= "  <div class=\"buttons\">\n";
                        $this->_XHTML .= $item['control']->get(false);
                        $this->_XHTML .= "  </div>";
                        break;

                    default:
                        if (!empty($warning)) {
                            $this->_XHTML .= "  <div class=\"form_warning\">\n";
                            $this->_XHTML .= "    ${warning}\n";
                            $this->_XHTML .= "  </div>\n";
                        }

                        //WAS:
                        if (!empty($title)) {
                            if ($usetwocolons) {
                                $this->_XHTML .= "  <div><label for=\"${name}\">${title}:</label>\n";
                            } else {
                                $this->_XHTML .= "  <div><label for=\"${name}\">${title}</label>\n";
                            }
                        }
                        $this->_XHTML .= "  ${control}";
                        $this->_XHTML .= " </div>\n";

                        if (!empty($comment)) {
                            $this->_XHTML .= "  <span class=\"form_comment\">\n";
                            $this->_XHTML .= "    ${comment}\n";
                            $this->_XHTML .= "  </span>\n";
                        }

                        break;
                }
            }
        }

        $this->_XHTML .= "</td></tr></table></form>\n";
        $this->_JSCode.= "}\n";
        $this->_JSCode.= "//]]>\n";
        $this->_JSCode.= "</script>\n";

        if ($this->_shouldValidate && !$this->_customValidate) {
            $this->_XHTML = $this->_JSCode."\n\n".$this->_XHTML;
        }
    }
}
