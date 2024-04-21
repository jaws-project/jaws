<?php
/**
 * Class of template engine parser
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://shopify.github.io
 * @see         https://docs.djangoproject.com/en/3.1/topics/templates/
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_XTemplate_Parser
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    /**
     * Jaws_XTemplate object
     *
     * @var     object
     * @access  public
     */
    public $tpl = null;

    /**
     * array of Jaws_XTemplate_Document objects
     *
     * @var     object
     * @access  private
     */
    private $documents = array();

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
        'WHITESPACE_CONTROL1' => '-',

        // Whitespace control.
        'WHITESPACE_CONTROL2' => '~',

        // Whitespace control.
        'IGNORE_CONTROL' => '#',

        // Tag start.
        'TAG_OPEN' => '{%',

        // Tag end.
        'TAG_CLOSE' => '%}',

        // Variable start.
        'VARIABLE_OPEN' => '{{',

        // Variable end.
        'VARIABLE_CLOSE' => '}}',

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
     * Constructor
     *
     * @param   object  $tpl    Jaws_XTemplate object
     *
     * @return Jaws_XTemplate_Parser
     */
    public function __construct(&$tpl)
    {
        $this->tpl = $tpl;
        $this->app = Jaws::getInstance();
    }

    /**
     * Constructor
     *
     * @param   object  $tpl        Jaws_XTemplate object
     * @param   string  $tplName    File name
     * @param   string  $tplPath    File path
     */
    public function getDocument($tplName, $tplPath)
    {
        // read the source of the template and create a new sub document
        $source = $this->tpl->readTemplateFile(
            $tplName,
            $tplPath
        );

        $hash = Jaws_Cache::key($source);
        if (!array_key_exists($hash, $this->documents)) {
            // get cache if exists
            //$this->documents[$hash] = $this->app->cache->get($hash, true);
            //if ($this->documents[$hash] === false ) {
                // if no cached version exists
                $templateTokens = Jaws_XTemplate_Parser::tokenize($source);
                $this->documents[$hash] = new Jaws_XTemplate_Document($this->tpl, $templateTokens);
                $this->documents[$hash]->parse($templateTokens, true);
                // set cache
                //$this->app->cache->set($hash, $this->documents[$hash], true);
            //}
        }

        return $this->documents[$hash];
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
            case 'TAG_START':
                return self::get('TAG_OPEN') .
                    '(?!' .
                    self::get('IGNORE_CONTROL') .
                    ')';
            case 'TAG_END':
                return '(?!' .
                    self::get('IGNORE_CONTROL') .
                    ')' .
                    self::get('TAG_CLOSE');
            case 'VARIABLE_START':
                return self::get('VARIABLE_OPEN') .
                    '(?!' .
                    self::get('IGNORE_CONTROL') .
                    ')';
            case 'VARIABLE_END':
                return '(?!' .
                    self::get('IGNORE_CONTROL') .
                    ')' .
                    self::get('VARIABLE_CLOSE');
            case 'WHITESPACE_CONTROL':
                return '[' .
                    self::get('WHITESPACE_CONTROL1') .
                    '|' .
                    self::get('WHITESPACE_CONTROL2') .
                    ']';
            case 'QUOTED_FRAGMENT':
                return '(?:' .
                    self::get('QUOTED_STRING') .
                    '|(?:[^\s,\|\'"]|' .
                    self::get('QUOTED_STRING') .
                    ')+)';
            case 'BRACKETED_FRAGMENT':
                return '(?:\[[^\]]*\])';
            case 'TAG_ATTRIBUTES':
                return '/(\w+)\s*\:\s*(' .
                    self::get('QUOTED_FRAGMENT') .
                    ')/';
            case 'TOKENIZATION_REGEXP':
                return '/(' .
                self::get('TAG_START') . '.*?' .
                self::get('TAG_END') . '|' .
                self::get('VARIABLE_START') . '.*?' .
                self::get('VARIABLE_END') .
                ')/';
            default:
                return null;
        }
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
                0,
                PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );
    }

}