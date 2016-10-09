<?php
/**
 * Blog - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Hooks_Search extends Jaws_Gadget_Hook
{
    /**
     * Gets the gadget's search fields
     *
     * @access  public
     * @return  array   search fields array
     */
    function GetOptions() {
        return array(
            'blog' => array('title', 'summary', 'text'),
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
        $objORM->table('blog');
        $objORM->select(
            'id', 'title', 'fast_url', 'summary', 'text', 'categories', 'image', 'createtime', 'updatetime'
        );
        $result = $objORM->where('published', true)
            ->and()
            ->where('createtime', Jaws_DB::getInstance()->date(), '<=')
            ->and()
            ->loadWhere('search.terms')
            ->orderBy('createtime desc')
            ->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return false;
        }

        $date = Jaws_Date::getInstance();
        $entries = array();
        foreach ($result as $key=>$r) {
            $permission = true;
            foreach (explode(",", $r['categories']) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    $permission = false;
                }
            }
            if(!$permission) {
                continue;
            }

            $entry = array();
            $entry['title'] = $r['title'];
            if (empty($r['fast_url'])) {
                $url = $this->gadget->urlMap('SingleView', array('id' => $r['id']));
            } else {
                $url = $this->gadget->urlMap('SingleView', array('id' => $r['fast_url']));
            }
            $entry['url'] = $url;
            if (empty($r['image'])) {
                $entry['image'] = 'gadgets/Blog/Resources/images/logo.png';
            } else {
                $entry['image'] = $GLOBALS['app']->getDataURL(). 'blog/images/'. $r['image'];
            }

            $entry['snippet'] = empty($r['summary'])? $r['text'] : $r['summary'];
            $entry['date']    = $date->ToISO($r['createtime']);

            $entries[] = $entry;
        }

        return $entries;
    }
}
