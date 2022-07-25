<?php
/**
 * Class of template engine parser
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 * @see         https://shopify.github.io
 * @see         https://docs.djangoproject.com/en/3.1/topics/templates/
 * @see         https://github.com/harrydeluxe/php-liquid
 */
class Jaws_XTemplate_Parser
{
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
     * Constructor
     *
     * @param   string  $source
     *
     * @return Jaws_XTemplate_Parser
     */
    public function __construct($source)
    {
        /*
        $hash = Jaws_Cache::key($source);
        $this->document = $this->app->cache->get($hash, true);
        */

        // if no cached version exists
        //if ($this->document === false || $this->document->hasIncludes() == true) {
            $tokens = self::tokenize($source);
            $this->document = new Jaws_XTemplate_Document($tokens);
            /*
            $this->app->cache->set(
                $hash,
                $this->document,
                true
            );
            */
        //}

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

    /**
     * Renders the current template
     *
     * @param   array   $assigns    an array of values for the template
     * @param   array   $filters    additional filters for the template
     *
     * @return string
     */
    public function render($context)
    {

        return $this->document->render($context);
    }

}