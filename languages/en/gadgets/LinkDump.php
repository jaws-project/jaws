<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: LinkDump"
 * "Last-Translator: Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */
define('_EN_LINKDUMP_NAME', "Link Dump");
define('_EN_LINKDUMP_DESCRIPTION', "Easily link to things around the net");

// ACL Keys
define('_EN_LINKDUMP_ACL_DEFAULT', "Administration");
define('_EN_LINKDUMP_ACL_MANAGELINKS', "Links management");
define('_EN_LINKDUMP_ACL_MANAGEGROUPS', "Groups management");
define('_EN_LINKDUMP_ACL_MANAGETAGS', "Tags management");
define('_EN_LINKDUMP_ACL_UPDATEPROPERTIES', "Update properties");

/* Layout */
define('_EN_LINKDUMP_LAYOUT_DISPLAY', "Link dump");
define('_EN_LINKDUMP_LAYOUT_DISPLAY_DESCRIPTION', "Show links with the group's limitation count");
define('_EN_LINKDUMP_LAYOUT_CATEGORIES', "Categories");
define('_EN_LINKDUMP_LAYOUT_CATEGORIES_DESCRIPTION', "Show list of links's categories");
define('_EN_LINKDUMP_LAYOUT_TAG_CLOUD', "Tag Cloud");
define('_EN_LINKDUMP_LAYOUT_TAG_CLOUD_DESCRIPTION', "Generated a cloud of your links tags");

/* Common */
define('_EN_LINKDUMP_FASTURL', "Fast URL");
define('_EN_LINKDUMP_RANK', "Rank");

/* Links */
define('_EN_LINKDUMP_LINKS_TITLE', "Links's Tree");
define('_EN_LINKDUMP_LINKS_ADD', "Add Link");
define('_EN_LINKDUMP_LINKS_EDIT', "Edit Link");
define('_EN_LINKDUMP_LINKS_DELETE', "Delete Link");
define('_EN_LINKDUMP_LINKS_TAGS', "Tags");
define('_EN_LINKDUMP_LINKS_ARCHIVE', "Links Archive");
define('_EN_LINKDUMP_LINKS_FEED', "Feed");
define('_EN_LINKDUMP_LINKS_CLICKS', "Clicks");
define('_EN_LINKDUMP_LINKS_TAGCLOUD', "Link Dump Tag Cloud");
define('_EN_LINKDUMP_LINKS_TAG_ARCHIVE', "Tag Archive - {0}");
define('_EN_LINKDUMP_LINKS_NOEXISTS', "No link exists.");
define('_EN_LINKDUMP_LINKS_DELETE_CONFIRM', "Are you sure you want to delete link (%s%)?");
define('_EN_LINKDUMP_INCOMPLETE_FIELDS', "Some fields haven't been (correctly) filled in.");

/* Groups */
define('_EN_LINKDUMP_GROUPS', "Links's groups");
define('_EN_LINKDUMP_GROUPS_GROUP', "Group");
define('_EN_LINKDUMP_GROUPS_ADD', "Add Group");
define('_EN_LINKDUMP_GROUPS_EDIT', "Edit Group");
define('_EN_LINKDUMP_GROUPS_DELETE', "Delete Group");
define('_EN_LINKDUMP_GROUPS_LIMIT_COUNT', "Viewable Count");
define('_EN_LINKDUMP_GROUPS_LINKS_TYPE', "Links Type");
define('_EN_LINKDUMP_GROUPS_LINKS_TYPE_NOLINK', "No link");
define('_EN_LINKDUMP_GROUPS_LINKS_TYPE_RAWLINK', "Direct link");
define('_EN_LINKDUMP_GROUPS_LINKS_TYPE_MAPPED', "Mapped link");
define('_EN_LINKDUMP_GROUPS_ORDER_TYPE', "Order Type");
define('_EN_LINKDUMP_GROUPS_ORDER_BY_RANK', "by Rank ↓");
define('_EN_LINKDUMP_GROUPS_ORDER_BY_ID', "by ID ↓");
define('_EN_LINKDUMP_GROUPS_ORDER_BY_TITLE', "by Title ↓");
define('_EN_LINKDUMP_GROUPS_ORDER_BY_CLICKS', "by Clicks ↑");
define('_EN_LINKDUMP_GROUPS_DELETE_CONFIRM', "Are you sure you want to delete group (%s%) and all of its links?");

// Responses
define('_EN_LINKDUMP_LINKS_ADDED', "Link was successfully added");
define('_EN_LINKDUMP_LINKS_ADD_ERROR', "Failed to add new link");
define('_EN_LINKDUMP_LINKS_ADD_TAG_ERROR', "Failed to add new tag");
define('_EN_LINKDUMP_LINKS_UPDATED', "Link was successfully updated");
define('_EN_LINKDUMP_LINKS_UPDATE_ERROR', "Failed to update the link");
define('_EN_LINKDUMP_LINKS_UPDATE_TAG_ERROR', "Failed to update tags");
define('_EN_LINKDUMP_LINKS_REPLACED', "Link was successfully replaced");
define('_EN_LINKDUMP_LINKS_DELETED', "Link was successfully deleted");
define('_EN_LINKDUMP_LINKS_DELETE_ERROR', "Failed to delete the link");
define('_EN_LINKDUMP_LINKS_DELETE_TAG_ERROR', "Failed to delete tags");
define('_EN_LINKDUMP_LINKS_NOT_EXISTS', "Link does not exist");

define('_EN_LINKDUMP_GROUPS_ADDED', "A new group has been created.");
define('_EN_LINKDUMP_GROUPS_ADD_ERROR', "Failed to create new group");
define('_EN_LINKDUMP_GROUPS_UPDATED', "Group was successfully updated");
define('_EN_LINKDUMP_GROUPS_UPDATE_ERROR', "Failed to update the group");
define('_EN_LINKDUMP_GROUPS_DELETED', "Group was successfully deleted");
define('_EN_LINKDUMP_GROUPS_DELETE_ERROR', "Failed to delete the group");
define('_EN_LINKDUMP_GROUPS_NOT_DELETABLE', "This group not deletable.");
define('_EN_LINKDUMP_GROUPS_NOT_EXISTS', "Group does not exist");