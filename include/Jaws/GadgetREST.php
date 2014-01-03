<?php
/**
 * Jaws Gadgets : REST part
 *
 * @category   Gadget
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
require_once JAWS_PATH . 'include/Jaws/Gadget.php';

class Jaws_GadgetREST extends Jaws_Gadget
{
    /**
     * Serializer
     *
     * @access  protected
     */
    var $_Serializer;

    /**
     * Unserializer
     *
     * @access  protected
     */
    var $_Unserializer;

    /**
     * Refactor Init, Jaws_GadgetHTML::Init() loads the Piwi stuff
     *
     * @access  protected
     * @param   string    $value Name of the gadget's model
     */
    function Init($model)
    {
        parent::Init($model);
        // Load Piwi if it's a web app
        require_once PEAR_PATH. 'XML/Serializer.php';
        require_once PEAR_PATH. 'XML/Unserializer.php';

        $options = array(
                         XML_SERIALIZER_OPTION_INDENT               => '    ',
                         XML_SERIALIZER_OPTION_LINEBREAKS           => "\n",
                         XML_SERIALIZER_OPTION_SCALAR_AS_ATTRIBUTES => true,
//                             XML_SERIALIZER_OPTION_ENCODE_FUNC          => 'strtoupper'
                         );

        $this->_Serializer   = &new XML_Serializer($options);
        $this->_Unserializer = &new XML_Unserializer();
        $this->_Unserializer->setOption('parseAttributes', true);
        $this->_Unserializer->setOption('decodeFunction', 'strtolower');

        require_once JAWS_PATH . 'include/Jaws/Shared.php';

        header('Content-Type: text/xml; charset=utf-8');
    }

    /**
     * Overloads Jaws_Gadget::IsValid. Difference: Checks that the gadget (HTML) file exists
     *
     * @access  public
     * @param   string  $gadget Gadget's Name
     * @return  bool    Returns true if the gadget is valid, otherwise will finish the execution
     */
    function IsValid($gadget)
    {
        // Check if file exists
        // Hack until we decide if $gadget.php will be a proxy file
        $filepath = JAWS_PATH . 'gadgets/'.$gadget.'/REST.php';
        if (!file_exists($filepath)) {
            Jaws_Error::Fatal('Gadget file doesn\'t exists');
        }

        parent::IsValid($gadget);

    }
}
