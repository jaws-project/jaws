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
 * @copyright  2004-2014 Jaws Development Group
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
 * User authentication
 *
 * @access  public
 * @param   string  $user       username
 * @param   string  $password   password
 * @return  mixed   True or Jaws_Error
 */
function userAuthentication($username, $password)
{
    $authType = $GLOBALS['app']->Registry->fetch('authtype', 'Users');
    $authType = preg_replace('/[^[:alnum:]_-]/', '', $authType);
    $authFile = JAWS_PATH . 'include/Jaws/Auth/' . $authType . '.php';
    if (empty($authType) || !file_exists($authFile)) {
        $GLOBALS['log']->Log(
            JAWS_LOG_NOTICE,
            $authFile. ' file doesn\'t exists, using default authentication type'
        );
        $authType = 'Default';
    }

    if ($username === '' && $password === '') {
        $result = Jaws_Error::raiseError(
            _t('GLOBAL_ERROR_LOGIN_WRONG'),
            __FUNCTION__,
            JAWS_ERROR_NOTICE
        );
    }

    require_once JAWS_PATH . 'include/Jaws/Auth/' . $authType . '.php';
    $className = 'Jaws_Auth_' . $authType;
    $objAuth = new $className();
    $result = $objAuth->Auth($username, $password);
    if (!Jaws_Error::IsError($result)) {
        $GLOBALS['app']->Session->SetAttribute('logged', true);
        $GLOBALS['app']->Session->SetAttribute('user', $result['id']);
        $GLOBALS['app']->Session->SetAttribute('groups', $result['groups']);
        $GLOBALS['app']->Session->SetAttribute('superadmin', $result['superadmin']);
    }

    return $result;
}

/**
 * Get Blog ACL permission for a specified user
 *
 * @access  public
 * @param   string  $user           username
 * @param   string  $task           task to use
 * @return  bool    Graned (true) or Denied (false)    
 */
