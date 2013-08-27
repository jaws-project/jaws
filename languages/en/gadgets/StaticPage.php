<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: StaticPage"
 * "Last-Translator: "
 * "Language-Team: EN"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_EN_STATICPAGE_NAME', "Static Page");
define('_EN_STATICPAGE_DESCRIPTION', "A gadget to create and manage static content.");

// Actions
define('_EN_STATICPAGE_ACTION_DEFAULT', "Display the default page.");
define('_EN_STATICPAGE_ACTION_INDEX', "Display an index of static pages.");
define('_EN_STATICPAGE_ACTION_PAGE', "Display a single page.");

// Layout elements
define('_EN_STATICPAGE_ACTIONS_PAGESLIST', "Pages List");
define('_EN_STATICPAGE_ACTIONS_PAGESLIST_DESC', "Lists the available static pages.");
define('_EN_STATICPAGE_ACTIONS_GROUPSLIST', "Groups List");
define('_EN_STATICPAGE_ACTIONS_GROUPSLIST_DESC', "Lists the available groups of static pages.");
define('_EN_STATICPAGE_ACTIONS_GROUPPAGES', "Group pages");
define('_EN_STATICPAGE_ACTIONS_GROUPPAGES_DESC', "Lists the available static pages in the this group");

// ACLs
define('_EN_STATICPAGE_ACL_DEFAULT', "Adminstrate Static Pages");
define('_EN_STATICPAGE_ACL_ADDPAGE', "Add Pages");
define('_EN_STATICPAGE_ACL_EDITPAGE', "Edit Pages");
define('_EN_STATICPAGE_ACL_DELETEPAGE', "Delete Pages");
define('_EN_STATICPAGE_ACL_PUBLISHPAGES', "Publish Pages");
define('_EN_STATICPAGE_ACL_MANAGEPUBLISHEDPAGES', "Manage Published Pages");
define('_EN_STATICPAGE_ACL_MODIFYOTHERSPAGES', "Modify other's pages");
define('_EN_STATICPAGE_ACL_MANAGEGROUPS', "Manage Groups");
define('_EN_STATICPAGE_ACL_PROPERTIES', "Configure settings");

// Errors
define('_EN_STATICPAGE_ERROR_PAGE_NOT_ADDED', "There was a problem adding the page.");
define('_EN_STATICPAGE_ERROR_PAGE_NOT_UPDATED', "There was a problem updating the page.");
define('_EN_STATICPAGE_ERROR_PAGE_NOT_DELETED', "There was a problem deleting the page.");
define('_EN_STATICPAGE_ERROR_PAGE_NOT_FOUND', "The page you requested could not be found.");
define('_EN_STATICPAGE_ERROR_UNKNOWN_COLUMN', "An unknown sort column was provided.");
///FIXME they should not have the same translation.
define('_EN_STATICPAGE_ERROR_PAGES_NOT_RETRIEVED', "The page index could not be loaded.");
define('_EN_STATICPAGE_ERROR_PAGE_NOT_MASSIVE_DELETED', "There was a problem while deleting the group of pages");
define('_EN_STATICPAGE_ERROR_SETTINGS_NOT_SAVED', "There was a problem while saving the settings");
define('_EN_STATICPAGE_ERROR_LANGUAGE_NOT_EXISTS', "Language {0} is not valid");
define('_EN_STATICPAGE_ERROR_TRANSLATION_EXISTS', "A translation to {0} already exists");
define('_EN_STATICPAGE_ERROR_TRANSLATION_NOT_ADDED', "There was a problem adding the page translation");
define('_EN_STATICPAGE_ERROR_TRANSLATION_NOT_UPDATED', "There was a problem updating the page translation");
define('_EN_STATICPAGE_ERROR_TRANSLATION_NOT_DELETED', "There was a problem deleting the page translation");
define('_EN_STATICPAGE_ERROR_TRANSLATION_NOT_EXISTS', "The translation does not exists");
define('_EN_STATICPAGE_ERROR_GROUP_NOT_DELETABLE', "This group not deletable.");

