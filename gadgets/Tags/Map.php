<?php
/**
 * Tags URL maps
 *
 * @category   GadgetMaps
 * @package    Tags
 * @author     Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright  2013-2015 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'ViewTag',
    'tags/tags[/users/{user}]/{tag}[/gadgets/{tagged_gadget}][/pages/{page}]'
);
$maps[] = array(
    'TagCloud',
    'tags/cloud[/users/{user}][/gadgets/{tagged_gadget}]'
);
$maps[] = array(
    'ManageTags',
    'tags/manage[/page/{page}][/gadgets_filter/{gadgets_filter}][/term/{term}][/pageitem/{page_item}]'
);
$maps[] = array(
    'EditTagUI',
    'tags/tags/{tag}/edit'
);
