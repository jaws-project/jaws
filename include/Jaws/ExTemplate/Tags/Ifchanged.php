<?php
/**
 * Class for tag Ifchanged
 * Quickly create a table from a collection
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ExTemplate_Tags_Ifchanged extends Jaws_ExTemplate_TagSegmental
{
    /**
     * The last value
     *
     * @var string
     */
    private $lastValue = '';

    /**
     * Constructor
     *
     * @param string $markup
     * @param array $tokens
     * @param   string  $rootPath
     *
     * @throws Exception
     */
    public function __construct($markup, array &$tokens, $rootPath = null)
    {
        parent::__construct($markup, $tokens, $rootPath);
    }

    /**
     * Renders the block
     *
     * @param Context $context
     *
     * @return string
     */
    public function render($context)
    {
        $output = parent::render($context);

        if ($this->lastValue == $output) {
            return '';
        }
        $this->lastValue = $output;
        return $this->lastValue;
    }

}