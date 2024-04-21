<?php
/**
 * Jaws template engine inspired Liquid/Django template structure
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
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
    private $loadFromTheme = false;

    /**
     * load RTL template file id exists
     *
     * @var bool
     */
    private $loadRTLDirection = null;

    /**
     * theme information array
     *
     * @var array
     */
    private $theme = array();

    /**
     * theme layout name
     *
     * @var string
     */
    private $layout = '';

    /**
     * template root path
     *
     * @var string
     */
    private $tplRootPath = '';

    /**
     * @var context object
     */
    private $context;

    /**
     * @var engine parser object
     */
    public $parser;

    /**
     * @var The Document that represents the template
     */
    private $document;

    /**
     * @var array Globally included filters
     */
    private $filters = array();

    /**
     * Constructor
     *
     * @return Jaws_XTemplate
     */
    public function __construct()
    {
        $this->app = Jaws::getInstance();
        $this->parser = new Jaws_XTemplate_Parser($this);

        $this->theme = $this->app->GetTheme();
        $layout = $this->app->layout->GetLayoutName();
        $this->layout = @is_dir($this->theme['path']. '/'. $layout)? $layout : '';
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
    public function readTemplateFile($fname, $fpath = '')
    {
        if (empty($fname)) {
            throw new Exception("Empty template name");
        }

        $nameRegex = new Jaws_Regexp('/^[^.\/][a-zA-Z0-9_\.\/-]+$/');
        if (!$nameRegex->match($fname)) {
            throw new Exception("Illegal template name '$fname'");
        }

        if (empty($fpath) || $fpath == '.') {
            $fpath = $this->tplRootPath;
        }

        $filePath = rtrim($fpath, '/');
        $fileExtn = strrchr($fname, '.');
        $fileName = substr($fname, 0, -strlen($fileExtn));

        // load from theme?
        if ($this->loadFromTheme) {
            $layout = empty($filePath)? '' : $this->layout;
            if (file_exists($this->theme['path']. $layout. '/'. $filePath. '/'. $fname)) {
                $filePath = $this->theme['path']. $layout. '/'. $filePath;
            } else {
                $filePath = ROOT_JAWS_PATH . $filePath;
            }
        }

        $prefix  = '';
        if ($this->loadRTLDirection ||
           (is_null($this->loadRTLDirection) && function_exists('_t') && Jaws::t('LANG_DIRECTION') == 'rtl')
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
     * Parses the given template file
     *
     * @param   string  $tplName
     * @param   string  $tplPath
     * @param   bool    $loadFromTheme  Try to load template from theme
     *
     * @return  Jaws_XTemplate
     */
    public function parse($tplName, $tplPath = '', $loadFromTheme = false)
    {
        $this->tplRootPath = $tplPath;
        $this->loadFromTheme = $loadFromTheme;
        $this->document = $this->parser->getDocument($tplName, $tplPath);
        return $this;
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

        return $this->document->render($this->context);
    }

}