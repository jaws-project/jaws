<?php
/**
 * Class to sort arrays in different forms
 *
 * @category   JawsType
 * @package    Core
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2005-2014 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Jaws_ArraySort
{
    /**
     * Sort and array by the second index
     *
     * @param   array   $multiArray  Input array
     * @param   string  $secondIndex Index to look
     * @param   bool   $caseSensitive if the sort should be case sensitive (optional)
     * @return  array   Sorted array
     * @access  public
     */
    static function SortBySecondIndex($multiArray, $secondIndex, $caseSensitive = true, $reverseSort = false)
    {
        while (list($firstIndex, ) = each($multiArray)) {
            if ($caseSensitive) {
                $indexMap[$firstIndex] = $multiArray[$firstIndex][$secondIndex];
            } else {
                $indexMap[$firstIndex] = strtolower($multiArray[$firstIndex][$secondIndex]);
            }
        }

        if ($reverseSort) {
            arsort($indexMap);
        } else {
            asort($indexMap);
        }

        while (list($firstIndex, ) = each($indexMap)) {
            if (is_numeric($firstIndex)) {
                $sortedArray[] = $multiArray[$firstIndex];
            } else {
                $sortedArray[$firstIndex] = $multiArray[$firstIndex];
            }
        }

        return $sortedArray;
    }

    /**
     * Sort by QuickSort technique
     * FROM: http://martinjansen.com/projects/Quicksort/
     *
     * @param   array   $array        Input array
     * @param  int     $firstElement Element to start the sort
     * @param  int     $lastElement  Element where the sort should stop
     * @return  bool    True if sort was ok, false if not.
     * @access  public
     */
    static function QuickSort(&$array, $firstElement = null, $lastElement = null)
    {
        if (!is_array($array)) {
            return false;
        }

        if (is_null($firstElement)) {
            $firstElement = 0;
        }

        if (is_null($lastElement)) {
            $lastElement = count($array) - 1;
        }

        if ($firstElement < $lastElement) {
            $middleElement = floor(($firstElement + $lastElement) / 2);
            $compareElement = $array[$middleElement];

            $fromLeft = $firstElement;
            $fromRight = $lastElement;

            while ($fromLeft <= $fromRight) {
                while($array[$fromLeft] < $compareElement) {
                    $fromLeft++;
                }

                while ($array[$fromRight] > $compareElement) {
                    $fromRight--;
                }

                if ($fromLeft <= $fromRight) {
                    Jaws_ArraySort::QuickSortChangeElements($array, $fromLeft, $fromRight);
                    $fromLeft++;
                    $fromRight--;
                }
            }
            Jaws_ArraySort::QuickSort($array, $firstElement, $fromRight);
            Jaws_ArraySort::QuickSort($array, $fromLeft, $lastElement);
        }

        return true;
    }

    /**
     * Change elements of a quick sorting
     *
     * @param   array   $array  Input array
     * @param  int     $a      First element to replace
     * @param  int     $b      Second element to replace
     * @access  public
     */
    static function QuickSortChangeElements(&$array, $a, $b)
    {
        if (isset($array[$a]) && isset($array[$b])) {
            $memory    = $array[$a];
            $array[$a] = $array[$b];
            $array[$b] = $memory;
        }
    }

    /**
     * Sort by BubbleSort
     * Based: http://www.programmershelp.co.uk/showcode.php?e=254
     *
     * @param   array  $array  Input array
     * @param  return array   Sorted array
     * @return public
     */
    static function BubbleSort($array)
    {
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            $array_count = count($array);
            for ($j = $i + 1; $j < $array_count; $j++) {
                if ($array[$i] > $array[$j]) {
                    $tmp       = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $tmp;
                }
            }
        }
        return $array;
    }

    /**
     * Sort by BubbleSort but in reverse mode
     *
     * @param   array  $array  Input array
     * @param  return array   Sorted array
     * @return public
     */
    static function BubbleSortInReverse($array, $reverse)
    {
        $count = count($array);
        for ($i = 0; $i < $count; $i++){
            $array_count = count($array);
            for ($j = $i + 1; $j < $array_count; $j++){
                if ($array[$i] < $array[$j]){
                    $tmp       = $array[$i];
                    $array[$i] = $array[$j];
                    $array[$j] = $tmp;
                }
            }
        }
        return $array;
    }

    /**
     * Sort by Insertion Sort
     * Based: php.net/sort
     *
     * @param  return array   Sorted array
     * @return public
     */
    static function InsertionSort($array)
    {
        foreach ($array as $key => $val) {
            $val_[] = $val;
            $key_[] = $key;
        }

        $count = count($val_);
        for ($i = 1; $i < $count; $i++) {
            $index = $val_[$i];
            $kindex = $key_[$i];
            $j = $i;
            while ($j > 0 && $val_[$j - 1] > $index) {
                $val_[$j] = $val_[$j - 1];
                $key_[$j] = $key_[$j - 1];
                $j = $j - 1;
            }
            $val_[$j] = $index;
            $key_[$j] = $kindex;
        }

        foreach ($val_ as $key => $val) {
            $array[$key_[$key]] = $val;
        }

        return $array;
    }

    /**
     * Sort a multidimensional array alphabetically
     * FROM: php.net/sort
     *
     * @param  return array   Sorted array
     * @return public
     */
    static function SortAlphabetically($array)
    {
        $multiarray = array();
        $array_out  = array();
        $loopvalue  = 0;

        $multicount = count($array) - 1;
        for ($i = 0; $i <= $multicount; $i++) {
            array_push($multiarray, $array[$i][2]);
        }

        reset(asort($multiarray));

        while (list($key, $val) = each($multiarray)) {
            $array_out[$loopvalue] = $array[$key];
            $loopvalue++;
        }

        return $array_out;
    }

}