// Strings
define('_EN_STATICPAGE_PAGES_LIST', "Page Index");
define('_EN_STATICPAGE_PAGES_TREE', "Pages tree");
define('_EN_STATICPAGE_TITLE_NOT_FOUND', "Page Not Found");
define('_EN_STATICPAGE_CONTENT_NOT_FOUND', "The page you requested could not be found, please contact the site administrator if you believe it should exist.");
define('_EN_STATICPAGE_MENU_PAGES', "Manage Pages");
define('_EN_STATICPAGE_MENU_ADDPAGE', "Add Page");
define('_EN_STATICPAGE_LAST_UPDATE', "Last Update");
define('_EN_STATICPAGE_NO_PAGES', "No pages were found.");
define('_EN_STATICPAGE_PAGE_CREATED', "The page has been created.");
define('_EN_STATICPAGE_PAGE_UPDATED', "The page has been updated.");
define('_EN_STATICPAGE_PAGE_DELETED', "The page has been deleted.");
define('_EN_STATICPAGE_SETTINGS_SAVED', "The settings have been saved");
define('_EN_STATICPAGE_PAGE_MASSIVE_DELETED', "The group of pages have been deleted");
define('_EN_STATICPAGE_PAGE_AUTOUPDATED', "The page has been auto saved");
define('_EN_STATICPAGE_ADD_PAGE', "Add Page");
define('_EN_STATICPAGE_UPDATE_PAGE', "Update Page");
define('_EN_STATICPAGE_PAGE', "Page");
define('_EN_STATICPAGE_CONFIRM_DELETE_PAGE', "Are you sure you want to delete this page?");
define('_EN_STATICPAGE_CONFIRM_MASIVE_DELETE_PAGE', "Are you sure you want to delete selected pages?");
define('_EN_STATICPAGE_FASTURL', "Fast URL");
define('_EN_STATICPAGE_DRAFT', "Draft");
define('_EN_STATICPAGE_PUBLISHED', "Published");
define('_EN_STATICPAGE_STATUS', "Status");
define('_EN_STATICPAGE_ORDERBY', "Order by");
define('_EN_STATICPAGE_SHOW_TITLE', "Show title");
define('_EN_STATICPAGE_DEFAULT_PAGE', "Default page");
define('_EN_STATICPAGE_DEFAULT_LANGUAGE', "Default language");
define('_EN_STATICPAGE_PAGE_LANGUAGE', "Language page");
define('_EN_STATICPAGE_USE_MULTILANGUAGE', "Use multilanguage pages");
define('_EN_STATICPAGE_PAGE_TRANSLATION', "Translation");
define('_EN_STATICPAGE_ADD_LANGUAGE', "New");
define('_EN_STATICPAGE_ADD_TRANSLATION', "Add translation");
define('_EN_STATICPAGE_UPDATE_TRANSLATION', "Update translation");
define('_EN_STATICPAGE_TRANSLATION_CREATED', "The translation has been created.");
define('_EN_STATICPAGE_TRANSLATION_UPDATED', "The translation has been updated.");
define('_EN_STATICPAGE_TRANSLATION_DELETED', "The translation has been deleted.");
define('_EN_STATICPAGE_AVAIL_TRANSLATIONS', 'Available translations');
define('_EN_STATICPAGE_ADVANCED_OPTIONS', 'Advanced >');

// Groups
define('_EN_STATICPAGE_GROUPS', 'Groups');
define('_EN_STATICPAGE_GROUP', 'Group');
define('_EN_STATICPAGE_GROUP_ADD', 'Add new group');
define('_EN_STATICPAGE_GROUP_EDIT', 'Edit group');
define('_EN_STATICPAGE_GROUPS_LIST', 'Group Index');
define('_EN_STATICPAGE_GROUP_CONFIRM_DELETE', 'Are you sure you want to delete the group?');
define('_EN_STATICPAGE_GROUP_INCOMPLETE_FIELDS', 'Some fields are not complete.');
define('_EN_STATICPAGE_NOTICE_GROUP_CREATED', 'The group was created successfully.');
define('_EN_STATICPAGE_NOTICE_GROUP_UPDATED', 'The group was updated successfully.');
define('_EN_STATICPAGE_NOTICE_GROUP_DELETED', 'The group was deleted successfully.');
