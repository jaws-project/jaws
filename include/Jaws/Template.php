<?php
/**
 * Class for manage simple templates
 *
 * @category   Layout
 * @package    Core
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
/**
 * Struct for a block
 */
class Jaws_TemplateBlock
{
    var $Name = '';
    var $Attributes = array();
    var $Content = '';
    var $RawContent = '';
    var $Parsed = '';
    var $Vars = array();
    var $InnerBlock = array();
}

/**
 * Template engine class
 */
class Jaws_Template
{
    var $Content;
    var $IdentifierRegExp;
    var $AttributesRegExp;
    var $BlockRegExp;
    var $VarsRegExp;
    var $IsBlockRegexp;
    var $MainBlock;
    var $CurrentBlock;
    var $Blocks = array();


    var $rawStore = null;
    var $loadFromTheme = null;
    var $loadRTLDirection = null;

    /**
     * Class constructor
     *
     * @access  public
     * @param   string $base_path   Template base path(gadget or plugin name or template base path)
     * @param   string $base_type   Template base type(JAWS_COMPONENT_OTHERS,
     *                                                 JAWS_COMPONENT_GADGET,
     *                                                 JAWS_COMPONENT_PLUGIN)
     * @return  void
     */
    function Jaws_Template()
    {
        $this->IdentifierRegExp = '[\.0-9A-Za-z_-]+';
        $this->AttributesRegExp = '/(\w+)((\s*=\s*".*?")|(\s*=\s*\'.*?\')|(\s*=\s*\w+)|())/s';
        $this->BlockRegExp = '@<!--\s+begin\s+('.$this->IdentifierRegExp.')\s+([^>]*)-->(.*)<!--\s+end\s+\1\s+-->@sim';
        $this->VarsRegExp = '@{\s*('.$this->IdentifierRegExp.')\s*}@sim';
        $this->IsBlockRegExp = '@##\s*('.$this->IdentifierRegExp.')\s*##@sim';
    }

