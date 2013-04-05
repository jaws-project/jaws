<?php
/**
 * Comments URL maps
 *
 * @category   GadgetMaps
 * @package    Shoutbox
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2008-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
$maps[] = array(
    'Comments',
    'comments[/page/{page}/perpage/{perpage}/order/{order}]',
    array(
        'page' => '[[:digit:]]+',
        'perpage' => '[[:digit:]]+',
        'order' => '[[:digit:]]+',
    )
);
