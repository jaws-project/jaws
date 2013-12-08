<?php
/**
 * Comments URL maps
 *
 * @category    GadgetMaps
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
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
    'comments[/gadget/{gadgetname}]/rss'
);
$maps[] = array(
    'RecentCommentsAtom',
    'comments[/gadget/{gadgetname}]/atom'
);