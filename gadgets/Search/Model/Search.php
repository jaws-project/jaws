<?php
/**
 * Search Gadget
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Model_Search extends Jaws_Gadget_Model
{
    /**
     * Search options
     *
     * @var     array
     * @access  private
     */
    var $_SearchTerms = array();

    /**
     * Returns the search results
     *
     * @access  public
     * @param   array   $options    Search options
     * @return  array   Search results
     */
    function Search($options)
    {
        $result = array();
        $result['_totalItems'] = 0;

        $this->_SearchTerms = $options;
        $gadgetList = $this->GetSearchableGadgets();
        $gSearchable = $this->gadget->registry->fetch('searchable_gadgets');
        $gadgets = ($gSearchable=='*')? array_keys($gadgetList) : explode(', ', $gSearchable);
        if (array_key_exists('gadgets',  $options) &&
            !empty($options['gadgets']) &&
            in_array($options['gadgets'], $gadgets))
        {
            $gadgets = array($options['gadgets']);
        }

        foreach ($gadgets as $gadget) {
            $gadget = trim($gadget);
            if ($gadget == 'Search' || empty($gadget)) {
                continue;
            }

            $objHook = Jaws_Gadget::getInstance($gadget)->hook->load('Search');
            if (Jaws_Error::IsError($objHook)) {
                continue;
            }

            $searchFields = $objHook->GetOptions();
            if (empty($searchFields)) {
                continue;
            }

            $result[$gadget] = array();
            foreach($searchFields as $table => $fields) {
                $objORM = Jaws_ORM::getInstance();
                foreach($options as $option => $words) {
                    if (empty($words) || !is_array($words)) {
                        continue;
                    }

                    $words = array_filter(array_map('trim', $words));
                    if (empty($words)) {
                        continue;
                    }
                    switch($option) {
                        case 'exclude':
                            foreach($words as $word) {
                                $objORM->openWhere();
                                foreach($fields as $fidx => $field) {
                                    $objORM->where("lower($field)", array('%$%', $word), 'not like')->and();
                                }
                                $objORM->closeWhere()->and();
                            }
                            break;

                        case 'all':
                        case 'exact':
                            foreach($words as $word) {
                                $objORM->openWhere();
                                foreach($fields as $fidx => $field) {
                                    $objORM->where("lower($field)", array('%$%', $word), 'like')->or();
                                }
                                $objORM->closeWhere()->and();
                            }
                            break;

                        case 'least':
                            foreach($words as $word) {
                                $objORM->openWhere();
                                foreach($fields as $fidx => $field) {
                                    $objORM->where("lower($field)", array('%$%', $word), 'like')->or();
                                }
                                $objORM->closeWhere()->or();
                            }
                            $objORM->and();
                            break;
                    }
                }

                $objORM->saveWhere('search.terms');
                $gResult = $objHook->Execute($table, $objORM);
                if (Jaws_Error::IsError($gResult) || empty($gResult)) {
                    continue;
                }
                $result[$gadget] = array_merge($result[$gadget], $gResult);
                $result['_totalItems'] += count($gResult);
            }

            if (empty($result[$gadget])) {
                unset($result[$gadget]);
            }

        }

        return $result;
    }

    /**
     * Prepares result title by joining search phrases
     *
     * @access  public
     * @param   array   $options    Search options
     * @return  string  Search result title
     */
    function implodeSearch($options = null)
    {
        if (is_null($options)) {
            $options = $this->_SearchTerms;
        }

        $resTitle = '';
        $terms = implode(' ', is_array($options['all'])? $options['all'] : explode(' ', $options['all']));
        if (!empty($terms)) {
            $resTitle .= $terms;
        }

        $terms = implode(' +', is_array($options['least'])? $options['least'] : explode(' ', $options['least']));
        if (!empty($terms)) {
            $resTitle .= ' +' . $terms;
        }

        $terms = is_array($options['exact'])? implode(' ', $options['exact']) : $options['exact'];
        if (!empty($terms)) {
            $resTitle .= ' "' . $terms . '"';
        }

        $terms = implode(' -', is_array($options['exclude'])? $options['exclude'] : explode(' ', $options['exclude']));
        if (!empty($terms)) {
            $resTitle .= ' -' . $terms;
        }

        return $resTitle;
    }

    /**
     * Parses a search phrase to find the excluding matches, exact matches,
     * any matches and all other words
     *
     * @access  public
     * @param   string  $phrase     Phrase to parse
     * @param   array   $searchable List of searchable gadgets
     * @return  array   An array with the following indexes (and results):
     *                     - all, exact, least and exclude
     */
    function parseSearch($options, &$searchable)
    {
        $phrase = $options['all'];
        if (!empty($phrase)) {
            $phrase.= chr(32);
        }
        $newOptions = array('all'     => '',
            'exact'   => '',
            'least'   => '',
            'exclude' => '',
            'date'    => '');
        $size = Jaws_UTF8::strlen($phrase);
        $lastKey = '';
        $tmpWord = '';
        for($i=0; $i<$size; $i++) {
            $word = Jaws_UTF8::substr($phrase, $i , 1);
            $ord  = Jaws_UTF8::ord($word);
            $tmpWord.= $word;
            switch($ord) {
                case 34: // Quotes..
                    if ($lastKey == 'exact') { //Open exact was open, we are closing it
                        $newOptions['exact'].= Jaws_UTF8::substr($tmpWord, 1, Jaws_UTF8::strlen($tmpWord) - 2);
                        $lastKey = '';
                        $tmpWord = '';
                    } else if (empty($lastKey)) {
                        $lastKey = 'exact'; //We open the exact match
                    }
                    break;
                case 43: //Plus
                    if ($lastKey != 'exact') {
                        $lastKey = 'least';
                    }
                    break;
                case 45: //Minus
                    if ($lastKey != 'exclude') {
                        $lastKey = 'exclude';
                    }
                    break;
                case 32: //Space
                    if ($lastKey != 'exact' && !empty($lastKey)) {
                        if ($lastKey != 'all') {
                            $substrCount = 1;
                            if ($tmpWord[0] == ' ') {
                                $substrCount = 2;
                            }
                            $newOptions[$lastKey].= Jaws_UTF8::substr($tmpWord, $substrCount);
                        } else {
                            $newOptions[$lastKey].= $tmpWord;
                        }
                        $lastKey = '';
                        $tmpWord = '';
                    }
                    break;
                default:
                    //Any other word opens all
                    if (empty($lastKey)) {
                        $lastKey = 'all';
                    }
                    break;
            }
        }

        $options['all'] = '';
        $min_key_len = $this->gadget->registry->fetch('min_key_len');
        foreach(array_keys($newOptions) as $option) {
            if (!empty($newOptions[$option])) {
                $options[$option] = trim(isset($options[$option])?
                    $options[$option] . ' ' . $newOptions[$option] :
                    $newOptions[$option]);
            }

            $content = (isset($options[$option])) ? $options[$option] : '';
            $content = $content;
            $content = Jaws_UTF8::strtolower(Jaws_UTF8::trim($content));
            if (Jaws_UTF8::strlen($content) >= $min_key_len) {
                $searchable = true;
            }

            $options[$option] = '';
            switch($option) {
                case 'exclude':
                case 'least':
                case 'all':
                    $options[$option] = array_filter(explode(' ', $content));
                    break;
                case 'exact':
                    $options[$option] = array($content);
                    break;
                case 'date':
                    if (in_array($content, array('past_1month', 'past_2month', 'past_3month',
                        'past_6month', 'past_1year',  'anytime'))) {
                        $options[$option] = array($content);
                    } else {
                        $options[$option] = array('anytime');
                    }
                    break;
            }

        }

        return $options;
    }

    /**
     * Gets searchable gadgets
     *
     * @access  public
     * @return  array   List of searchable gadgets
     */
    function GetSearchableGadgets()
    {
        $cmpModel = Jaws_Gadget::getInstance('Components')->model->load('Gadgets');
        $gadgetList = $cmpModel->GetGadgetsList(null, true, true, true);
        $gadgets = array();
        foreach ($gadgetList as $key => $gadget) {
            if (is_file(JAWS_PATH . 'gadgets/' . $gadget['name'] . '/Hooks/Search.php'))
                $gadgets[$key] = $gadget;
        }
        return $gadgets;
    }

    /**
     * Gets entry pager numbered links
     *
     * @access  public
     * @param   int     $page       Active page number
     * @param   int     $page_size  Number of results per page
     * @param   int     $total      Number of all results
     * @return  array   Array of page numbers
     */
    function GetEntryPagerNumbered($page, $page_size, $total)
    {
        $tail = 1;
        $paginator_size = 4;
        $pages = array();
        if ($page_size == 0) {
            return $pages;
        }

        $npages = ceil($total / $page_size);

        if ($npages < 2) {
            return $pages;
        }

        // Previous
        if ($page == 1) {
            $pages['previous'] = false;
        } else {
            $pages['previous'] = $page - 1;
        }

        if ($npages <= ($paginator_size + $tail)) {
            for ($i = 1; $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } elseif ($page < $paginator_size) {
            for ($i = 1; $i <= $paginator_size; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        } elseif ($page > ($npages - $paginator_size + $tail)) {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

            for ($i = $npages - $paginator_size + ($tail - 1); $i <= $npages; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }
        } else {
            for ($i = 1; $i <= $tail; $i++) {
                $pages[$i] = $i;
            }

            $pages['separator1'] = true;

            $start = floor(($paginator_size - $tail)/2);
            $end = ($paginator_size - $tail) - $start;
            for ($i = $page - $start; $i < $page + $end; $i++) {
                if ($i == $page) {
                    $pages['current'] = $i;
                } else {
                    $pages[$i] = $i;
                }
            }

            $pages['separator2'] = true;

            for ($i = $npages - ($tail - 1); $i <= $npages; $i++) {
                $pages[$i] = $i;
            }

        }

        // Next
        if ($page == $npages) {
            $pages['next'] = false;
        } else {
            $pages['next'] = $page + 1;
        }

        $pages['total'] = $total;

        return $pages;
    }
}