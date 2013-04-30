<?php
/**
 * Blog Gadget
 *
 * @category   Gadget
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Actions_Posts extends Blog_HTML
{
    /**
     * Generates XHTML template
     * 
     * @access  public
     * @param   int     $cat    
     * @return  string  XHTML template content
     */
    function ViewPage($cat = null)
    {
        $request =& Jaws_Request::getInstance();
        $page = $request->get('page', 'get');

        if (is_null($page) || $page <= 0 ) {
            $page = 1;
        }

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');

        $GLOBALS['app']->Layout->AddHeadLink($GLOBALS['app']->Map->GetURLFor('Blog', 'Atom'),
                                             'alternate',
                                             'application/atom+xml',
                                             'Atom - All');
        $GLOBALS['app']->Layout->AddHeadLink($GLOBALS['app']->Map->GetURLFor('Blog', 'RSS'),
                                             'alternate',
                                             'application/rss+xml',
                                             'RSS 2.0 - All');
        /**
         * This will be supported in next Blog version - Bookmarks for each categorie
         *
         * $categories = $model->GetCategories();
         * if (!Jaws_Error::IsError($categories)) {
         * //$GLOBALS['app']->Layout->AddHeadLink($base_url.'blog.atom', 'alternate', 'application/atom+xml', 'Atom - All');
         * foreach ($categories as $cat) {
         *                $name = $cat['name'];
         * }
         *
         * foreach ($categories as $cat) {
         *   $name = $cat['name'];
         * }
         *}
         */

        $tpl = new Jaws_Template('gadgets/Blog/templates/');
        $tpl->Load('Posts.html', true);
        $tpl->SetBlock('view');

        $model = $GLOBALS['app']->LoadGadget('Blog', 'Model');
        $entries = $model->GetEntriesAsPage($cat, $page);
        if (!Jaws_Error::IsError($entries) && count($entries) > 0) {
            $row = 0;
            $col = 0;
            $index = 0;
            $columns = (int) $this->gadget->registry->get('columns');
            $columns = ($columns <= 0)? 1 : $columns;
            foreach ($entries as $entry) {
                if ($col == 0) {
                    $tpl->SetBlock('view/entryrow');
                    $tpl->SetVariable('row', $row);
                }

                $tpl->SetBlock('view/entryrow/column');
                $tpl->SetVariable('col', $col);
                $res = $this->ShowEntry($tpl, 'view/entryrow/column', $entry);
                $tpl->ParseBlock('view/entryrow/column');

                $index++;
                $col = $index % $columns;
                if ($col == 0 || $index == count($entries)) {
                    $row++;
                    $tpl->ParseBlock('view/entryrow');
                }
            }
        }

        if ($tpl->VariableExists('navigation')) {
            $total = $model->GetNumberOfPages($cat);
            $limit = $this->gadget->registry->get('last_entries_limit');
            $tpl->SetVariable('navigation', $this->GetNumberedPageNavigation($page, $limit, $total, 'ViewPage'));
        }
        $tpl->ParseBlock('view');
        return $tpl->Get();
    }

}