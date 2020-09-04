<?php
/**
 * Jaws Upgrade Class - Takes care of all the upgrade steps
 *
 * @category   Application
 * @package    Upgrade
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright  2005-2020 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class JawsUpgrader
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
     * The filesystem path the upgrader is running from.
     * @var string
     */
    var $_db_file_config;

    /**
     * The complete stage list.
     * @var array
     */
    var $Stages = array();

    /**
     * Constructor
     *
     * @param string The path this upgrader is running from.
     */
    private function __construct($stage, $db_config = null)
    {
        $this->name = $stage['name'];
        $this->file = $stage['file'];

        $this->_db_file_config = $db_config;
    }

    /**
     * Loads a stage based on an array of information.
     * The array should be like this:
     *   name => 'Human Readable Name of Stage'
     *   file => 'stage'
     *
     * file should be the file the stage's class is stored
     * in, without the .php extension.
     *
     * @access  public
     * @param   array   Information on the stage being loaded.
     * @param   boolean If the function should return the instance for the stage
     *
     * @return  object|bool|Jaws_Error
     */
    function LoadStage($stage, $instance = true)
    {
        $file = 'stages/' . $stage['file'] . '.php';
        if (!file_exists($file)) {
            Jaws_Error::Fatal(
                'The ' . $stage['name'] . " stage couldn't be loaded, because " . $stage['file'] . ".php doesn't exist."
            );
        }

        if ($instance) {
            include_once $file;
            $classname = 'Upgrader_' . $stage['file'];
            $classExists = version_compare(PHP_VERSION, '5.0', '>=') ?
                           class_exists($classname, false) : class_exists($classname);
            if ($classExists) {
                if (isset($stage['options'])) {
                    $stage = new $classname($stage['options']);
                } else {
                    $stage = new $classname;
                }

                return $stage;
            }

            Jaws_Error::Fatal(
                "The ".$stage['name']." stage couldn't be loaded, because the class ". $stage['file']." couldn't be found."
            );
        }

        $this->Stages[] = $stage;
        return true;
    }

    /**
     * Returns an array containing information about the stages.
     *
     * @access  public
     * @return  array
     */
    function GetStages()
    {
        return $this->Stages;
    }

    /**
     * Loads the list of stages available from a stage list file.
     *
     * @access  public
     * @param   string The file to load the stages from.
     * @return  bool|Jaws_Error
     */
    function LoadStages(&$stages)
    {
        foreach ($stages as $stage) {
            if (isset($stage['name']) && isset($stage['file'])) {
                $this->LoadStage($stage, false);
            }
        }
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
            if (!file_exists($file)) {
                Jaws_Error::Fatal(
                    'The ' . $stage['file'] .
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
     * Builds the upgrader page.
     *
     * @access  public
     * @return  string      A block of valid XHTML to display an introduction and form.
     */
    function Display()
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
    function Validate()
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
    function Run()
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
            Jaws_Translate::TRANSLATE_UPGRADE,
            '',
            $string,
            $params
        );
    }

}