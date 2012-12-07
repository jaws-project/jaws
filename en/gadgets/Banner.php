<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Banner"
 * "Last-Translator: Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_EN_BANNER_NAME', "Banners");
define('_EN_BANNER_DESCRIPTION', "Banners management.");

/* ACLs */
define('_EN_BANNER_ACL_DEFAULT', "Use banners");
define('_EN_BANNER_ACL_MANAGEBANNERS', "Banner management");
define('_EN_BANNER_ACL_MANAGEGROUPS', "Group management");
define('_EN_BANNER_ACL_BANNERSGROUPING', "Banner grouping");
define('_EN_BANNER_ACL_VIEWREPORTS', "View reports");

/* Layout Strings */
define('_EN_BANNER_ACTION_TITLE', "{0} banners");
define('_EN_BANNER_ACTION_DISPLAY_NAME', "Banners");
define('_EN_BANNER_ACTION_DISPLAY_DESCRIPTION', "Display Banners");

/* Banners Management */
define('_EN_BANNER_BANNERS_BANNERS', "Banners");
define('_EN_BANNER_BANNERS_BANNERID', "Banner ID");
define('_EN_BANNER_BANNERS_BANNERTYPE', "Type");
define('_EN_BANNER_BANNERS_BANNERTYPE_AUTODETECT', "Auto Detect");
define('_EN_BANNER_BANNERS_BANNERTYPE_TEXT', "Text");
define('_EN_BANNER_BANNERS_BANNERTYPE_IMAGE', "Image");
define('_EN_BANNER_BANNERS_BANNERTYPE_FLASH', "Flash");
define('_EN_BANNER_BANNERS_THROUGH_UPLOADING', "Through uploading");
define('_EN_BANNER_BANNERS_BANNER', "Banner");
define('_EN_BANNER_BANNERS_TEMPLATE', "Template");
define('_EN_BANNER_BANNERS_LIMITATIONS', "Limitations");
define('_EN_BANNER_BANNERS_VIEWS', "Views");
define('_EN_BANNER_BANNERS_RESET_VIEWS', "Reset views counter");
define('_EN_BANNER_BANNERS_CLICKS', "Clicks");
define('_EN_BANNER_BANNERS_RESET_CLICKS', "Reset clicks counter");
define('_EN_BANNER_BANNERS_RANDOM', "Randomly");
define('_EN_BANNER_BANNERS_ADD', "Add Banner");
define('_EN_BANNER_BANNERS_EDIT', "Edit Banner");
define('_EN_BANNER_BANNERS_UPDATE', "Update Banner");
define('_EN_BANNER_BANNERS_DELETE', "Delete Banner");
define('_EN_BANNER_BANNERS_ADD_GROUPS', "Add To Groups");
define('_EN_BANNER_BANNERS_NO_SELECTION', "Please select an banner from your left");
define('_EN_BANNER_BANNERS_INCOMPLETE_FIELDS', "Some fields haven't been (correctly) filled in.");
define('_EN_BANNER_BANNERS_ALREADY_EXISTS', "There is another banner using the same title ({0}).");
define('_EN_BANNER_BANNERS_CONFIRM_DELETE', "Delete this banner?");
define('_EN_BANNER_BANNERS_CONFIRM_RESET_VIEWS', "Reset views counter for this banner?");
define('_EN_BANNER_BANNERS_CONFIRM_RESET_CLICKS', "Reset clicks counter for this banner?");

/* Banners Management Responses */
define('_EN_BANNER_BANNERS_CREATED', "Banner {0} has been created.");
define('_EN_BANNER_BANNERS_UPDATED', "Banner {0} has been updated.");
define('_EN_BANNER_BANNERS_DELETED', "Banner {0} has been deleted.");

/* Banners Management Errors */
define('_EN_BANNER_BANNERS_NOT_CREATED', "There was a problem creating banner {0}.");
define('_EN_BANNER_BANNERS_NOT_UPDATED', "There was a problem updating banner {0}.");
define('_EN_BANNER_BANNERS_CANT_DELETE', "There was a problem deleting banner {0}.");
define('_EN_BANNER_BANNERS_ERROR_DOES_NOT_EXISTS', "Banner not found.");
define('_EN_BANNER_BANNERS_ERROR_TITLE_DUPLICATE', "Banner title already exist.");
define('_EN_BANNER_ERROR_CANT_DELETE_OLD', "Can't delete old banner file ({0}).");

/* Banners Group Management */
define('_EN_BANNER_GROUPS_GROUPS', "Groups");
define('_EN_BANNER_GROUPS_GROUP', "Group");
define('_EN_BANNER_GROUPS_GROUPID', "Group ID");
define('_EN_BANNER_GROUPS_COUNT', "Count");
define('_EN_BANNER_GROUPS_ADD', "Add Group");
define('_EN_BANNER_GROUPS_DELETE', "Delete Group");
define('_EN_BANNER_GROUPS_ADD_BANNERS', "Add Banners");
define('_EN_BANNER_GROUPS_ADD_BANNER', "Add banner to group");
define('_EN_BANNER_GROUPS_NO_SELECTION', "Please select a group from your left");
define('_EN_BANNER_GROUPS_INCOMPLETE_FIELDS', "Some fields haven't been filled in.");
define('_EN_BANNER_GROUPS_CONFIRM_DELETE', "Are you sure you want to delete this group?");
define('_EN_BANNER_GROUPS_MARK_BANNERS', "Select the banners you want to add to the group");
define('_EN_BANNER_GROUPS_MEMBERS', "Banners belonging to group");
define('_EN_BANNER_GROUPS_SHOW_TITLE', "Show Title");
define('_EN_BANNER_GROUPS_SHOW_TYPE', "Show Type");
define('_EN_BANNER_GROUPS_SHOW_TYPE_0', "Simple");
define('_EN_BANNER_GROUPS_SHOW_TYPE_1', "Vertical scroll");
define('_EN_BANNER_GROUPS_SHOW_TYPE_2', "Horizontal Scroll");

/* Banners Group Management Responses*/
define('_EN_BANNER_GROUPS_CREATED', "Group {0} has been created.");
define('_EN_BANNER_GROUPS_UPDATED', "Group {0} has been updated");
define('_EN_BANNER_GROUPS_DELETED', "Group {0} has been deleted.");
define('_EN_BANNER_GROUPS_UPDATED_BANNERS', "The relations between banners and groups have been updated");

/* Banners Group Management Errors*/
define('_EN_BANNER_GROUPS_NOT_CREATED', "There was a problem creating group {0}.");
define('_EN_BANNER_GROUPS_NOT_UPDATED', "There was a problem updating group {0}.");
define('_EN_BANNER_GROUPS_CANT_DELETE', "There was a problem deleting group {0}.");
define('_EN_BANNER_GROUPS_ERROR_NOT_DELETABLE', "This group not deletable.");
define('_EN_BANNER_GROUPS_ERROR_DOES_NOT_EXISTS', "Group not found.");
define('_EN_BANNER_GROUPS_ERROR_TITLE_DUPLICATE', "Group title already exist.");

/* Banners Reports */
define('_EN_BANNER_REPORTS_REPORTS', "Reports");
define('_EN_BANNER_REPORTS_BANNERS_STATUS_ALWAYS', "Always");
define('_EN_BANNER_REPORTS_BANNERS_STATUS_RANDOM', "Random");
define('_EN_BANNER_REPORTS_BANNERS_STATUS_INVISIBLE', "Invisible");
define('_EN_BANNER_REPORTS_BANNERS_STATUS_VISIBLE', "Visible");
