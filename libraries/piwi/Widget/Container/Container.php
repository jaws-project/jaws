<?php
/*
 * Container.php - Main Class for all container widgets
 *
 * @version  $Id $
 * @author   Pablo Fischer <pablo@pablo.com.mx>
 *
 * <c> Pablo Fischer 2004
 * <c> Piwi
 */
define ('PIWI_INVALID_FILTER', -1);
define ('PIWI_INVALID_FILTER_MESSAGE', 'Invalid filter passed');

require_once PIWI_PATH . '/Widget/Widget.php';



class Container extends Widget
{
    /**
     * An array with all the items contained in the widget
     *
     * @var     array $_items
     * @access  private
     * @see     add(), delete()
     *
     */
    var $_items;

    /**
     * Border of the container
     *
     * @var     array $_border
     * @access  private
     * @see     setBorder(), getBorder()
     *
     */
    var $_border;

    /**
     * Direction of the box (horizontal or vertical)
     *
     * @var    $_direction
     * @access private
     * @see    getDirection()
     */
    var $_direction;

    /**
     * Set the spacing
     *
     * @var    $_spacing
     * @access private
     * @see    getSpacing(), setSpacing()
     */
    var $_spacing;

    /**
     * Flag to determinate if widget should get the child items with their titles
     *
     * @var    boolean  $_useTitles
     * @access private
     */
    var $_useTitles;

    /**
     * The submitted values
     *
     * @var mixed string/array the values passed
     * @access private
     */
    var $_submitValues;

    /**
     * Container Initializer
     *
     * @access private
     */
    function init()
    {
        $this->_items        = array();
        $this->_packable     = true;
        $this->_useTitles    = false;
        $this->_familyWidget = 'container';
        parent::init();

    }

    /**
     * Add an item to the container
     *
     * @param  object The item
     * @param  string If you want to identify your item, you should give it a name
     * @access public
     */
    function add(&$item, $identifier = '')
    {
        if (!isset($item)) {
            return false;
        }

        if (empty($identifier)) {
            $this->_items[] = $item;
        } else {
            $this->_items[$identifier] = $item;
        }
    }

    /**
     * Delete an item from the container (only if you know the ID!)
     *
     * @param   string The ID of the item
     * @access  public
     */
    function delete($identifier)
    {
        unset($this->_items[$identifier]);
    }

    /**
     * Get the items
     *
     * @param   string The ID of the item
     * @access  public
     */
    function getItem($id)
    {
        return $this->_items[$id];
    }

    /**
     * Get the items
     *
     * @access  public
     */
    function getItems()
    {
        return $this->_items;
    }

    /**
     * Set the border
     *
     * @param  int $border Border
     * @access public
     */
    function setBorder($border)
    {
        $this->_border = $border;
    }

    /**
     * Get the border
     *
     * @access public
     */
    function getBorder()
    {
        return $this->_border;
    }

    /**
     * Set the spacing
     *
     * @param  int   $spacing The Spacing
     * @access public
     */
    function setSpacing($spacing)
    {
        $this->_spacing = $spacing;
    }

    /**
     * Get the spacing
     *
     * @access public
     * @return int    Spacing of the box
     */
    function getSpacing()
    {
        return $this->_spacing;
    }

    /**
     * Get the direction
     *
     * @access public
     * @return string Direction of the box
     */
    function getDirection()
    {
        return $this->_direction;
    }

    /**
     * Set the direction
     *
     * @param  int   $direction The Direction
     * @access public
     */
    function setDirection($direction)
    {
        $this->_direction = $direction;
    }

    /**
     * Build the basic piwiXML data, adding container params
     *
     * @access   private
     */
    function buildBasicPiwiXML()
    {
        parent::buildBasicPiwiXML();

        if (is_numeric($this->_border)) {
            $this->_PiwiXML->addAttribute('border', $this->_border);
        }

        if (is_numeric($this->_spacing)) {
            $this->_PiwiXML->AddAttribute('spacing', $this->_spacing);
        }
    }

    /**
     * Build the basix XHTML data - Adding Container params
     *
     * @access   private
     * @return   string  XHTML data
     */
    function buildBasicXHTML()
    {
        $xhtml = '';
        if (is_numeric($this->_border)) {
            $xhtml .= " border=\"".$this->_border."px\"";
        }

        if (is_numeric($this->_spacing)) {
            $xhtml .= " cellspacing=\"".$this->_spacing."\"";
        }

        $xhtml .= parent::buildBasicXHTML();

        return $xhtml;
    }

    /**
     * Get the items with their titles
     *
     * @access   public
     */
    function getItemsWithTitles()
    {
        $this->_useTitles = true;
        $this->rebuild();
        return $this->get();
    }

    /**
     * Get item validators
     *
     * @access   public
     * @return   string  javascript code to validate
     */
    function getItemValidators()
    {
        $js = '';
        foreach ($this->_items as $item) {
            //hey.. some containers have the controls in a hash!
            if (isset($item) && is_array($item) && isset($item['control'])) {
                $item = $item['control'];
            }

            if ($item->isPackable()) {
                $js .= $item->getItemValidators();
            } else {
                $validators = $item->getValidators();
                foreach ($validators as $validator) {
                    $js .= $validator->getCode();
                }
            }
        }

        return $js;
    }
}
