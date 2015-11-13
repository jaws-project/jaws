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
                    array('[title]', '[description]', '[meta_keywords]', '[meta_description]'),
                    );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql   Prepared search(WHERE) SQL
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($pSql = '')
    {
        $params = array();

        $sql = '
            SELECT
               [id],[name], [title], [description], [meta_keywords], [meta_description]
            FROM [[tags]]
            ';

        if ($GLOBALS['app']->Session->Logged()) {
            $params['user'] = $GLOBALS['app']->Session->GetAttribute('user');
            $wStr = '([user]=0 OR [user]={user})';
        } else {
            $wStr = '[user]=0';
        }

        $sql .= ' WHERE ' . $wStr;
        $sql .= ' AND ' . $pSql;
        $sql .= ' ORDER BY [id] desc';

        $types = array('integer', 'text', 'text', 'text', 'text', 'text');
        $result = Jaws_DB::getInstance()->queryAll($sql, $params, $types);
        if (Jaws_Error::IsError($result)) {
            return array();
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
            $tags[$t['id']]   = $tag;
        }

        return $tags;
    }

}