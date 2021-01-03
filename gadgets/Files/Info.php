<?php
/**
 * Files Information
 *
 * @category    GadgetModel
 * @package     Files
 */
class Files_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $version = '0.4.0';

    /**
     * Default front-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_action = false;

    /**
     * Default back-end action name
     *
     * @var     string
     * @access  protected
     */
    var $default_admin_action = 'Files';

    /**
     * Default Filesystem Management driver
     *
     * @var     int
     * @access  public
     */
    public $fmDriver = 'File'; // readonly

    /**
     * Constructor
     *
     * @access  public
     * @param   string  $gadget Gadget's name(filesystem name)
     * @return  void
     */
    function __construct($gadget)
    {
        parent::__construct($gadget);
        $this->fileManagement = Jaws_FileManagement::getInstance(
            $this->gadget->registry->fetch('fm_driver')
        );
    }

}