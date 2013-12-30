<?php
/**
 * Blog XML RPC
 * APIs
 * - metaweblog
 *    http://www.xmlrpc.com/metaWeblogApi
 *
 * @author     Helgi �ormar �orbj�rnsson <dufuz@php.net>
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @package Blog
 */
define('APP_TYPE',    'web');
define('JAWS_SCRIPT', 'xmlrpc');
define('BASE_SCRIPT', basename(__FILE__));
require '../../config/JawsConfig.php';
require_once JAWS_PATH . 'include/Jaws/InitApplication.php';
$GLOBALS['app']->loadObject('Jaws_ACL', 'ACL');
require_once JAWS_PATH . 'include/Jaws/User.php';
require_once PEAR_PATH . 'XML/RPC/Server.php';

/**
 * Get Blog ACL permission for a specified user
 *
 * @access  public
 * @param   string  $user           username
 * @param   string  $task           task to use
 * @param   bool    $superadmin     is super admin
 * @return  bool    Graned (true) or Denied (false)    
 */
function GetBlogPermission($user, $task, $superadmin)
{
    $groups = Jaws_User::GetGroupsOfUser($user);
    if (Jaws_Error::IsError($groups)) {
        return false;
    }

    return $GLOBALS['app']->ACL->GetFullPermission($user, array_keys($groups), 'Blog', $task, $superadmin);
}

/**
 * Aux functions
 *
 * @access  public
 * @param   object  $p
 * @param   string  $i
 * @return  mixed   
 */
function getScalarValue($p, $i)
{
    $r = $p->getParam($i);
    if (!XML_RPC_Value::isValue($r)) {
        return false;
        //return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, 'fubar user param');
    }

    return $r->scalarval();
}

/**
 * Parses content
 *
 * @access  public
 * @param   string  $content
 * @return  string  content
 */
function parseContent($content)
{
    $content = htmlentities($content, ENT_NOQUOTES, 'UTF-8');
    $in  = array('&gt;', '&lt;', '&quot;', '&amp;');
    $out = array('>', '<', '"', '&');
    $content = str_replace($in, $out, $content);

    return $content;
}

