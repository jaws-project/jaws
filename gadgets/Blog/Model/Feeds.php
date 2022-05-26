<?php
/**
 * Blog Gadget
 *
 * @category   GadgetModel
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2021 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Blog_Model_Feeds extends Jaws_Gadget_Model
{
    /**
     * Has the Atom pointer to create the RSS/XML files
     *
     * @var     object  $_Atom  AtomFeed object
     * @access  private
     */
    var $_Atom = null;

    /**
     * Create ATOM struct
     *
     * @access  public
     * @param   string  $feed_type  OPTIONAL feed type
     * @return  mixed  Can return the Atom Object or Jaws_Error on error
     */
    function GetAtomStruct($feed_type = 'atom')
    {
        if (isset($this->_Atom) && is_array($this->_Atom->Entries) && count($this->_Atom->Entries) > 0) {
            return $this->_Atom;
        }

        $this->_Atom = new Jaws_AtomFeed();
        $now = Jaws_DB::getInstance()->date();
        $limit = $this->gadget->registry->fetch('xml_limit');

        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'user_id:integer', 'username', 'email', 'nickname', 'title', 'subtitle', 'summary',
            'text', 'fast_url', 'blog.publishtime', 'blog.updatetime', 'clicks:integer',
            'allow_comments:boolean', 'published:boolean', 'categories'
        )->join('users', 'blog.user_id', 'users.id');
        $blogTable->where('blog.published', true)->and()->where('blog.publishtime', $now, '<=');
        $result = $blogTable->orderBy('blog.publishtime desc')->limit($limit)->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($this::t('ERROR_GETTING_ATOMSTRUCT'));
        }

        // Check dynamic ACL
        foreach ($result as $key => $entry) {
            foreach (explode(",", $entry['categories']) as $cat) {
                if (!$this->gadget->GetPermission('CategoryAccess', $cat)) {
                    unset($result[$key]);
                }
            }
        }

        $siteURL = $this->app->getSiteURL('/');
        $url = $this->gadget->urlMap(
            $feed_type == 'atom'? 'Atom' : 'RSS',
            array(),
            array('absolute', true)
        );

        $this->_Atom->SetTitle($this->gadget->registry->fetch('site_name', 'Settings'));
        $this->_Atom->SetLink($url);
        $this->_Atom->SetId($siteURL);
        $this->_Atom->SetTagLine($this->gadget->registry->fetch('site_slogan', 'Settings'));
        $this->_Atom->SetAuthor($this->gadget->registry->fetch('site_author', 'Settings'),
            $this->app->getSiteURL(),
            $this->gadget->registry->fetch('gate_email', 'Settings'));
        $this->_Atom->SetGenerator('JAWS '.$this->app->registry->fetch('version'));
        $this->_Atom->SetCopyright($this->gadget->registry->fetch('site_copyright', 'Settings'));

        $objDate = Jaws_Date::getInstance();
        foreach ($result as $r) {
            $entry = new AtomEntry();
            $entry->SetTitle($r['title']);
            $post_id = empty($r['fast_url']) ? $r['id'] : $r['fast_url'];
            $url = $this->gadget->urlMap('SingleView', array('id' => $post_id), array('absolute', true));
            $entry->SetLink($url);
            $entry->SetId($url);

            $summary = $r['summary'];
            $text    = $r['text'];

            // for compatibility with old versions
            $more_pos = Jaws_UTF8::strpos($text, '[more]');
            if ($more_pos !== false) {
                $summary = Jaws_UTF8::substr($text, 0, $more_pos);
                $text    = Jaws_UTF8::str_replace('[more]', '', $text);

                // Update this entry to split summary and body of post
                $model = $this->gadget->model->load('Posts');
                $model->SplitEntry($r['id'], $summary, $text);
            }

            $summary = empty($summary)? $text : $summary;
            $summary = $this->gadget->plugin->parse($summary);
            $text    = $this->gadget->plugin->parse($text);

            $entry->SetSummary($summary, 'html');
            //$entry->SetContent($text, 'html');
            $email = $r['email'];
            $entry->SetAuthor($r['nickname'], $this->_Atom->Link->HRef, $email);
            $entry->SetPublished($objDate->ToISO($r['publishtime']));
            $entry->SetUpdated($objDate->ToISO($r['updatetime']));

            $model = $this->gadget->model->load('Categories');
            $cats = $model->GetCategoriesInEntry($r['id']);
            foreach ($cats as $c) {
                $schema = $this->gadget->urlMap('ShowCategory', array('id' => $c['id']), array('absolute', true));
                $entry->AddCategory($c['id'], $c['name'], $schema );
            }
            $this->_Atom->AddEntry($entry);

            if (!isset($last_modified) || ($last_modified < $r['updatetime'])) {
                $last_modified = $r['updatetime'];
            }
        }

        if (isset($last_modified)) {
            $this->_Atom->SetUpdated($objDate->ToISO($last_modified));
        } else {
            $this->_Atom->SetUpdated($objDate->ToISO(date('Y-m-d H:i:s')));
        }
        return $this->_Atom;
    }

    /**
     * Create ATOM of the blog
     *
     * @access  public
     * @param   bool    $write Flag that determinates if Atom file should be written to disk
     * @return  mixed   XML string or Jaws_Error on error
     */
    function MakeAtom($write = false)
    {
        $atom = $this->GetAtomStruct('atom');
        if (Jaws_Error::IsError($atom)) {
            return $atom;
        }

        if ($write) {
            if (!Jaws_FileManagement_File::is_writable(ROOT_DATA_PATH . 'xml')) {
                return new Jaws_Error($this::t('ERROR_WRITING_ATOMFILE'));
            }

            $atom->SetLink($this->app->getDataURL('xml/blog.atom', false));
            ///FIXME we need to do more error checking over here
            Jaws_FileManagement_File::file_put_contents(ROOT_DATA_PATH . 'xml/blog.atom', $atom->GetXML());
            Jaws_FileManagement_File::chmod(ROOT_DATA_PATH . 'xml/blog.atom');
        }

        return $atom->GetXML();
    }

    /**
     * Create RSS of the blog
     *
     * @access  public
     * @param   bool    $write  Flag that determinates if it should returns the RSS
     * @return  mixed   Returns the RSS(string) if it was required, or Jaws_Error on error
     */
    function MakeRSS($write = false)
    {
        $atom = $this->GetAtomStruct('rss');
        if (Jaws_Error::IsError($atom)) {
            return $atom;
        }

        if ($write) {
            if (!Jaws_FileManagement_File::is_writable(ROOT_DATA_PATH . 'xml')) {
                return new Jaws_Error($this::t('ERROR_WRITING_RSSFILE'));
            }

            $atom->SetLink($this->app->getDataURL('xml/blog.rss', false));
            ///FIXME we need to do more error checking over here
            Jaws_FileManagement_File::file_put_contents(ROOT_DATA_PATH . 'xml/blog.rss', $atom->ToRSS2());
            Jaws_FileManagement_File::chmod(ROOT_DATA_PATH . 'xml/blog.rss');
        }

        return $atom->ToRSS2();
    }

    /**
     * Create ATOM struct of a given category
     *
     * @access  public
     * @param   int     $category   Category ID
     * @param   string  $feed_type  OPTIONAL feed type
     * @return  mixed   Can return the Atom Object or Jaws_Error on error
     */
    function GetCategoryAtomStruct($category, $feed_type = 'atom')
    {
        $model = $this->gadget->model->load('Categories');
        $catInfo = $model->GetCategory($category);
        if (Jaws_Error::IsError($catInfo)) {
            return new Jaws_Error($this::t('ERROR_GETTING_CATEGORIES_ATOMSTRUCT'));
        }

        $now = Jaws_DB::getInstance()->date();
        $blogTable = Jaws_ORM::getInstance()->table('blog');
        $blogTable->select(
            'blog.id:integer', 'user_id:integer', 'blog_entrycat.category_id:integer', 'username', 'email',
            'nickname', 'title', 'subtitle', 'fast_url', 'summary', 'text',  'blog.publishtime', 'blog.updatetime',
            'clicks:integer', 'allow_comments:boolean', 'published:boolean'
        )->join('users', 'blog.user_id', 'users.id')->join('blog_entrycat', 'blog.id', 'blog_entrycat.entry_id');
        $blogTable->where('published', true)->and()->where('blog.publishtime', $now, '<=');
        $blogTable->and()->where('blog_entrycat.category_id', $catInfo['id']);
        $result = $blogTable->orderby('blog.publishtime desc')->fetchAll();
        if (Jaws_Error::IsError($result)) {
            return new Jaws_Error($this::t('ERROR_GETTING_CATEGORIES_ATOMSTRUCT'));
        }

        $cid = empty($catInfo['fast_url']) ? $catInfo['id'] : Jaws_XSS::filter($catInfo['fast_url']);

        $categoryAtom = new Jaws_AtomFeed();
        $siteURL = $this->app->getSiteURL('/');
        $url = $this->gadget->urlMap(
            $feed_type == 'atom'? 'ShowAtomCategory' : 'ShowRSSCategory',
            array('id' => $cid),
            array('absolute', true)
        );

        $categoryAtom->SetTitle($this->gadget->registry->fetch('site_name', 'Settings'));
        $categoryAtom->SetLink($url);
        $categoryAtom->SetId($siteURL);
        $categoryAtom->SetTagLine($catInfo['name']);
        $categoryAtom->SetAuthor($this->gadget->registry->fetch('site_author', 'Settings'),
            $siteURL,
            $this->gadget->registry->fetch('gate_email', 'Settings'));
        $categoryAtom->SetGenerator('JAWS '.$this->app->registry->fetch('version'));
        $categoryAtom->SetCopyright($this->gadget->registry->fetch('site_copyright', 'Settings'));

        $objDate = Jaws_Date::getInstance();
        foreach ($result as $r) {
            $entry = new AtomEntry();
            $entry->SetTitle($r['title']);
            $post_id = empty($r['fast_url']) ? $r['id'] : $r['fast_url'];
            $url = $this->gadget->urlMap('SingleView', array('id' => $post_id), array('absolute', true));
            $entry->SetLink($url);
            $entry->SetId($url);

            $summary = $r['summary'];
            $text    = $r['text'];

            // for compatibility with old versions
            $more_pos = Jaws_UTF8::strpos($text, '[more]');
            if ($more_pos !== false) {
                $summary = Jaws_UTF8::substr($text, 0, $more_pos);
                $text    = Jaws_UTF8::str_replace('[more]', '', $text);

                // Update this entry to split summary and body of post
                $model = $this->gadget->model->load('Posts');
                $model->SplitEntry($r['id'], $summary, $text);
            }

            $summary = empty($summary)? $text : $summary;
            $summary = $this->gadget->plugin->parse($summary);
            $text    = $this->gadget->plugin->parse($text);

            $entry->SetSummary($summary, 'html');
            $entry->SetContent($text, 'html');
            $email = $r['email'];
            $entry->SetAuthor($r['nickname'], $categoryAtom->Link->HRef, $email);
            $entry->SetPublished($objDate->ToISO($r['publishtime']));
            $entry->SetUpdated($objDate->ToISO($r['updatetime']));

            $categoryAtom->AddEntry($entry);

            if (!isset($last_modified)) {
                $last_modified = $r['updatetime'];
            }
        }

        if (isset($last_modified)) {
            $categoryAtom->SetUpdated($objDate->ToISO($last_modified));
        } else {
            $categoryAtom->SetUpdated($objDate->ToISO(date('Y-m-d H:i:s')));
        }

        return $categoryAtom;
    }

    /**
     * Create ATOM of the blog
     *
     * @access  public
     * @param   int     $categoryId     Category ID
     * @param   string  $catAtom
     * @param   bool    $writeToDisk    Flag that determinates if Atom file should be written to disk
     * @return  mixed   Returns nothing if atom was saved, otherwise returns the ATOM in XML(string) or Jaws_Error on error
     */
    function MakeCategoryAtom($categoryId, $catAtom = null, $writeToDisk = false)
    {
        if (empty($catAtom)) {
            $catAtom = $this->GetCategoryAtomStruct($categoryId, 'atom');
            if (Jaws_Error::IsError($catAtom)) {
                return $catAtom;
            }
        }

        if ($writeToDisk) {
            if (!Jaws_FileManagement_File::is_writable(ROOT_DATA_PATH.'xml')) {
                return new Jaws_Error($this::t('ERROR_WRITING_CATEGORY_ATOMFILE'));
            }

            $filename = basename($catAtom->Link->HRef);
            $filename = substr($filename, 0, strrpos($filename, '.')) . '.atom';
            $catAtom->SetLink($this->app->getDataURL('xml/' . $filename, false));
            ///FIXME we need to do more error checking over here
            Jaws_FileManagement_File::file_put_contents(ROOT_DATA_PATH . 'xml/' . $filename, $catAtom->GetXML());
            Jaws_FileManagement_File::chmod(ROOT_DATA_PATH . 'xml/' . $filename);
        }

        return $catAtom->GetXML();
    }

    /**
     * Create RSS of a given category
     *
     * @access  public
     * @param   int     $categoryId     Category ID
     * @param   string  $catAtom
     * @param   bool    $writeToDisk    Flag that determinates if Atom file should be written to disk
     * @return  mixed   Returns the RSS(string) if it was required, or Jaws_Error on error
     */
    function MakeCategoryRSS($categoryId, $catAtom = null, $writeToDisk = false)
    {
        if (empty($catAtom)) {
            $catAtom = $this->GetCategoryAtomStruct($categoryId, 'rss');
            if (Jaws_Error::IsError($catAtom)) {
                return $catAtom;
            }
        }

        if ($writeToDisk) {
            if (!Jaws_FileManagement_File::is_writable(ROOT_DATA_PATH.'xml')) {
                return new Jaws_Error($this::t('ERROR_WRITING_CATEGORY_ATOMFILE'));
            }

            $filename = basename($catAtom->Link->HRef);
            $filename = substr($filename, 0, strrpos($filename, '.')) . '.rss';
            $catAtom->SetLink($this->app->getDataURL('xml/' . $filename, false));
            ///FIXME we need to do more error checking over here
            Jaws_FileManagement_File::file_put_contents(ROOT_DATA_PATH . 'xml/' . $filename, $catAtom->ToRSS2());
            Jaws_FileManagement_File::chmod(ROOT_DATA_PATH . 'xml/' . $filename);
        }

        return $catAtom->ToRSS2();
    }

}