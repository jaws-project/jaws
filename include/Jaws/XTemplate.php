<?php
/**
 * Jaws template engine inspired Liquid/Django template structure
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2021 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://shopify.github.io
 * @see         https://docs.djangoproject.com/en/3.1/topics/templates/
 * @see         https://github.com/harrydeluxe/php-liquid
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
     *
     * @var array configuration array
     */
    public static $config = array(
        // The method is called on objects when resolving variables to see
        // if a given property exists.
        'HAS_PROPERTY_METHOD' => 'field_exists',

        // This method is called on object when resolving variables when
        // a given property exists.
        'GET_PROPERTY_METHOD' => 'get',

        // Separator between filters.
        'FILTER_SEPARATOR' => '\|',

        // Separator for arguments.
        'ARGUMENT_SEPARATOR' => ',',

        // Separator for argument names and values.
        'FILTER_ARGUMENT_SEPARATOR' => ':',

        // Separator for variable attributes.
        'VARIABLE_ATTRIBUTE_SEPARATOR' => '.',

        // Whitespace control.
        'WHITESPACE_CONTROL' => '-',

        // Tag start.
        'TAG_START' => '{%',

        // Tag end.
        'TAG_END' => '%}',

        // Variable start.
        'VARIABLE_START' => '{{',

        // Variable end.
        'VARIABLE_END' => '}}',

        // Variable name.
        'VARIABLE_NAME' => '[a-zA-Z_][a-zA-Z_0-9.-]*',

        // Comparison operator
        'COMPARISON_OPERATOR' => '==|!=|<>|<=?|>=?|contains(?=\s)',

        'QUOTED_STRING' => '(?:"[^"]*"|\'[^\']*\')',
        'QUOTED_STRING_FILTER_ARGUMENT' => '"[^"]*"|\'[^\']*\'',

        // Automatically escape any variables unless told otherwise by a "raw" filter
        'ESCAPE_BY_DEFAULT' => false,

        // The name of the key to use when building pagination query strings e.g. ?page=1
        'PAGINATION_REQUEST_KEY' => 'page',

        // The name of the context key used to denote the current page number
        'PAGINATION_CONTEXT_KEY' => 'page',
    );

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
     * Get a configuration setting.
     *
     * @param   string  $key    setting key
     *
     * @return  string
     */
    public static function get($key)
    {
        if (array_key_exists($key, self::$config)) {
            return self::$config[$key];
        }
        // This case is needed for compound settings
        switch ($key) {
            case 'QUOTED_FRAGMENT':
                return '(?:' .
                    self::get('QUOTED_STRING') .
                    '|(?:[^\s,\|\'"]|' .
                    self::get('QUOTED_STRING') .
                    ')+)';
            case 'TAG_ATTRIBUTES':
                return '/(\w+)\s*\:\s*(' .
                    self::get('QUOTED_FRAGMENT') .
                    ')/';
            case 'TOKENIZATION_REGEXP':
                return '/(' .
                self::$config['TAG_START'] . '.*?' .
                self::$config['TAG_END'] . '|' .
                self::$config['VARIABLE_START'] . '.*?' .
                self::$config['VARIABLE_END'] .
                ')/';
            default:
                return null;
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
     * Tokenizes the given source string
     *
     * @param   string  $source
     *
     * @return  array
     */
    public static function tokenize($source)
    {
        return empty($source)
            ? array()
            : preg_split(
                self::get('TOKENIZATION_REGEXP'),
                $source,
                null,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );
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
        /*
        $hash = Jaws_Cache::key($source);
        $this->document = $this->app->cache->get($hash, true);
        */

        // if no cached version exists
        //if ($this->document === false || $this->document->hasIncludes() == true) {
            $tokens = Jaws_XTemplate::tokenize($source);
            $this->document = new Jaws_XTemplate_Document($tokens);
            /*
            $this->app->cache->set(
                $hash,
                $this->document,
                true
            );
            */
        //}

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
        $context = new Jaws_XTemplate_Context($assigns);

        if (!is_null($filters)) {
            $this->filters = array_merge($this->filters, $filters);
        }

        foreach ($this->filters as $filter) {
            if (is_array($filter)) {
                // Unpack a callback saved as second argument
                $context->addFilter(...$filter);
            } else {
                $context->addFilter($filter);
            }
        }

        return $this->document->render($context);
    }

}