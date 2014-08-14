<?php
/**
 * Blog - Search gadget hook
 *
 * @category   GadgetHook
 * @package    Blog
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2007-2014 Jaws Development Group
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
                    array('[title]', '[summary]', '[text]'),
                    );
    }

    /**
     * Returns an array with the results of a search
     *
     * @access  public
     * @param   string  $pSql   Prepared search (WHERE) SQL
     * @return  array   An array of entries that matches a certain pattern
     */
    function Execute($pSql = '')
    {
        $params = array('published' => true);

        $sql = '
            SELECT
                [id],
                [title],
                [fast_url],
                [summary],
                [text],
                [categories],
                [createtime],
                [updatetime]
            FROM [[blog]]
            WHERE
                [published] = {published}
              AND
                [createtime] <= {now}
            ';

        $sql .= ' AND ' . $pSql;
        $sql .= ' ORDER BY [createtime] desc';

        $params['now']       = Jaws_DB::getInstance()->date();
        $params['published'] = true;

        $result = Jaws_DB::getInstance()->queryAll($sql, $params);
        if (Jaws_Error::IsError($result)) {
            return array();
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
            //FIXME: Will be great if we can get the first image in "text"
            $entry['image'] = 'gadgets/Blog/Resources/images/logo.png';
            $entry['snippet'] = empty($r['summary'])? $r['text'] : $r['summary'];
            $entry['date']    = $date->ToISO($r['createtime']);

            $stamp = str_replace(array('-', ':', ' '), '', $r['createtime']);
            $entries[$stamp] = $entry;
        }

        return $entries;
    }
}
