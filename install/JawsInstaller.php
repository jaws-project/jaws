<?php
/**
 * Jaws installer management class.
 *
 * @category   Application
 * @package    Install
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright   2005-2022 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class JawsInstaller
{
    /**
     * Stage name
     */
    var $name;

    /**
     * stage file name
     */
    var $file;

    /**
     * The filesystem path the installer is running from.
     * @var string
     */
    var $_stage_config;

    /**
     * The complete stage list.
     * @var array
     */
    private static $stages = array();

    /**
     * Predefined data
     * @var array
     * @access protected
     */
    var $_predefined = array();

    var $_isPredefined = false;

    /**
     * Constructor
     *
     * @param string The path this installer is running from.
     */
    protected function __construct($stage, $config = null)
    {
        $this->name = $stage['name'];
        $this->file = $stage['file'];

        $this->_stage_config = $config;
        /*
        if (Jaws_FileManagement_File::file_exists('data.ini')) {
            $this->_predefined = parse_ini_file('data.ini', true);
            $this->_isPredefined = true;
        }
        */
    }

    function hasPredefined()
    {
        return $this->_isPredefined;
    }

    function getPredefinedData()
    {
        return $this->_predefined;
    }

    /**
     * Loads a stage based on an array of information.
     * The array should be like this:
     *   name => "Human Readable Name of Stage"
     *   file => "stage"
     *
     * file should be the file the stage's class is stored
     * in, without the .php extension.
     *
     * @access  public
     * @param   int     $stage  Stage index number
     *
     * @return  object|bool|Jaws_Error
     */
    static function loadStage($stage)
    {
        if (!isset(self::$stages[$stage]['obj'])) {
            try {
                $file = 'stages/' . self::$stages[$stage]['file'] . '.php';
                include_once $file;
                $classname = 'Installer_' . self::$stages[$stage]['file'];
                self::$stages[$stage]['obj'] = new $classname(self::$stages[$stage]);
            } catch (Exception $e) {
                Jaws_Error::Fatal(
                    "The ".$stage['file']." stage couldn't be loaded",
                    __FILE__,
                    __LINE__
                );
            }

        }

        return self::$stages[$stage]['obj'];
    }

    /**
     * Returns stages
     *
     * @access  public
     * @return  array
     */
    function getStages()
    {
        return self::$stages;
    }

    /**
     * Returns count of stages
     *
     * @access  public
     * @return  array
     */
    function countStages()
    {
        return count(self::$stages);
    }

    /**
     * Loads the list of stages available from a stage list file.
     *
     * @access  public
     * @return  bool|Jaws_Error
     */
    static function loadStages()
    {
        require_once 'stagelist.php';

        foreach ($stages as $stage) {
            $file = 'stages/' . $stage['file'] . '.php';
            if (!Jaws_FileManagement_File::file_exists($file)) {
                Jaws_Error::Fatal(
                    'The ' . $stage['name'] .
                    " stage couldn't be loaded, because " .
                    $stage['file'] . ".php doesn't exist.",
                    __FILE__,
                    __LINE__
                );
            }

            self::$stages[] = array(
                'name' => self::t($stage['file']),
                'file' => $stage['file']
            );
        }
    }

    /**
     * Builds the installer page.
     *
     * @access  public
     * @return  string A block of valid XHTML to display an introduction and form.
     */
    function display()
    {
        return '';
    }

    /**
     * Validates any data provided to the stage.
     *
     * @access  public
     * @return  bool|Jaws_Error  Returns either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function validate()
    {
        return true;
    }

    /**
     * Does any actions required to finish the stage, such as DB queries.
     *
     * @access  public
     * @return  bool|Jaws_Error  Either true on success, or a Jaws_Error
     *                          containing the reason for failure.
     */
    function run()
    {
        return true;
    }

    /**
     * Convenience function to translate strings
     *
     * @param   string  $params Method parameters
     *
     * @return string
     */
    public static function t($params)
    {
        $params = func_get_args();
        $string = array_shift($params);

        return Jaws_Translate::getInstance()->XTranslate(
            '',
            Jaws_Translate::TRANSLATE_INSTALL,
            '',
            $string,
            $params
        );
    }

}