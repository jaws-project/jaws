<?php
/**
 * Tags - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Tags
 */
class Tags_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets search fields of the gadget
     *
     * @access  public
     * @return  array   List of search fields
     */
    function GetOptions() {
        return array(
            'tags' => array('title', 'description', 'meta_keywords', 'meta_description'),
        );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $table  Table name
     * @param   object  $objORM Jaws_ORM instance object
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($table, &$objORM)
    {
        $objORM->table('tags');
        $objORM->select('id', 'name', 'title', 'description', 'meta_keywords', 'meta_description');
        $objORM->openWhere('user', 0);
        if ($GLOBALS['app']->Session->Logged()) {
            $objORM->or()->where('user', (int)$GLOBALS['app']->Session->GetAttribute('user'));
        }
        $objORM->closeWhere()->and()->loadWhere('search.terms');
        $result = $objORM->orderBy('id')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $tags = array();
        foreach ($result as $t) {
            $tag = array();
            $tag['title'] = $t['title'];
            $url = $this->gadget->urlMap(
                'ViewTag',
                array('tag' => $t['name'])
            );
            $tag['url']     = $url;
            $tag['image']   = 'gadgets/Tags/Resources/images/logo.png';
            $tag['snippet'] = $t['description'];
            $tag['date']    = null;
            $tags[$t['id']] = $tag;
        }

        return $tags;
    }

}