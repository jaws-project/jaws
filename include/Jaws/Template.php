<?php
/**
 * Class for manage simple templates
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Structure for a block
 */
class Jaws_TemplateBlock
{
    var $Name = '';
    var $Content = '';
    var $RawContent = '';
    var $Parsed = '';
    var $Vars = array();
    var $InnerBlock = array();
    var $Path = '';
}

/**
 * Template engine class
 */
class Jaws_Template
{
    /**
     * Jaws app object
     *
     * @var     object
     * @access  public
     */
    public $app = null;

    var $Content;
    var $IdentifierRegExp;
    var $BlockRegExp;
    var $NewBlockRegExp;
    var $VarsRegExp;
    var $IsBlockRegExp;
    var $MainBlock;
    var $CurrentBlock;
    var $Blocks = array();
    var $tplString = '';
    var $globalVariables = array();

    var $theme = null;
    var $layout = '';
    var $rawStore = null;
    var $loadFromTheme = false;
    var $loadRTLDirection = null;

    /**
     * Class constructor
     *
     * @access  public
     * @param   bool    $loadFromTheme          Try to load template from theme
     * @param   bool    $loadGlobalVariables    Fetch and set global variables 
     * @return  void
     */
    function __construct($loadFromTheme = false, $loadGlobalVariables = true)
    {
        $this->app = Jaws::getInstance();

        $this->IdentifierRegExp = '[\.[:digit:][:lower:]_\-]+';
        $this->BlockRegExp =
            '@<!--\s+begin\s+('.
            $this->IdentifierRegExp.
            ')\s+([^>]*)-->(.+?)<!--\s+end\s+\1\s+-->@sim';
        $this->VarsRegExp = '@{{\s*('.$this->IdentifierRegExp.')\s*}}@sim';
        $this->IsBlockRegExp = '@##\s*('.$this->IdentifierRegExp.')\s*##@sim';
        $namexp = '[[:digit:][:lower:]_]+';
        $this->NewBlockRegExp = '@<!--\s+begin\s+('.$namexp.')\s+'.
            '(?:if\((!)?('.$namexp.')\)\s+|)'.
            '(?:loop\(('.$namexp.')\)\s+|)'.
            '-->(.*)<!--\s+end\s+\1\s+-->@sim';

        // set defualt values
        $this->globalVariables['theme_url']   = '';
        $this->globalVariables['data_url']    = '';
        $this->globalVariables['.browser']    = '';
        $this->globalVariables['main_index']  = '';
        $this->globalVariables['main_gadget'] = '';
        $this->globalVariables['main_action'] = '';
        $this->globalVariables['main_layout'] = '';

        $this->globalVariables['dir'] = Jaws::t('LANG_DIRECTION') == 'rtl'? 'rtl' : 'ltr';
        $this->globalVariables['.dir'] = Jaws::t('LANG_DIRECTION') == 'rtl'? '.rtl' : '';
        $this->globalVariables['base_url']      = Jaws_Utils::getBaseURL('/');
        $this->globalVariables['requested_url'] = Jaws_Utils::getRequestURL();
        $this->globalVariables['base_script']   = BASE_SCRIPT;

        if ($loadGlobalVariables) {
            $this->loadFromTheme = $loadFromTheme;
            $this->theme  = $this->app->GetTheme();
            $layout = $this->app->layout->GetLayoutName();
            $this->layout = @is_dir($this->theme['path']. '/'. $layout)? $layout : '';
            $browser = $this->app->GetBrowserFlag();

            $this->globalVariables['theme_url']   = $this->theme['url'];
            $this->globalVariables['data_url']    = $this->app->getDataURL();
            $this->globalVariables['.browser']    = empty($browser)? '' : ".$browser";
            $this->globalVariables['main_index']  = $this->app->mainIndex? 'index' : '';
            $this->globalVariables['main_gadget'] = strtolower($this->app->mainRequest['gadget']);
            $this->globalVariables['main_action'] = strtolower($this->app->mainRequest['action']);
            $this->globalVariables['main_layout'] = strtolower(str_replace('.', '_', $this->layout));
        } else {
            $this->loadFromTheme = false;
        }

    }

    /**
     * Returns template without any process
     *
     * @access  public
     */
    function GetContent()
    {
        return $this->Content;
    }

    /**
     * Loads a template from a file
     *
     * @access  public
     * @param   string  $fname      File name
     * @param   string  $fpath      File path
     * @param   bool    $return     Return content?
     * @return  mixed   Template content or void(based on $return parameter)
     */
    function Load($fname, $fpath = '', $return = false)
    {
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
           (is_null($this->loadRTLDirection) && function_exists('_t') && Jaws::t('LANG_DIRECTION') == 'rtl'))
        {
            $prefix = '.rtl';
        }