/**
 * metaWeblog.getUsersBlogs
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getUsersBlogs($params)
{
    // parameters
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3,  _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'default_admin', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $struct = array();
    $siteurl = $GLOBALS['app']->GetSiteURL();
    $sitename = $GLOBALS['app']->Registry->fetch('site_name', 'Settings');

    $data = array(
        'isAdmin'  => new XML_RPC_Value('1', 'boolean'),
        'url'      => new XML_RPC_Value($siteurl),
        'blogid'   => new XML_RPC_Value('1'),
        'blogName' => new XML_RPC_Value($sitename)
    );
    $struct[]  = new XML_RPC_Value($data, 'struct');
    $data = array($struct[0]);
    $response = new XML_RPC_Value($data, 'array');
    return new XML_RPC_Response($response);
}

/**
 * metaWeblog.getUserInfo
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getUserInfo($params)
{
    // parameters
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);
    if (!$user || !$password) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, 'fubar user param');
    }

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'default_admin', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $siteurl = $GLOBALS['app']->GetSiteURL();
    $user = Jaws_User::GetUser((int)$userInfo['id'], true, true);
    $data = array(
        'nickname'  => new XML_RPC_Value($user['username']),
        'userid'    => new XML_RPC_Value($user['id']),
        'url'       => new XML_RPC_Value($siteurl),
        'email'     => new XML_RPC_Value($user['email']),
        'lastname'  => new XML_RPC_Value($user['lname']),
        'firstName' => new XML_RPC_Value($user['fname']),
    );
    $struct = new XML_RPC_Value($data, 'struct');
    return new XML_RPC_Response($struct);
}

/**
 * New Post (metaWeblog.newPost)
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_newPost($params)
{
    // parameters
    $blogToPost = getScalarValue($params, 0); // blog gadget only supports 1 blog, so this parameter is not used.
    $user       = getScalarValue($params, 1);
    $password   = getScalarValue($params, 2);

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'AddEntries', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $struct  = XML_RPC_decode($params->getParam(3));
    $title   = $struct['title'];
    $cats    = $struct['categories'];
    $summary = '';
    $content = parseContent($struct['description']);
    $more_pos = Jaws_UTF8::strpos($content, '<!--more-->');
    if ($more_pos !== false) {
        $summary = Jaws_UTF8::substr($content, 0, $more_pos);
        $content = Jaws_UTF8::substr_replace($content, '', 0, $more_pos + 11);
    }

    $model = $this->gadget->model->loadAdmin('Categories');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $categories = array();
    foreach ($cats as $cat) {
        $catInfo = $model->GetCategoryByName($cat);
        if (Jaws_Error::IsError($catInfo)) {
            return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $catInfo->GetMessage());
        }

        if (isset($catInfo['id'])) {
            $categories[] = $catInfo['id'];
        }
    }

    // Not used yet
//     $extended       = $data['mt_text_more'];
//     $excerpt        = $data['mt_excerpt'];
//     $keywords       = $data['mt_keywords'];
//     $allow_ping     = $data['mt_allow_ping'];
//     $convert_breaks = $data['mt_convert_breaks'];
//     $tb_ping_urls   = $data['mt_tb_ping_urls'];

    // Allow Comments ?
    if (!empty($data['mt_allow_comments'])) {
        $allow_c = $data['mt_allow_comments'];
    } else {
        $allow_c = $this->registry->fetch('allow_comments');
        $allow_c = $allow_c == 'true' ? 1 : 0;
    }

    if (empty($categories)) {
        $categories = array($this->registry->fetch('default_category'));
    }
    $publish  = getScalarValue($params, 4);

    $post_id = $model->NewEntry($userInfo['id'], $categories, $title, $summary, $content, $title, '', '', $allow_c, '', $publish);
    if (Jaws_Error::IsError ($post_id)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $post_id->GetMessage());
    }

    $val = new XML_RPC_Value("$post_id", 'string');
    return new XML_RPC_Response($val);
}

/**
 * metaWeblog.editPost
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_editPost($params)
{
    $post_id  = getScalarValue($params, 0);
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);

    $struct  = XML_RPC_decode($params->getParam(3));
    $title   = $struct['title'];
    $cats    = $struct['categories'];
    $summary = '';
    $content = parseContent($struct['description']);
    $more_pos = Jaws_UTF8::strpos($content, '<!--more-->');
    if ($more_pos !== false) {
        $summary = Jaws_UTF8::substr($content, 0, $more_pos);
        $content = Jaws_UTF8::substr_replace($content, '', 0, $more_pos + 11);
    }

    $model = $this->gadget->model->loadAdmin('Categories');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $categories = array();
    foreach ($cats as $cat) {
        $catInfo = $model->GetCategoryByName($cat);
        if (Jaws_Error::IsError($catInfo)) {
            return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $catInfo->GetMessage());
        }

        if (isset($catInfo['id'])) {
            $categories[] = $catInfo['id'];
        }
    }

    // Allow Comments ?
    $allow_c = $this->registry->fetch('allow_comments');
    $allow_c = $allow_c == 'true' ? 1 : 0;

    $publish = getScalarValue($params, 4);

    // Not used yet
//     $extended       = $data['mt_text_more'];
//     $excerpt        = $data['mt_excerpt'];
//     $keywords       = $data['mt_keywords'];
//     $allow_c        = $data['mt_allow_comments'];
//     $allow_ping     = $data['mt_allow_ping'];
//     $convert_breaks = $data['mt_convert_breaks'];

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'AddEntries', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $blog_result = $model->UpdateEntry($post_id, $categories, $title, $summary, $content, '', '', '', $allow_c, '', $publish);
    if (Jaws_Error::IsError ($blog_result)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $blog_result->GetMessage());
    }

    return new XML_RPC_Response(new XML_RPC_Value('1', 'boolean'));
}

/**
 * metaWeblog.deletePost
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_deletePost($params)
{
    // parameters
    $post_id  = getScalarValue($params, 1);
    $user     = getScalarValue($params, 2);
    $password = getScalarValue($params, 3);
    $publish  = getScalarValue($params, 4);

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'DeleteEntries', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $model = $this->gadget->model->loadAdmin('Posts');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $res = $model->DeleteEntry($post_id);
    if (Jaws_Error::IsError($res)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $res->GetMessage());
    }

    $val = new XML_RPC_Value('1', 'boolean');
    return new XML_RPC_Response($val);
}

/**
 * metaWeblog.getCategories
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getCategories($params)
{
    $blog     = getScalarValue($params, 0); // blog gadget only supports 1 blog, so this parameter is not used.
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'default_admin', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, $categories->GetMessage());
    }

    $model = $this->gadget->model->load('Categories');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $categories = $model->GetCategories();
    if (Jaws_Error::IsError ($categories)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $categories->GetMessage());
    }

    $struct = array();
    foreach ($categories as $category) {
        $cid = empty($category['fast_url']) ? $category['id'] : Jaws_XSS::filter($category['fast_url']);
        $htmlurl = $GLOBALS['app']->Map->GetURLFor('Blog', 'ShowCategory', array('id' => $cid));
        $rssurl  = $GLOBALS['app']->Map->GetURLFor('Blog', 'ShowRSSCategory', array('id' => $category['id']));
        $data = array(
            'categoryid'   => new XML_RPC_Value($category['id']),
            'categoryName' => new XML_RPC_Value($category['name']),
            'title'        => new XML_RPC_Value($category['name']),
            'description'  => new XML_RPC_Value($category['description']),
            'htmlUrl'      => new XML_RPC_Value($htmlurl),
            'rssUrl'       => new XML_RPC_Value($rssurl),
        );
        $struct[] = new XML_RPC_Value($data, 'struct');
    }

    $val = new XML_RPC_Value($struct, 'array');
    return new XML_RPC_Response($val);
}

/**
 * metaWeblog.getPost
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getPost($params)
{
    $post_id  = getScalarValue($params, 0);
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'default_admin', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $model = $this->gadget->model->load('Posts');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $entry = $model->GetEntry($post_id);
    if (Jaws_Error::IsError($entry)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $entry->GetMessage());
    }

    $publishtime = strtotime($entry['publishtime']);
    $publishtime = date('Ymd', $publishtime) . 'T' . date('H:i:s', $publishtime);
    $summary = stripslashes($entry['summary']);
    $content = stripslashes($entry['text']);

    $categories = array();
    $cats = $model->GetCategoriesInEntry($post_id);
    if (!Jaws_Error::isError($cats)) {
        foreach ($cats as $cat) {
            $categories[] = new XML_RPC_Value($cat['name']);
        }
    }

    $pid  = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
    $link = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $pid));

    $data = array(
        'categories'  => new XML_RPC_Value($categories, 'array'),
        'dateCreated' => new XML_RPC_Value($publishtime, 'dateTime.iso8601'),
        'description' => new XML_RPC_Value($summary),
        'link'        => new XML_RPC_Value($link),
        'permLink'    => new XML_RPC_Value($link),
        'postid'      => new XML_RPC_Value($entry['id'], 'int'),
        'title'       => new XML_RPC_Value($entry['title']),
        'userid'      => new XML_RPC_Value($entry['user_id'], 'int'),
        'blogid'      => new XML_RPC_Value('1'),
        'mt_allow_comments' => new XML_RPC_Value($entry['allow_comments'], 'boolean'),
        'mt_text_more'      => new XML_RPC_Value($content)
    );

    $struct = new XML_RPC_Value($data, 'struct');
    return new XML_RPC_Response($struct);
}

/**
 * metaWeblog.getRecentPosts
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getRecentPosts($params)
{
    //parameters
    $user          = getScalarValue($params, 1);
    $password      = getScalarValue($params, 2);
    $entries_limit = getScalarValue($params,3);

    if (Jaws_Error::IsError($userInfo = Jaws_User::Valid($user, $password))) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    $GLOBALS['app']->Session->SetAttribute('user', $userInfo['id']);
    $GLOBALS['app']->Session->SetAttribute('superadmin', $userInfo['superadmin']);
    if (!GetBlogPermission($user, 'default_admin', $userInfo['superadmin'])) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $model = $this->gadget->model->load('Posts');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $entries = $model->GetLastEntries($entries_limit);
    if (Jaws_Error::IsError($entries)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $entries->GetMessage());
    }

    $i = 0;
    foreach ($entries as $entry) {
        $publishtime = strtotime($entry['publishtime']);
        $publishtime = date('Ymd', $publishtime) . 'T' . date('H:i:s', $publishtime);
        $summary     = stripslashes($entry['summary']);
        $content     = stripslashes($entry['text']);
        $permalink   = new XML_RPC_Value($GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $entry['id'])));
        $link        = new XML_RPC_Value($GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $entry['fast_url'])));
        //FIXME: Fill the fields
        $allow_pings = new XML_RPC_Value('');

        // Fetch categories for this post
        $categories = array();
        $cats = $model->GetCategoriesInEntry($entry['id']);
        if (!Jaws_Error::isError($cats)) {
            foreach ($cats as $cat) {
                $categories[] = new XML_RPC_Value($cat['name']);
            }
        }

        $data = array(
            'authorName'        => new XML_RPC_Value($entry['name']),
            'dateCreated'       => new XML_RPC_Value($publishtime, 'dateTime.iso8601'),
            'userid'            => new XML_RPC_Value($entry['user_id'], 'int'),
            'postid'            => new XML_RPC_Value($entry['id'], 'int'),
            'blogid'            => new XML_RPC_Value('1'),
            'description'       => new XML_RPC_Value($summary),
            'title'             => new XML_RPC_Value($entry['title']),
            'categories'        => new XML_RPC_Value($categories, 'array'),
            'link'              => $link,
            'permalink'         => $permalink,
            'mt_excerpt'        => new XML_RPC_Value(''),
            'mt_allow_comments' => new XML_RPC_Value($entry['allow_comments'], 'boolean'),
            'mt_allow_pings'    => $allow_pings,
            'mt_text_more'      => new XML_RPC_Value($content)
        );
        $struct[$i] = new XML_RPC_Value($data, 'struct');
        $i++;
    }

    if ($i > 0 ) {
        $data = array($struct[0]);
        for ($j = 1; $j < $i; $j++) {
            array_push($data, $struct[$j]);
        }
    } else {
        $data = array();
    }

    $resp = new XML_RPC_Value($data, 'array');
    return new XML_RPC_Response($resp);
}

/**
 * metaWeblog.getTemplate
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getTemplate($params)
{
    return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('BLOG_ERROR_XMLRPC_NO_GETTEMPLATE_SUPPORT'));
}

/**
 * metaWeblog.setTemplate
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_setTemplate($params)
{
    return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('BLOG_ERROR_XMLRPC_NO_SETTEMPLATE_SUPPORT'));
}

/* PingBack functions
 * specs on www.hixie.ch/specs/pingback/pingback
 */

