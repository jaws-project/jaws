<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Poll"
 * "Last-Translator: Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_EN_POLL_NAME', "Poll");
define('_EN_POLL_DESCRIPTION', "A poll system that lets your visitors submit votes of any subject");

/* Actions Title */
define('_EN_POLL_ACTION_POLL_TITLE', "Poll");
define('_EN_POLL_ACTION_POLLS_TITLE', "Polls List");
define('_EN_POLL_ACTION_RESULT_TITLE', "Poll Results");
define('_EN_POLL_ACTION_POLLS_INGROUP_TITLE', "{0} polls list");

/* ACL's */
define('_EN_POLL_ACL_DEFAULT', "Administer Poll");
define('_EN_POLL_ACL_MANAGEPOLLS', "Add/Edit Polls");
define('_EN_POLL_ACL_MANAGEGROUPS', "Add/Edit Poll Groups");
define('_EN_POLL_ACL_VIEWREPORTS', "View Reports");

/* Polls Management */
define('_EN_POLL_POLLS', "Polls");
define('_EN_POLL_POLLS_QUESTION', "Question");
define('_EN_POLL_POLLS_TYPE', "Type");
define('_EN_POLL_POLLS_TYPE_COOKIE', "Cookie Based");
define('_EN_POLL_POLLS_TYPE_FREE', "No Limited");
define('_EN_POLL_POLLS_SELECT_TYPE', "Select Type");
define('_EN_POLL_POLLS_SELECT_SINGLE', "Single Select");
define('_EN_POLL_POLLS_SELECT_MULTI', "Multi Select");
define('_EN_POLL_POLLS_RESULT_VIEW', "Result View");
define('_EN_POLL_POLLS_ANSWER', "Answer");
define('_EN_POLL_POLLS_ANSWERS', "Answers");
define('_EN_POLL_POLLS_ADD', "Add Polls");
define('_EN_POLL_POLLS_ADD_TITLE', "Add Poll");
define('_EN_POLL_POLLS_EDIT_TITLE', "Edit Poll");
define('_EN_POLL_POLLS_ANSWERS_TITLE', "Edit Answers");
define('_EN_POLL_POLLS_CONFIRM_DELETE', "Delete this Poll?");
define('_EN_POLL_POLLS_INCOMPLETE_FIELDS', "Some fields haven't been (correctly) filled in.");

/* Polls Groups Management */
define('_EN_POLL_GROUPS', "Poll Groups");
define('_EN_POLL_GROUPS_ADD', "Add Groups");
define('_EN_POLL_GROUPS_ADD_TITLE', "Add Group");
define('_EN_POLL_GROUPS_EDIT_TITLE', "Edit Group");
define('_EN_POLL_GROUPS_POLLS_TITLE', "Group Polls");
define('_EN_POLL_GROUPS_CONFIRM_DELETE', "Are you sure you want to delete this group?");

/* Polls Reports */
define('_EN_POLL_REPORTS', "Reports");
define('_EN_POLL_REPORTS_VOTE', "Vote");
define('_EN_POLL_REPORTS_RESULTS', "Poll Results");
define('_EN_POLL_REPORTS_PERCENT', "{0}%");
define('_EN_POLL_REPORTS_TOTAL_VOTES', "Total Votes");

/* Layout */
define('_EN_POLL_ACTIONS_POLL', "Display poll");
define('_EN_POLL_ACTIONS_POLL_DESC', "Display poll");
define('_EN_POLL_LAYOUT_LAST', "Last poll");
define('_EN_POLL_ACTIONS_POLLS', "List of polls");
define('_EN_POLL_ACTIONS_POLLS_DESC', "List of polls");
define('_EN_POLL_LAYOUT_POLLS_ALL', 'All polls');

/* front-end */
define('_EN_POLL_VOTE', "Vote");
define('_EN_POLL_THANKS', "Thank you for your voting");
define('_EN_POLL_ALREADY_VOTED', "You have already voted on this poll");
define('_EN_POLL_RESULT_DISABLED', "Poll results is disabled");

/* Responses */
define('_EN_POLL_POLLS_ADDED', "The poll has been added");
define('_EN_POLL_POLLS_UPDATED', "The poll has been updated");
define('_EN_POLL_POLLS_DELETED', "Poll has been deleted");
define('_EN_POLL_GROUPS_CREATED', "Group {0} has been created.");
define('_EN_POLL_GROUPS_UPDATED', "Group {0} has been updated");
define('_EN_POLL_GROUPS_DELETED', "Group {0} has been deleted.");
define('_EN_POLL_GROUPS_UPDATED_POLLS', "The relations between polls and groups have been updated");
define('_EN_POLL_ANSWERS_UPDATED', "Poll answers has been updated");

/* Errors */
define('_EN_POLL_ERROR_POLL_NOT_ADDED', "There was a problem adding the poll");
define('_EN_POLL_ERROR_POLL_NOT_UPDATED', "There as a problem updating the poll");
define('_EN_POLL_ERROR_POLL_NOT_DELETED', "There was a problem deleting the poll");
define('_EN_POLL_ERROR_GROUP_NOT_ADDED', "There was a problem creating group {0}.");
define('_EN_POLL_ERROR_GROUP_NOT_UPDATED', "There was a problem updating group {0}.");
define('_EN_POLL_ERROR_GROUP_NOT_DELETED', "There was a problem deleting group {0}.");
define('_EN_POLL_ERROR_GROUP_DOES_NOT_EXISTS', "Group not found.");
define('_EN_POLL_ERROR_GROUP_TITLE_DUPLICATE', "Group title already exist.");
define('_EN_POLL_ERROR_REQUIRES_TWO_ANSWERS', "Two answers at least");
define('_EN_POLL_ERROR_ANSWERS_NOT_UPDATED', "There was a problem updating the poll answers");
define('_EN_POLL_ERROR_ANSWER_NOT_DELETED', "There was a problem deleting the poll answer");
define('_EN_POLL_ERROR_ANSWER_NOT_UPDATED', "There was a problem updating the poll answer");
define('_EN_POLL_ERROR_EXCEPTION_ANSWER_NOT_DELETED', "Answers could not been deleted while deleting the poll");
define('_EN_POLL_ERROR_ANSWER_NOT_ADDED', "There was a problem adding the answer to the poll");
define('_EN_POLL_ERROR_VOTE_NOT_ADDED', "There was a problem adding the vote");
