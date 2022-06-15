<?php
/**
 * Base class for tags that make logical decisions
 *
 * @category    Template
 * @package     Core
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2020-2022 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_XTemplate_TagConditional extends Jaws_XTemplate_TagSegmental
{
    /**
     * The current left variable to compare
     *
     * @var string
     */
    public $left;

    /**
     * The current right variable to compare
     *
     * @var string
     */
    public $right;

    /**
     * Returns a string value of an array for comparisons
     *
     * @param   mixed   $value
     *
     * @throws  Exception
     * @return  string
     */
    private function stringValue($value)
    {
        // Objects should have a __toString method to get a value to compare to
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                $value = (string) $value;
            } else {
                $class = get_class($value);
                throw new Exception("Value of type $class has no `__toString` methods");
            }
        }

        // Arrays simply return true
        if (is_array($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Check to see if to variables are equal in a given context
     *
     * @param string $left
     * @param string $right
     * @param Context $context
     *
     * @return bool
     */
    protected function equalVariables($left, $right, $context)
    {
        $left = $this->stringValue($context->get($left));
        $right = $this->stringValue($context->get($right));

        return ($left == $right);
    }

    /**
     * Interpret a comparison
     *
     * @param   string  $left
     * @param   string  $right
     * @param   string  $op
     * @param   object  $context
     *
     * @throws  Exception
     * @return  bool
     */
    protected function interpretCondition($left, $right, $op, $context)
    {
        $left = new Jaws_XTemplate_Variable($left);
        $left = $left->render($context);
        if (is_null($op)) {
            return $left;
        }

        $right = new Jaws_XTemplate_Variable($right);
        $right = $right->render($context);

        // special rules for null values
        if (is_null($left) || is_null($right)) {
            // null == null returns true
            if ($op == '==' && is_null($left) && is_null($right)) {
                return true;
            }

            // null != anything other than null return true
            if ($op == '!=' && (!is_null($left) || !is_null($right))) {
                return true;
            }

            // everything else, return false;
            return false;
        }

        // regular rules
        switch ($op) {
            case '==':
                return ($left == $right);

            case '!=':
                return ($left != $right);

            case '>':
                return ($left > $right);

            case '<':
                return ($left < $right);

            case '>=':
                return ($left >= $right);

            case '<=':
                return ($left <= $right);

            case 'contains':
                return is_array($left) ? in_array($right, $left) : (strpos($left, $right) !== false);

            default:
                throw new Exception("Error in tag '" . $this->name() . "' - Unknown operator $op");
        }
    }

}