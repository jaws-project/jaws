<?php
/**
 * Forum Gadget Info
 *
 * @category   GadgetInfo
 * @package    Forum
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class ForumInfo extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.1.0';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'ManageForums',
        'ManageTopics',
        'ManagePosts',
        'AddTopic',
        'EditTopic',
        'CloseTopic',
        'AddPostToClosedTopic',
        'DeleteTopic',
    );

}