function GetBlogPermission($user, $task)
{
    return $GLOBALS['app']->Session->GetPermission('Blog', $task);
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3,  _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission('Blog', 'default_admin')) {
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'default_admin')) {
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'AddEntries')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $struct  = XML_RPC_decode($params->getParam(3));
    $cats    = $struct['categories'];
    $catsModel = Jaws_Gadget::getInstance('Blog')->model->load('Categories');
    if (Jaws_Error::isError($catsModel)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $catsModel->GetMessage());
    }

    $categories = array();
    foreach ($cats as $cat) {
        $catInfo = $catsModel->GetCategoryByName($cat);
        if (Jaws_Error::IsError($catInfo)) {
            return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $catInfo->GetMessage());
        }

        if (isset($catInfo['id'])) {
            $categories[] = $catInfo['id'];
        }
    }

    $title = $struct['title'];
    if (!isset($struct['mt_text_more'])) {
        if (false !== $more_pos = Jaws_UTF8::strpos($struct['description'], '<!--more-->')) {
            $summary = Jaws_UTF8::substr($struct['description'], 0, $more_pos);
            $content = Jaws_UTF8::substr($struct['description'], $more_pos + 11);
        } else {
            $summary = $struct['description'];
            $content = '';
        }
    } else {
        $summary = $struct['description'];
        $content = $struct['mt_text_more'];
    }
    $summary = parseContent($summary);
    $content = parseContent($content);

    // allow comments
    if (isset($struct['mt_allow_comments'])) {
        $allow_c = (bool)$struct['mt_allow_comments'];
    } else {
        $allow_c = (bool)$GLOBALS['app']->Registry->fetch('allow_comments');
    }
    // categories
    if (empty($categories)) {
        $categories = array($GLOBALS['app']->Registry->fetch('default_category'));
    }
    // published
    $publish  = getScalarValue($params, 4);
    // tags
    $tags = isset($struct['mt_keywords'])? $struct['mt_keywords'] : '';
    // publish time
    $timestamp = null;
    if (isset($struct['date_created_gmt'])) {
        $date = date_parse_from_format('Ymd\TH:i:s', $struct['date_created_gmt']);
        $date = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
        $timestamp = date('Y-m-d H:i:s', $date);
    }

    $trackbacks = '';
    if (isset($struct['mt_tb_ping_urls'])) {
        $trackbacks = implode("\n", $struct['mt_tb_ping_urls']);
    }

    $postsModel = Jaws_Gadget::getInstance('Blog')->model->loadAdmin('Posts');
    $post_id = $postsModel->NewEntry(
        $userInfo['id'],
        $categories,
        $title,
        $summary,
        $content,
        $title,
        '',
        '',
        $tags,
        $allow_c,
        $trackbacks,
        $publish,
        $timestamp
    );
    if (Jaws_Error::IsError($post_id)) {
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'AddEntries')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $struct  = XML_RPC_decode($params->getParam(3));
    $cats    = $struct['categories'];
    $catsModel = Jaws_Gadget::getInstance('Blog')->model->load('Categories');
    if (Jaws_Error::isError($catsModel)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $catsModel->GetMessage());
    }

    $categories = array();
    foreach ($cats as $cat) {
        $catInfo = $catsModel->GetCategoryByName($cat);
        if (Jaws_Error::IsError($catInfo)) {
            return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $catInfo->GetMessage());
        }

        if (isset($catInfo['id'])) {
            $categories[] = $catInfo['id'];
        }
    }

    $title = $struct['title'];
    if (!isset($struct['mt_text_more'])) {
        if (false !== $more_pos = Jaws_UTF8::strpos($struct['description'], '<!--more-->')) {
            $summary = Jaws_UTF8::substr($struct['description'], 0, $more_pos);
            $content = Jaws_UTF8::substr($struct['description'], $more_pos + 11);
        } else {
            $summary = $struct['description'];
            $content = '';
        }
    } else {
        $summary = $struct['description'];
        $content = $struct['mt_text_more'];
    }
    $summary = parseContent($summary);
    $content = parseContent($content);

    // allow comments
    if (isset($struct['mt_allow_comments'])) {
        $allow_c = (bool)$struct['mt_allow_comments'];
    } else {
        $allow_c = (bool)$GLOBALS['app']->Registry->fetch('allow_comments');
    }
    // published
    $publish = getScalarValue($params, 4);
    // tags
    $tags = isset($struct['mt_keywords'])? $struct['mt_keywords'] : '';
    // publish time
    $timestamp = null;
    if (isset($struct['date_created_gmt'])) {
        $date = date_parse_from_format('Ymd\TH:i:s', $struct['date_created_gmt']);
        $date = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
        $timestamp = date('Y-m-d H:i:s', $date);
    }
    // trackbacks
    $trackbacks = '';
    if (isset($struct['mt_tb_ping_urls'])) {
        $trackbacks = implode("\n", $struct['mt_tb_ping_urls']);
    }

    $postModel = Jaws_Gadget::getInstance('Blog')->model->loadAdmin('Posts');
    $blog_result = $postModel->UpdateEntry(
        $post_id,
        $categories,
        $title,
        $summary,
        $content,
        '',
        '',
        '',
        $tags,
        $allow_c,
        $trackbacks,
        $publish,
        $timestamp
    );
    if (Jaws_Error::IsError($blog_result)) {
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'DeleteEntries')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $publish  = getScalarValue($params, 4);
    $model = Jaws_Gadget::getInstance('Blog')->model->loadAdmin('Posts');
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'default_admin')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, $categories->GetMessage());
    }

    $model = Jaws_Gadget::getInstance('Blog')->model->load('Categories');
    if (Jaws_Error::isError($model)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $model->GetMessage());
    }

    $categories = $model->GetCategories();
    if (Jaws_Error::IsError($categories)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $categories->GetMessage());
    }

    $struct = array();
    foreach ($categories as $category) {
        $cid = empty($category['fast_url']) ? $category['id'] : Jaws_XSS::filter($category['fast_url']);
        $htmlurl = $GLOBALS['app']->Map->GetURLFor('Blog', 'ShowCategory', array('id' => $cid));
        $rssurl  = $GLOBALS['app']->Map->GetURLFor('Blog', 'ShowRSSCategory', array('id' => $category['id']));
        $data = array(
            'categoryId'   => new XML_RPC_Value($category['id']),
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'default_admin')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $postsModel = Jaws_Gadget::getInstance('Blog')->model->load('Posts');
    if (Jaws_Error::isError($postsModel)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $postsModel->GetMessage());
    }

    $entry = $postsModel->GetEntry($post_id);
    if (Jaws_Error::IsError($entry)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $entry->GetMessage());
    }

    $publishtime = date('Ymd\TH:i:s', strtotime($entry['publishtime']));
    $updatedtime = date('Ymd\TH:i:s', strtotime($entry['updatetime']));
    $summary = stripslashes($entry['summary']);
    $content = stripslashes($entry['text']);

    $categories = array();
    $categoriesModel = Jaws_Gadget::getInstance('Blog')->model->load('Categories');
    $cats = $categoriesModel->GetCategoriesInEntry($post_id);
    if (!Jaws_Error::isError($cats)) {
        foreach ($cats as $cat) {
            $categories[] = new XML_RPC_Value($cat['name']);
        }
    }

    $pid  = empty($entry['fast_url']) ? $entry['id'] : $entry['fast_url'];
    $link = $GLOBALS['app']->Map->GetURLFor('Blog', 'SingleView', array('id' => $pid));

    $data = array(
        'blogid'      => new XML_RPC_Value('1'),
        'postid'      => new XML_RPC_Value($entry['id'], 'int'),
        'title'       => new XML_RPC_Value($entry['title']),
        'description' => new XML_RPC_Value($summary),
        'link'        => new XML_RPC_Value($link),
        'userid'      => new XML_RPC_Value($entry['user_id'], 'int'),
        'date_created_gmt'  => new XML_RPC_Value($publishtime),
        'date_modified_gmt' => new XML_RPC_Value($updatedtime),
        'permaLink'   => new XML_RPC_Value($link),
        'categories'  => new XML_RPC_Value($categories, 'array'),
        'mt_excerpt'  => new XML_RPC_Value(''),
        'mt_keywords' => new XML_RPC_Value(implode(',', $entry['tags'])),
        'mt_text_more'      => new XML_RPC_Value($content),
        'mt_allow_comments' => new XML_RPC_Value($entry['allow_comments'], 'boolean'),
        'mt_allow_pings'    => new XML_RPC_Value($entry['allow_comments'], false),
        'mt_tb_ping_urls'   => new XML_RPC_Value(explode("\n", $entry['trackbacks']), 'array'),
    );

    $struct = new XML_RPC_Value($data, 'struct');
    return new XML_RPC_Response($struct);
}

