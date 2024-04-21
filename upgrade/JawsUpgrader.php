<?php
/**
 * Jaws Upgrade Class - Takes care of all the upgrade steps
 *
 * @category   Application
 * @package    Upgrade
 * @author     Jon Wood <jon@substance-it.co.uk>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright   2005-2024 Jaws Development Group
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
    private static $stages = array();

    /**
     * Constructor
     *
     * @param string The path this upgrader is running from.
     */
    protected function __construct($stage, $db_config = null)
    {
        $this->name = $stage['name'];
        $this->file = $stage['file'];

        $this->_db_file_config = $db_config;
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
    static function loadStage($stage, $options = array())
    {
        if (!isset(self::$stages[$stage]['obj'])) {
            try {
                $file = 'stages/' . self::$stages[$stage]['file'] . '.php';
                include_once $file;
                $classname = 'Upgrader_' . self::$stages[$stage]['file'];
                self::$stages[$stage]['obj'] = new $classname(self::$stages[$stage], $options);
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
                    'The ' . $stage['file'] .
                    " stage couldn't be loaded, because " .
                    $stage['file'] . ".php doesn't exist.",
                    __FILE__,
                    __LINE__
                );
            }

            if (isset($stage['vars'])) {
                $name = self::t($stage['name'], ...$stage['vars']);
            } else {
                $name = self::t($stage['file']);
            }

            self::$stages[] = array(
                'name' => $name,
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