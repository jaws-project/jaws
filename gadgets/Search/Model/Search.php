<?php
/**
 * Search Gadget
 *
 * @category    GadgetModel
 * @package     Search
 * @author      Jonathan Hernandez <ion@suavizado.com>
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2005-2024 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Search_Model_Search extends Jaws_Gadget_Model
{
    /**
     * Parse input search query to find the excluding matches, exact matches, any matches and all other words
     *
     * @access  public
     * @param   string  $query  Search query
     * @return  array   An array with the following indexes (and results): exact, least and exclude
     */
    function parseSearchQuery($phrases)
    {
        $result = array(
            'exclude' => [],
            'exact' => [],
            'least' => [],
        );

        if (!empty($phrases['all'])) {
            $query = preg_replace_callback('/"([^"]+)"|-(\w+)|\+(\w+)/u', function ($matches) use (&$result) {
                if (!empty($matches[1])) {
                    $result['exact'][] = $matches[1];
                } elseif (!empty($matches[2])) {
                    $result['exclude'][] = $matches[2];
                } elseif (!empty($matches[3])) {
                    $result['least'][] = $matches[3];
                }
                return ''; // Remove matched parts from the query
            }, $phrases['all']);

            // Extract remaining words as normal search terms
            $words = array_filter(explode(' ', trim($query)));
            $result['exact'] = array_merge($result['exact'], array_values($words));
        } else {
            // remove invalid phrase keys
            $result = array_intersect_key($phrases, $result);
        }

        $min_key_len = (int)$this->gadget->registry->fetch('min_key_len');
        foreach ($result as $part => $partPhrases) {
            if (!is_array($partPhrases)) {
                $partPhrases = [$partPhrases];
            }
            $partPhrases = array_map('Jaws_UTF8::trim', $partPhrases);
            $partPhrases = array_filter(
                $partPhrases,
                static function($val) use ($min_key_len){
                    return !empty($val) && (Jaws_UTF8::strlen($val) >= $min_key_len);
                }
            );

            $result[$part] = $partPhrases;
        }

        return array_filter($result);
    }

    /**
     * Returns the search results
     *
     * @access  public
     * @param   array   $phrases    Search phrases (exact, least, exclude)
     * @param   array   $options    Search options (gadgets, date, page, limit)
     * @return  array   Search results
     */
    function Search($phrases, $options)
    {
        $defaultOptions = array(
            'gadgets' => '',
            'date' => ['anytime'],
            'page' => 1,
            'limit' => 10,
        );
        $options = array_merge($defaultOptions, $options);

        $result = array(
            'total' => 0,
            'gadgets' => array(),
            'items' => array(),
        );

        $gadgetList = $this->GetSearchableGadgets();
        $searchableGadgets = $this->gadget->registry->fetch('searchable_gadgets');
        if ($searchableGadgets == '*') {
            $gadgets = array_keys($gadgetList);
        } else {
            $gadgets = array_filter(array_map('trim', explode(',', $searchableGadgets)));
        }
        if (!empty($options['gadgets']) && in_array($options['gadgets'], $gadgets))
        {
            $gadgets = [$options['gadgets']];
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

            if (property_exists($objHook, 'standalone')) {
                // new search method
                $gResult = $objHook->Execute($phrases);
                if (Jaws_Error::IsError($gResult) || empty($gResult)) {
                    continue;
                }

                array_push($result['items'], ...$gResult);
                $result['gadgets'][$gadget] = array(
                    'name' => $gadget,
                    'title' => $objHook->gadget->title,
                    'count' => count($gResult)
                );
                $result['total'] += $result['gadgets'][$gadget]['count'];
                continue;
            }

            $searchFields = $objHook->GetOptions();
            if (empty($searchFields)) {
                continue;
            }

            $result['gadgets'][$gadget] = array(
                'name' => $gadget,
                'title' => $objHook->gadget->title,
                'count' => 0
            );
            foreach($searchFields as $table => $fields) {
                $objORM = Jaws_ORM::getInstance();
                foreach($phrases as $part => $words) {
                    switch($part) {
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
                                    $objORM->where("lower($field)", $word, 'like')->or();
                                }
                                $objORM->closeWhere()->and();
                            }
                            break;

                        case 'least':
                            foreach($words as $word) {
                                $objORM->openWhere();
                                foreach($fields as $fidx => $field) {
                                    $objORM->where("lower($field)", $word, 'like')->or();
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
                
                array_push($result['items'], ...$gResult);
                $result['gadgets'][$gadget]['count'] += count($gResult);
                $result['total'] += $result['gadgets'][$gadget]['count'];
            }

            if ($result['gadgets'][$gadget]['count'] == 0) {
                unset($result['gadgets'][$gadget]);
            }

        }

        return $result;
    }

    /**
     * Prepares result title by joining search phrases
     *
     * @access  public
     * @param   array   $phrases    Search phrases (exact, least, exclude)
     * @return  string  Search result title
     */
    function implodeSearch($phrases = array())
    {
        $defaulPhrases = array(
            'all' => [],
            'exact' => [],
            'least' => [],
            'exclude' => [],
        );
        $phrases = array_merge($defaulPhrases, $phrases);

        $resTitle = '';
        $terms = implode(' ', $phrases['all']);
        if (!empty($terms)) {
            $resTitle .= $terms;
        }

        $terms = implode(' +', $phrases['least']);
        if (!empty($terms)) {
            $resTitle .= ' +' . $terms;
        }

        $terms = implode(' ', $phrases['exact']);
        if (!empty($terms)) {
            $resTitle .= ' "' . $terms . '"';
        }

        $terms = implode(' -', $phrases['exclude']);
        if (!empty($terms)) {
            $resTitle .= ' -' . $terms;
        }

        return Jaws_XSS::filter($resTitle);
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
        $gadgetList = $cmpModel->GetGadgetsList(null, true, true);
        $gadgets = array();
        foreach ($gadgetList as $key => $gadget) {
            if (is_file(ROOT_JAWS_PATH . 'gadgets/' . $gadget['name'] . '/Hooks/Search.php'))
                $gadgets[$key] = $gadget;
        }
        return $gadgets;
    }

}