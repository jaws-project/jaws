<?php
/**
 * Blog URL maps
 *
 * @category   GadgetMaps
 * @package    Blog
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi �ormar �orbj�rnsson <dufuz@php.net>
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'DefaultAction',
    'blog'
);
$maps[] = array(
    'LastPost',
    'blog/last'
);
$maps[] = array(
    'ViewDatePage',
    'blog/{year}[/{month}][/{day}][/page/{page}]',
    array('year'  => '\d{4}',
          'month' => '[01]?\d',
          'day'   => '[0-3]?\d',
          'page'  => '[[:digit:]]+')
);
$maps[] = array(
    'RSS',
    'blog/rss',
    array(),
    'xml'
);
$maps[] = array(
    'ShowRSSCategory',
    'blog/rss/category/{id}',
    array('id' => '[\p{L}[:digit:]\-_\.]+',)
);
$maps[] = array(
    'Atom',
    'blog/atom',
    array(),
    'xml'
);
$maps[] = array(
    'ShowAtomCategory',
    'blog/atom/category/{id}',
    array('id' => '[\p{L}[:digit:]\-_\.]+',)
);
$maps[] = array(
    'SingleView', 
    'blog/show/{id}[/page/{page}][/order/{order}]',
    array('id' => '[\p{L}[:digit:]\-_\.]+',)
);
$maps[] = array(
    'ViewAuthorPage',
    'blog/author/{id}[/page/{page}]',
    array('id'   => '[\p{L}[:digit:]\-_\.]+',
          'page' => '[[:digit:]]+',)
);
$maps[] = array(
    'ViewPage',
    'blog/page/{page}'
);
$maps[] = array(
    'TypePosts',
    'blog/type/{type}',
    array('type'   => '[\p{L}[:digit:]\-_\.]+')
);
$maps[] = array(
    'Types',
    'blog/types'
);
$maps[] = array(
    'ShowCategory',
    'blog/category/{id}[/page/{page}]',
    array('id'   => '[\p{L}[:digit:]\-_\.]+',
          'page' => '[[:digit:]]+',)
);
$maps[] = array(
    'CategoriesList',
    'blog/categories'
);
$maps[] = array(
    'Trackback',
    'trackback/{id}',
    array(),
    ''
);
$maps[] = array(
    'Archive',
    'blog/archive'
);
$maps[] = array(
    'PopularPosts',
    'blog/popular[/page/{page}]'
);
$maps[] = array(
    'Authors',
    'blog/authors'
);
$maps[] = array(
    'Pingback',
    'pingback',
    array(),
    ''
);