/**
 * metaWeblog.getPostCategories
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_getPostCategories($params)
{
    $post_id  = getScalarValue($params, 0);
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'default_admin')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, $categories->GetMessage());
    }

    $categoriesModel = Jaws_Gadget::getInstance('Blog')->model->load('Categories');
    $categories = $categoriesModel->GetCategoriesInEntry($post_id);
    if (Jaws_Error::isError($categories)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+2, $categories->GetMessage());
    }

    $struct = array();
    foreach ($categories as $category) {
        $data = array(
            'categoryId'   => new XML_RPC_Value($category['id']),
            'categoryName' => new XML_RPC_Value($category['name']),
            'isPrimary'    => new XML_RPC_Value(false),
        );
        $struct[] = new XML_RPC_Value($data, 'struct');
    }

    $val = new XML_RPC_Value($struct, 'array');
    return new XML_RPC_Response($val);
}


/**
 * metaWeblog.getPostCategories
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_setPostCategories($params)
{
    $post_id  = getScalarValue($params, 0);
    $user     = getScalarValue($params, 1);
    $password = getScalarValue($params, 2);

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'default_admin')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, $categories->GetMessage());
    }

    $categories = getScalarValue($params, 3);
    return new XML_RPC_Response(new XML_RPC_Value('1', 'boolean'));
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

    $userInfo = userAuthentication($user, $password);
    if (Jaws_Error::IsError($userInfo)) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+4, _t('GLOBAL_ERROR_LOGIN_WRONG'));
    }

    if (!GetBlogPermission($user, 'default_admin')) {
        return new XML_RPC_Response(0, $GLOBALS['XML_RPC_erruser']+3, _t('GLOBAL_ERROR_NO_PRIVILEGES'));
    }

    $model = Jaws_Gadget::getInstance('Blog')->model->load('Posts');
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
        $categoriesModel = Jaws_Gadget::getInstance('Blog')->model->load('Categories');
        $cats = $categoriesModel->GetCategoriesInEntry($entry['id']);
        if (!Jaws_Error::isError($cats)) {
            foreach ($cats as &$cat) {
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

/**
 * metaWeblog.setTemplate
 *
 * @access  public
 * @param   array   $params     array of params
 * @return  XML_RPC_Response object
 */
function metaWeblog_setPostPublish($params)
{
    return new XML_RPC_Response(new XML_RPC_Value('1', 'boolean'));
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
    'blogger.deletePost' => array(
        'function'  => 'metaWeblog_deletePost',
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
    // MovableType API
    'mt.getCategoryList' => array(
        'function'  => 'metaWeblog_getCategories',
    ),
    'mt.getPostCategories' => array(
        'function'  => 'metaWeblog_getPostCategories',
    ),
    'mt.setPostCategories' => array(
        'function'  => 'metaWeblog_setPostCategories',
    ),
    'mt.publishPost' => array(
        'function'  => 'metaWeblog_setPostPublish',
    ),

    // Pingback
    'pingback.ping'                    => array('function' => 'pingback_ping'),
    'pingback.extensions.getPingbacks' => array('function' => 'pingback_extensions_getPingbacks'),
);

$server = new XML_RPC_Server($rpc_methods);