        $tplFile = $filePath. '/'. $fileName. $prefix. $fileExtn;
        $tplExists = file_exists($tplFile);
        if (!$tplExists && !empty($prefix)) {
            $tplFile = $filePath. '/'. $fileName. $fileExtn;
            $tplExists = file_exists($tplFile);
        }

        if (!$tplExists) {
            Jaws_Error::Fatal('Template '. $tplFile. ' doesn\'t exists');
        }

        $content = @file_get_contents($tplFile);
        if (empty($content)) {
            Jaws_Error::Fatal('There was a problem while reading the template file: '. $tplFile);
        }

        if (preg_match_all("#<!-- INCLUDE (.*) -->#i", $content, $includes, PREG_SET_ORDER)) {
            foreach ($includes as $key => $include) {
                @list($incl_fname, $incl_fpath) = preg_split('#\s#', $include[1]);
                if (empty($incl_fpath)) {
                    $incl_fpath = $fpath;
                }

                $replacement = $this->Load($incl_fname, $incl_fpath, true);
                $content = str_replace($include[0], $replacement, $content);
            }
        }

        if ($return) {
            return $content;
        }

        $this->loadFromString($content);
    }

    /**
     * Loads a template from a string
     *
     * @param   $tplString String that contains a template structure
     * @access  public
     */
    function loadFromString($tplString)
    {
        $this->tplString = preg_replace('@\r?\n\s*<!--@sim', '<!--', $tplString);
        $this->Content   = $this->tplString;
        $this->Blocks    = $this->GetBlocks($this->Content);
        $this->MainBlock = $this->GetMainBlock();
    }

    /**
     * Returns the main block, sub-blocks are replaced with ##subblock##
     *
     * @access  public
     */
    function GetMainBlock()
    {
        $result = $this->Content;
        foreach ($this->Blocks as $k => $iblock) {
            $pattern = '@<!--\s+begin\s+'.$iblock->Name.'\s+([^>]*)-->(.+?)<!--\s+end\s+'.$iblock->Name.'\s+-->@sim';
            $result = preg_replace($pattern, '##'.$iblock->Name.'##' , $result);
        }
        return $result;
    }

    /**
     * Return the sub-blocks structure for a given block
     *
     * @param   $content    Block string
     * @param   $path       Block path
     * @access  public
     */
    function GetBlocks($content, $path = '')
    {
        $blocks = array();
        if (preg_match_all($this->BlockRegExp, $content, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $key => $match) {
                $wblock = new Jaws_TemplateBlock();
                $wblock->Name = $match[1];
                $wblock->Path = (empty($path)? '' : "$path/") . $match[1];
                $wblock->Content    = $match[3];
                $wblock->RawContent = $this->rawStore? $match[3] : null;
                $wblock->InnerBlock = $this->GetBlocks($wblock->Content, $wblock->Path);
                foreach ($wblock->InnerBlock as $k => $iblock) {
                    $pattern = 
                        '@<!--\s+begin\s+' .
                        $iblock->Name .
                        '\s+([^>]*)-->(.+?)<!--\s+end\s+' .
                        $iblock->Name .
                        '\s+-->@sim';
                    $wblock->Content = preg_replace($pattern, '##'.$iblock->Name.'##' , $wblock->Content);
                }
                $wblock->Vars = $this->GetVariables($wblock->Content);
                $blocks[$wblock->Name] = $wblock;
            }
        }
        return $blocks;
    }

    /**
     * Fetch parsed template
     *
     * @param   $vars       Variables
     * @param   $content    Template string
     * @param   $root       Root block of template?
     * @access  public
     */
    function fetch($vars = array(), $content = '', $root = true)
    {
        if (empty($content)) {
            $vars = $vars + $this->globalVariables;
            $content = $this->tplString;
        }

        if (preg_match_all($this->NewBlockRegExp, $content, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $match) {
                $exchange = '';
                $blockName = $match[1];

                if (!empty($match[3]) &&
                   ((bool)$match[2]? (bool)$vars[$match[3]] : !(bool)$vars[$match[3]]))
                {
                    // condition for parsing block
                    $exchange = '';
                } elseif ((!empty($match[4]) && !empty($vars[$match[4]]))) {
                    // passed array for loop block must be an array
                    if (!isset($vars[$blockName]) || !is_array($vars[$blockName])) {
                        $vars[$blockName] = array();
                    }
                    // parse loop block
                    if (!Jaws_Error::IsError($vars[$match[4]])) {
                        foreach ($vars[$match[4]] as $loopVars) {
                            $exchange.= $this->fetch($loopVars + $vars[$blockName], $match[5], false);
                        }
                    }
                } elseif (isset($vars[$blockName])) {
                    if (is_array($vars[$blockName])) {
                        // parse simple block
                        $exchange = $this->fetch($vars[$blockName], $match[5], false);
                    } else {
                        // replace block with given variable
                        $exchange = (string)$vars[$blockName];
                    }
                }
                $content = str_replace($match[0], $exchange, $content);
            }
        }

        if ($root) {
            // Merge global variables and input variables together
            $vars = $vars + $this->globalVariables;
        }
        // set variables
        foreach($vars as $key => $value) {
            if (!is_array($value)) {
                $content = str_replace('{{'.$key.'}}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Return the variables array of a given block
     *
     * @param   $blockContent Block string
     * @access  public
     */
    function GetVariables($blockContent)
    {
        $vars = array();
        if (preg_match_all($this->VarsRegExp, $blockContent, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $k => $match) {
                switch (strtolower($match[1])) {
                    case 'dir':
                    case '.dir':
                    case '.browser':
                    case 'theme_url':
                    case 'base_url':
                    case 'data_url':
                    case 'main_index':
                    case 'base_script':
                    case 'main_gadget':
                    case 'main_action':
                    case 'main_layout':
                    case 'requested_url':
                        $vars[$match[1]] = $this->globalVariables[$match[1]];
                        break;

                    default:
                        $vars[$match[1]] = '';
                }
            }
        }

        return $vars;
    }

    /**
     * Returns the processed template(parsed blocks)
     *
     * @access  public
     */
    function Get()
    {
        $result = str_replace('\\\\', '\\', $this->MainBlock);
        if (preg_match_all($this->IsBlockRegExp, $result, $regs, PREG_SET_ORDER)) {
            foreach ($regs as $blockToReplace) {
                $pattern = '@##\s*(' . $blockToReplace[1] . ')\s*##@sim';
                $result = preg_replace(
                    $pattern,
                    str_replace('$', '\$', $this->Blocks[$blockToReplace[1]]->Parsed),
                    $result
                );
            }
        }

        return $result;
    }

    /**
     * Returns the path of the current block
     *
     * @access  public
     * @return  string  Path
     */
    function GetCurrentBlockPath()
    {
        return is_null($this->CurrentBlock)? '' : $this->CurrentBlock->Path;
    }

    /**
     * Returns the content of the current block
     *
     * @access  public
     * @return  string  Content
     */
    function GetCurrentBlockContent()
    {
        return is_null($this->CurrentBlock)? '' : $this->CurrentBlock->Content;
    }

    /**
     * Returns the raw content of a block
     *
     * @access  public
     * @param   string $pathString Block path if empty use current block
     * @return  string  Content
     */
    function GetRawBlockContent($pathString = '', $block_include = true)
    {
        if (empty($pathString)) {
            if (is_null($this->CurrentBlock)) {
                return '';
            } elseif ($block_include) {
                return "<!-- BEGIN {$this->CurrentBlock->Name} -->".
                       $this->CurrentBlock->RawContent.
                       "<!-- END {$this->CurrentBlock->Name} -->";
            } else {
                return $this->CurrentBlock->RawContent;
            }
        } else {
            $block =& $this->GetBlockObject($pathString);
            if (is_null($block)) {
                return '';
            } elseif ($block_include) {
                return "<!-- BEGIN {$block->Name} -->".
                       $block->RawContent.
                       "<!-- END {$block->Name} -->";
            } else {
                return $block->RawContent;
            }
        }
    }

    /**
     * Set the content of the current block
     *
     * @param   $content    Block content
     * @access  public
     */
    function SetCurrentBlockContent($content)
    {
        $this->CurrentBlock->Content = $content;
    }

    /**
     * Parse a given block, replacing its variables and parsed sub-blocks
     *
     * @access  public
     * @param   $blockString    Block string
     * @param   $ignore         Ignore block parsed content
     * @return  string          Parsed content
     */
    function ParseBlock($blockString = '', $ignore = false)
    {
        $result = '';
        $blockString = trim($blockString, '/');
        $block = &$this->GetBlockObject($blockString);
        if (isset($block->Content)) {
            $result = $block->Content;
            foreach ($block->Vars as $k => $v) {
                if (!is_array($v)) {
                    $v = str_replace('\\', '\\\\', (string)$v);
                    $result = str_replace('{{'.$k.'}}', $v, (string)$result);
                }
            }

            if (preg_match_all($this->IsBlockRegExp, $result, $regs, PREG_SET_ORDER)) {
                foreach ($regs as $blockToReplace) {
                    $search = '##' . $blockToReplace[1] . '##';
                    $replace = $block->InnerBlock[$blockToReplace[1]]->Parsed;
                    $result = str_replace($search, $replace , $result);
                }
            }
            $block->Parsed .= ($ignore? '' : $result);
        }

        $blockString = substr($blockString, 0, strrpos($blockString, '/'));
        $this->SetBlock($blockString, false);

        return $result;
    }

    /**
     * Get a template variable in current block
     *
     * @param   string $key Variable name
     * @access  public
     */
    function GetVariable($key)
    {
        return $this->CurrentBlock->Vars[$key];
    }

    /**
     * Sets a template variable in current block
     *
     * @param   string $key Variable name
     * @param   string $value Variable value
     * @access  public
     */
    function SetVariable($key, $value)
    {
        $this->CurrentBlock->Vars[$key] = $value;
    }

    /**
     * Returns the block object of a given path
     *
     * @param   string $pathString Block path
     * @access  public
     */
    function &GetBlockObject($pathString)
    {
        if ($pathString == '') {
            return $this->CurrentBlock;
        }

        $blockDeep = 1;
        $path = explode('/', $pathString);
        foreach ($path as $b) {
            if ($blockDeep === 1) {
                if (!isset($this->Blocks[$b])) {
                    $this->Blocks[$b] = new Jaws_TemplateBlock();
                }
                $block = &$this->Blocks[$b];
            } else {
                if (!isset($block->InnerBlock[$b])) {
                    $block->InnerBlock[$b] = new Jaws_TemplateBlock();
                }
                $block = &$block->InnerBlock[$b];
            }
            $blockDeep++;
        }

        return $block;
    }

    /**
     * Changes the current block to the given path
     *
     * @param   string $pathString Block path
     * @access  public
     */
    function SetBlock($pathString, $init = true)
    {
        $pathString = trim($pathString, '/');
        $this->CurrentBlock = &$this->GetBlockObject($pathString);
        if ($init === true) {
            $this->InitializeSubBlock($this->CurrentBlock);
        }
    }

    /**
     * Initialize subblocks of a given block object
     *
     * @param   object $block Block object
     * @access  public
     */
    function InitializeSubBlock(&$block)
    {
        if (
            is_object($block) && isset($block->Content) &&
            preg_match_all($this->IsBlockRegExp, $block->Content, $regs, PREG_SET_ORDER)
        ) {
            foreach ($regs as $subBlock) {
                $block->InnerBlock[$subBlock[1]]->Parsed = '';
                $this->InitializeSubBlock($block->InnerBlock[$subBlock[1]]);
            }
        }
    }

    /**
     * Set variables from a given associative array
     *
     * @param   array $variablesArray Associative array to replace
     * @access  public
     */
    function SetVariablesArray($variablesArray)
    {
        foreach ($variablesArray as $key => $value) {
            $this->CurrentBlock->Vars[$key] = $value;
        }
    }

    /**
     * Resets a variable in a previous block
     *
     * @access  public
     * @param   string  $variable  Variable's name
     * @param   string  $value     Variable's value
     * @param   string  $block     Block's name
     */
    function ResetVariable($variable, $value, $block)
    {
        if (isset($this->Blocks[$block])) {
            $this->Blocks[$block]->Vars[$variable] = $value;
        }
    }

    /**
     * Check if a given block exists
     *
     * @param   string $pathString Block path
     * @return  bool    True if block is found, otherwise false.
     * @access  public
     */
    function BlockExists($pathString)
    {
        $blockDeep = 1;
        $consPath = '';
        foreach (explode('/', $pathString) as $b) {
            if ($blockDeep == 1) {
                if (!isset($this->Blocks[$b])) {
                    break;
                }

                $block = &$this->Blocks[$b];
                $consPath = $b;
            } else {
                if (!isset($block->InnerBlock[$b])) {
                    break;
                }

                $block = &$block->InnerBlock[$b];
                $consPath .= '/'.$b;
            }
            $blockDeep++;
        }
        return($pathString == $consPath);
    }

    /**
     * Check if a variable exists in curren block
     *
     * @param   string $variable Variable name
     * @return True if variable found, otherwise false
     * @access  public
     */
    function VariableExists($variable)
    {
        return stristr($this->CurrentBlock->Content, '{{'.$variable.'}}');
    }

    /**
     * Resets the values and updates
     *
     * @access  public
     */
    function ResetValues()
    {
        $this->rawStore = false;
        $this->loadFromTheme = null;
        $this->loadRTLDirection = null;
        $this->Content = '';
        $this->MainBlock = '';
        $this->Blocks = array();
        $this->CurrentBlock = '';
    }

}