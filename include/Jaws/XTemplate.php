<?php
/**
 * Jaws template engine inspired Liquid/Django template structure
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * load customized template file from theme directory
     *
     * @var bool
     */
    private static $loadFromTheme = false;

    /**
     * load RTL template file id exists
     *
     * @var bool
     */
    private static $loadRTLDirection = null;

    /**
     * theme information array
     *
     * @var array
     */
    private static $theme = array();

    /**
     * theme layout name
     *
     * @var string
     */
    private static $layout = '';

    /**
     * template root path
     *
     * @var string
     */
    private static $tplRootPath = '';

    /**
     * @var Document The root of the node tree
     */
    private $document;

    /**
     * @var array Globally included filters
     */
    private $filters = array();

    /**
     * Constructor
     *
     * @param   bool    $loadFromTheme          Try to load template from theme
     * @param   bool    $loadGlobalVariables    Fetch and set global variables 
     *
     * @return Jaws_XTemplate
     */
    public function __construct($loadFromTheme = false, $loadGlobalVariables = true)
    {
        $this->app = Jaws::getInstance();

        if ($loadGlobalVariables) {
            self::$loadFromTheme = $loadFromTheme;
            self::$theme = $this->app->GetTheme();
            $layout = $this->app->layout->GetLayoutName();
            self::$layout = @is_dir(self::$theme['path']. '/'. $layout)? $layout : '';
        } else {
            self::$loadFromTheme = false;
        }

    }

    /**
     * Add the filter
     *
     * @param   string      $filter
     * @param   callable    $callback
     *
     * @return  void
     */
    public function addFilter($filter, callable $callback = null)
    {
        // Store callback for later use
        if ($callback) {
            $this->filters[] = [$filter, $callback];
        } else {
            $this->filters[] = $filter;
        }
    }

    /**
     * Retrieve a template file
     *
     * @param   string  $fname  File name
     * @param   string  $fpath  File path
     *
     * @return  string  template file content
     */
    public static function readTemplateFile($fname, $fpath = '')
    {
        if (empty($fname)) {
            throw new Exception("Empty template name");
        }

        $nameRegex = new Jaws_Regexp('/^[^.\/][a-zA-Z0-9_\.\/-]+$/');
        if (!$nameRegex->match($fname)) {
            throw new Exception("Illegal template name '$fname'");
        }

        if (empty($fpath) || $fpath == '.') {
            $fpath = self::$tplRootPath;
        }

        $filePath = rtrim($fpath, '/');
        $fileExtn = strrchr($fname, '.');
        $fileName = substr($fname, 0, -strlen($fileExtn));

        // load from theme?
        if (self::$loadFromTheme) {
            $layout = empty($filePath)? '' : self::$layout;
            if (file_exists(self::$theme['path']. $layout. '/'. $filePath. '/'. $fname)) {
                $filePath = self::$theme['path']. $layout. '/'. $filePath;
            } else {
                $filePath = ROOT_JAWS_PATH . $filePath;
            }
        }

        $prefix  = '';
        if (self::$loadRTLDirection ||
           (is_null(self::$loadRTLDirection) && function_exists('_t') && Jaws::t('LANG_DIRECTION') == 'rtl')
        ) {
            $prefix = '.rtl';
        }

        $tplFile = $filePath. '/'. $fileName. $prefix. $fileExtn;
        $tplExists = file_exists($tplFile);
        if (!$tplExists && !empty($prefix)) {
            $tplFile = $filePath. '/'. $fileName. $fileExtn;
            $tplExists = file_exists($tplFile);
        }

        if (!$tplExists) {
            throw new Exception('Template '. $tplFile. ' doesn\'t exists');
        }

        return @file_get_contents($tplFile);
    }

    /**
     * Parses the given source string
     *
     * @param   string  $source
     *
     * @return  Jaws_XTemplate
     */
    public function parse($source)
    {
        $this->parser = new Jaws_XTemplate_Parser($source);
        return $this;
    }

    /**
     * Parses the given template file
     *
     * @param   string  $tplName
     * @param   string  $tplPath
     *
     * @return  Jaws_XTemplate
     */
    public function parseFile($tplName, $tplPath = '')
    {
        self::$tplRootPath = $tplPath;
        return $this->parse(self::readTemplateFile($tplName, $tplPath));
    }

    /**
     * Renders the current template
     *
     * @param   array   $assigns    an array of values for the template
     * @param   array   $filters    additional filters for the template
     *
     * @return string
     */
    public function render(array $assigns = array(), $filters = null)
    {
        $this->context = new Jaws_XTemplate_Context($assigns);

        if (!is_null($filters)) {
            $this->filters = array_merge($this->filters, $filters);
        }

        foreach ($this->filters as $filter) {
            if (is_array($filter)) {
                // Unpack a callback saved as second argument
                $this->context->addFilter(...$filter);
            } else {
                $this->context->addFilter($filter);
            }
        }

        return $this->parser->render($this->context);
    }

}