/**
 * pingback.ping gets a pingback and registers it
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  void
 */
function pingback_ping($params)
{
    //parameters
    $linkfrom = getScalarValue($params, 0);
    $linkto   = getScalarValue($params, 1);
}

/**
 * pingback.extensions.getPingbacks returns an array of URLs
 *   that pingbacked the given URL
 *   specs on http://www.aquarionics.com/misc/archives/blogite/0198.html
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  void
 */
function pingback_extensions_getPingbacks($params)
{

}

/*
 *  XML-RPC Server
 */

$rpc_methods = array(
    // Blogger.com API
    'blogger.getUsersBlogs' => array(
        'function'  => 'metaWeblog_getUsersBlogs',
        'signature' => array(
            array('array', 'string', 'string', 'string'),
        ),
    ),
    'blogger.getUserInfo' => array(
        'function'  => 'metaWeblog_getUserInfo',
        'signature' => array(
            array('struct', 'string', 'string', 'string'),
        ),
    ),

    // metaWeblog API
    'metaWeblog.getUsersBlogs' => array(
        'function'  => 'metaWeblog_getUsersBlogs',
        'signature' => array(
            array('array', 'string', 'string', 'string'),
        ),
    ),
    'metaWeblog.getUserInfo' => array(
        'function'  => 'metaWeblog_getUserInfo',
        'signature' => array(
            array('struct', 'string', 'string', 'string'),
        ),
    ),
    'metaWeblog.newPost' => array(
        'function'  => 'metaWeblog_newPost',
        'signature' => array(
            array('string', 'string', 'string', 'string', 'struct', 'boolean'),
        ),
    ),
    'metaWeblog.editPost' => array(
        'function'  => 'metaWeblog_editPost',
        'signature' => array(
            array('boolean', 'string', 'string', 'string', 'struct', 'boolean'),
        ),
    ),
    'metaWeblog.getPost' => array(
        'function'  => 'metaWeblog_getPost',
        'signature' => array(
            array('struct', 'string', 'string', 'string'),
        ),
    ),
    'metaWeblog.getCategories' => array(
        'function'  => 'metaWeblog_getCategories',
        'signature' => array(
            array('array', 'string', 'string', 'string'),
        ),
    ),
    'metaWeblog.getRecentPosts' => array(
        'function'  => 'metaWeblog_getRecentPosts',
        'signature' => array(
            array('array', 'string', 'string', 'string', 'int'),
        ),
    ),
    // 'metaWeblog.newMediaObject' => array('function' => 'metaWeblog_newMediaObject'), No Supported
    'metaWeblog.deletePost' => array(
        'function'  => 'metaWeblog_deletePost',
        'signature' => array(
            array('boolean', 'string', 'string', 'string', 'string', 'boolean'),
        ),
    ),
    'metaWeblog.getTemplate'   => array('function' => 'metaWeblog_getTemplate'),
    'metaWeblog.setTemplate'   => array('function' => 'metaWeblog_setTemplate'),

    // Pingback
    'pingback.ping'                    => array('function' => 'pingback_ping'),
    'pingback.extensions.getPingbacks' => array('function' => 'pingback_extensions_getPingbacks'),
);

$server = new XML_RPC_Server($rpc_methods);