    /**
     * Returns template without any proccess
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
     * @param   string  $component  Component name(owner of template file)
     * @param   bool    $return     Return content?
     * @return  mixed   Template content or void(based on $return parameter)
     */
    function Load($fname, $fpath = '', $component = '', $return = false)
    {
        $fpath   = rtrim($fpath, '/');
        $extFile = strrchr($fname, '.');
        $nmeFile = substr($fname, 0, -strlen($extFile));
        $prefix  = '';
        if ($this->loadRTLDirection ||
           (is_null($this->loadRTLDirection) && function_exists('_t') && _t('GLOBAL_LANG_DIRECTION') == 'rtl'))
        {
            $prefix = '.rtl';
        }

        $tplExists = false;
        $this->loadFromTheme = is_null($this->loadFromTheme)? (JAWS_SCRIPT == 'index') : $this->loadFromTheme;
        if ($this->loadFromTheme && (!empty($component) || empty($fpath))) {
            $theme = $GLOBALS['app']->GetTheme();
            if (!$theme['exists']) {
                Jaws_Error::Fatal('Theme '. $theme['name']. ' doesn\'t exists.');
            }

            $tplFile = $theme['path']. $component. '/'. $nmeFile. $prefix. $extFile;
            $tplExists = file_exists($tplFile);
            if (!$tplExists && !empty($prefix)) {
                $tplFile = $theme['path']. $component. '/'. $nmeFile. $extFile;
                $tplExists = file_exists($tplFile);
            }
        }

        if (!$tplExists) {
            $tplFile = $fpath. '/'. $nmeFile. $prefix. $extFile;
            $tplExists = file_exists($tplFile);
            if (!$tplExists && !empty($prefix)) {
                $tplFile = $fpath. '/'. $nmeFile. $extFile;
                $tplExists = file_exists($tplFile);
            }

            if (!$tplExists) {
                Jaws_Error::Fatal('Template '. $tplFile. ' doesn\'t exists');
            }
        }

        $content = @file_get_contents($tplFile);
        if (empty($content)) {
            Jaws_Error::Fatal('There was a problem while reading the template file: '. $tplFile);
        }

        if (preg_match_all("#<!-- INCLUDE (.*) -->#i", $content, $includes, PREG_SET_ORDER)) {
            foreach ($includes as $key => $include) {
                @list($incl_fname, $incl_fpath, $incl_component) = preg_split('#\s#', $include[1]);
                if (empty($incl_fpath)) {
                    $incl_fpath = $fpath;
                    $incl_component = $component;
                }

                $replacement = $this->Load($incl_fname, $incl_fpath, $incl_component, true);
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
     * @param   $tplString String that contains a template struct
     * @access  public
     */
    function LoadFromString($tplString)
    {
        $this->Content   = str_replace('\\', '\\\\', $tplString);
        $this->Content   = str_replace(array("-->\n", "-->\r\n"), '-->',  $this->Content);
        $this->Blocks    = $this->GetBlocks($this->Content);
        $this->MainBlock = $this->GetMainBlock();
    }

    /**
     * Returns the main block, subblocks are replaced with ##subblock##
     *
     * @access  public
     */
    function GetMainBlock()
    {
        $result = $this->Content;
        foreach ($this->Blocks as $k => $iblock) {
            $pattern = '@<!--\s+begin\s+'.$iblock->Name.'\s+([^>]*)-->(.*)<!--\s+end\s+'.$iblock->Name.'\s+-->@sim';
            $result = preg_replace($pattern, '##'.$iblock->Name.'##' , $result);
        }
        return $result;
    }

    /**
     * Return the subblocks struct for a given block
     *
     * @param   $contentString Block string
     * @access  public
     */
    function GetBlocks($contentString)
    {
        $blocks = array();
        if (preg_match_all($this->BlockRegExp, $contentString, $regs, PREG_SET_ORDER))  {
            foreach ($regs as $key => $match) {
                $wblock = new Jaws_TemplateBlock();
                $wblock->Name = $match[1];
                $attrs = array();
                preg_match_all($this->AttributesRegExp, $match[2], $attrs, PREG_SET_ORDER);
                foreach ($attrs as $attr) {
                    $attr[2] = ltrim($attr[2], " \n\r\t=");
                    $attr[2] = trim($attr[2], ($attr[2][0] == '"')? '"' : "'");
                    $wblock->Attributes[$attr[1]] = $attr[2];
                }
                $wblock->Content    = $match[3];
                $wblock->RawContent = $this->rawStore? $match[3] : null;
                $wblock->InnerBlock = $this->GetBlocks($wblock->Content);
                foreach ($wblock->InnerBlock as $k => $iblock) {
                    $pattern = '@<!--\s+begin\s+'.$iblock->Name.'\s+([^>]*)-->(.*)<!--\s+end\s+'.$iblock->Name.'\s+-->@sim';
                    $wblock->Content = preg_replace($pattern, '##'.$iblock->Name.'##' , $wblock->Content);
                }
                $wblock->Vars = $this->GetVariables($wblock->Content);
                $blocks[$wblock->Name] = $wblock;
            }
        }
        return $blocks;
    }

    /**
     * Return the attributes array of a current block
     *
     * @access  public
     */
    function GetCurrentBlockAttributes()
    {
        return $this->CurrentBlock->Attributes;
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
                $vars[$match[1]] = '';
                switch (strtolower($match[1])) {
                    case 'theme_url':
                        if (isset($GLOBALS['app'])) {
                            $theme = $GLOBALS['app']->GetTheme();
                            $vars[$match[1]] = $theme['url'];
                        }
                        break;

                    case 'base_url':
                        $vars[$match[1]] = Jaws_Utils::getBaseURL('/');
                        break;

                    case 'data_url':
                        if (isset($GLOBALS['app'])) {
                            $vars[$match[1]] = $GLOBALS['app']->getDataURL();
                        }
                        break;

                    case 'requested_url':
                        $vars[$match[1]] = Jaws_Utils::getRequestURL();
                        break;

                    case 'jaws_index':
                        if (isset($GLOBALS['app'])) {
                            $req = $GLOBALS['app']->GetMainRequest();
                            $vars[$match[1]] = $req['index']? 'jaws_index' : '';
                        }
                        break;

                    case '.dir':
                        $vars[$match[1]] = _t('GLOBAL_LANG_DIRECTION') == 'rtl'? '.rtl' : '';
                        break;

                    case '.browser':
                        if (isset($GLOBALS['app'])) {
                            $brow = $GLOBALS['app']->GetBrowserFlag();
                            $vars[$match[1]] = empty($brow)? '' : '.'.$brow;
                        }
                        break;

                    case 'base_script':
                        $vars[$match[1]] = BASE_SCRIPT;
                        break;

                    case 'requested_gadget':
                        if (isset($GLOBALS['app'])) {
                            $req = $GLOBALS['app']->GetMainRequest();
                            $vars[$match[1]] = strtolower($req['gadget']);
                        }
                        break;

                    case 'requested_action':
                        if (isset($GLOBALS['app'])) {
                            $req = $GLOBALS['app']->GetMainRequest();
                            $vars[$match[1]] = $req['action'];
                        }
                        break;

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
                $result = preg_replace($pattern, str_replace('$', '\$', $this->Blocks[$blockToReplace[1]]->Parsed) , $result);
            }
        }
        return $result;
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
     * Parse a given block, replacing its variables and parsed subblocks
     *
     * @param   $blockString Block string
     * @access  public
     */
    function ParseBlock($blockString = '')
    {
        $result = '';
        $block = &$this->GetBlockObject($blockString);
        if (isset($block->Content)) {
            $result = $block->Content;
            foreach ($block->Vars as $k => $v) {
                if (!is_array($v)) {
                    $v = str_replace('\\', '\\\\', $v);
                    $result = str_replace('{'.$k.'}', $v, $result);
                }
            }

            if (preg_match_all($this->IsBlockRegExp, $result, $regs, PREG_SET_ORDER)) {
                foreach ($regs as $blockToReplace) {
                    $search = '##' . $blockToReplace[1] . '##';
                    $replace = $block->InnerBlock[$blockToReplace[1]]->Parsed;
                    $result = str_replace($search, $replace , $result);
                }
            }
            $block->Parsed .= $result;
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
                $block = &$this->Blocks[$b];
            } else {
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
        return stristr($this->CurrentBlock->Content, '{'.$variable.'}');
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