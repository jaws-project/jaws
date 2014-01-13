<?php
/**
 * Comments URL maps
 *
 * @category    GadgetMaps
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013-2014 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'Guestbook',
    'guestbook[/page/{page}][/order/{order}]',
    array(
        'page' => '[[:digit:]]+',
        'perpage' => '[[:digit:]]+',
        'order' => '[[:digit:]]+',
    )
);
$maps[] = array(
    'RecentCommentsRSS',
    'comments[/gadget/{gadgetname}][/action/{actionname}][/reference/{reference}]/rss'
);
$maps[] = array(
    'RecentCommentsAtom',
    'comments[/gadget/{gadgetname}][/action/{actionname}][/reference/{reference}]/atom'
);
$maps[] = array(
    'UserComments',
    'comments/user/{user}',
    array(
        'user' => '[[:digit:]]+',
    )
);