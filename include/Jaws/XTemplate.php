<?php
/**
 * Jaws template engine inspired Liquid/Django template structure
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
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
     * The root path
     *
     * @var string
     */
    private $rootPath;

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
     * @param   string  $path
     *
     * @return Jaws_XTemplate
     */
    public function __construct($path = null)
    {
        $this->app = Jaws::getInstance();

        if (!empty($path)) {
            $realPath = realpath($path);
            if ($realPath === false) {
                throw new Exception("Root path could not be found: '$path'");
            }
            $path = $realPath;
        }
        $this->rootPath = $path;
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
     * @param   string  $tplPath
     * @param   string  $rootPath   The root path for templates
     *
     * @return  string  template file content
     */
    public static function readTemplateFile($tplPath, $rootPath = '')
    {
        if (empty($tplPath)) {
            throw new Exception("Empty template name");
        }

        $nameRegex = new Jaws_Regexp('/^[^.\/][a-zA-Z0-9_\.\/-]+$/');
        if (!$nameRegex->match($tplPath)) {
            throw new Exception("Illegal template name '$tplPath'");
        }

        $tplDir  = dirname($tplPath);
        $tplFile = basename($tplPath);

        $fullPath = join(DIRECTORY_SEPARATOR, array($rootPath, $tplDir, $tplFile));
        $realFullPath = realpath($fullPath);
        if ($realFullPath === false) {
            throw new Exception("File not found: $fullPath");
        }

        if (strpos($realFullPath, $rootPath) !== 0) {
            throw new Exception(
                "Illegal template full path: {$realFullPath} not under {$rootPath}"
            );
        }

        return file_get_contents($realFullPath);
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
            $this->document = new Jaws_XTemplate_Document($tokens, $this->rootPath);
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
     * @param   string  $tplPath
     * @return  Jaws_XTemplate
     */
    public function parseFile($tplPath)
    {
        return $this->parse(self::readTemplateFile($tplPath, $this->rootPath));
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