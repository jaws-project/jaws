<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Forum"
 * "Last-Translatillr: Thomas Lilliesköld <thomas.lillieskold@gmail.com>"
 * "Language-Team: SV"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_SV_FORUMS_NAME', "Forum");
define('_SV_FORUMS_DESCRIPTION', "Ett enkelt forum");

/* ACLs */
define('_SV_FORUMS_ACL_ADDFORUM', "Lägg till forum");
define('_SV_FORUMS_ACL_EDITFORUM', "Ändra forum");
define('_SV_FORUMS_ACL_LOCKFORUM', "Lås forum");
define('_SV_FORUMS_ACL_DELETEFORUM', "Radera forum");
define('_SV_FORUMS_ACL_ADDTOPIC', "Lägg till ämne");
define('_SV_FORUMS_ACL_EDITTOPIC', "Ändra ämne");
define('_SV_FORUMS_ACL_MOVETOPIC', "Flytta ämne");
define('_SV_FORUMS_ACL_EDITOTHERSTOPIC', "Ändra andras ämne");
define('_SV_FORUMS_ACL_EDITLOCKEDTOPIC', "Ändra låst ämne");
define('_SV_FORUMS_ACL_EDITOUTDATEDTOPIC', "Ändra utdaterat ämne");
define('_SV_FORUMS_ACL_LOCKTOPIC', "Lås ämne");
define('_SV_FORUMS_ACL_DELETETOPIC', "Radera ämne");
define('_SV_FORUMS_ACL_DELETEOTHERSTOPIC', "Radera andras ämne");
define('_SV_FORUMS_ACL_DELETEOUTDATEDTOPIC', "Radera utdaterat ämne");
define('_SV_FORUMS_ACL_ADDPOST', "Lägg till inlägg");
define('_SV_FORUMS_ACL_ADDPOSTATTACHMENT', "Lägg till bifogad fil till inlägg");
define('_SV_FORUMS_ACL_ADDPOSTTOLOCKEDTOPIC', "Lägg till inlägg till låst ämne");
define('_SV_FORUMS_ACL_EDITPOST', "Ändra inlägg");
define('_SV_FORUMS_ACL_EDITOTHERSPOST', "Ändra andras inlägg");
define('_SV_FORUMS_ACL_EDITPOSTINLOCKEDTOPIC', "Ändra inlägg i låst ämne");
define('_SV_FORUMS_ACL_EDITOUTDATEDPOST', "Ändra utdaterat inlägg");
define('_SV_FORUMS_ACL_DELETEPOST', "Radera inlägg");
define('_SV_FORUMS_ACL_DELETEOTHERSPOST', "Radera andras inlägg");
define('_SV_FORUMS_ACL_DELETEOUTDATEDPOST', "Radera utdaterat inlägg");
define('_SV_FORUMS_ACL_DELETEPOSTINLOCKEDTOPIC', "Radera inlägg i låst ämne");

/* Layout */
define('_SV_FORUMS_LAYOUT_RECENT_POSTS', "Senaste foruminlägg");
define('_SV_FORUMS_LAYOUT_RECENT_POSTS_DESC', "Visa nyliga inlägg med valda alternativ");

/* Commons */
define('_SV_FORUMS_GROUP', "Grupp");
define('_SV_FORUMS_GROUPS', "Grupper");
define('_SV_FORUMS_GROUPS_ALL', "Alla grupper");
define('_SV_FORUMS_FORUM', "Forum");
define('_SV_FORUMS_FORUMS', "Forum");
define('_SV_FORUMS_TOPIC', "Ämne");
define('_SV_FORUMS_TOPICS', "Ämnen");
define('_SV_FORUMS_POST', "Inlägg");
define('_SV_FORUMS_POSTS', "Inlägg");
define('_SV_FORUMS_ORDER', "Ordning");
define('_SV_FORUMS_FASTURL', "Snabblänk");
define('_SV_FORUMS_VIEWS', "Vyer");
define('_SV_FORUMS_REPLIES', "Svar");
define('_SV_FORUMS_LOCKED', "Låst");
define('_SV_FORUMS_POSTEDBY', "Inlagd av");
define('_SV_FORUMS_LASTPOST', "Senaste inlägg");

/* Forum */
define('_SV_FORUMS_TREE_TITLE', "Forumträd");
define('_SV_FORUMS_GROUP_NEW', "Ny grupp");
define('_SV_FORUMS_GROUP_EDIT', "Ändra grupp");
define('_SV_FORUMS_FORUM_NEW', "Nytt forum");
define('_SV_FORUMS_FORUM_EDIT', "Ändra forum");
define('_SV_FORUMS_NOTICE_FORUM_CREATED', "Ett nytt forum har skapats");
define('_SV_FORUMS_NOTICE_GROUP_CREATED', "En ny grupp har skapats");
define('_SV_FORUMS_NOTICE_FORUM_UPDATED', "Forumet har uppdaterats");
define('_SV_FORUMS_NOTICE_GROUP_UPDATED', "Gruppen har uppdaterats");
define('_SV_FORUMS_NOTICE_FORUM_DELETED', "Forumet har raderats");
define('_SV_FORUMS_NOTICE_GROUP_DELETED', "Gruppen har raderats");
define('_SV_FORUMS_ERROR_FORUM_NOT_EMPTY', "Forumet är inte tomt");
define('_SV_FORUMS_ERROR_GROUP_NOT_EMPTY', "Gruppen är inte tom");
define('_SV_FORUMS_CONFIRM_DELETE_FORUM', "Vill du verkligen radera forum (%s%)?");
define('_SV_FORUMS_CONFIRM_DELETE_GROUP', "Vill du verkligen radera grupp (%s%)?");

/* Ämnes */
define('_SV_FORUMS_TOPICS_SUBJECT', "Ämne");
define('_SV_FORUMS_TOPICS_MOVEDTO', "Flyttad till");
define('_SV_FORUMS_TOPICS_NEW', "Nytt ämne");
define('_SV_FORUMS_TOPICS_NEW_TITLE', "Nytt ämne");
define('_SV_FORUMS_TOPICS_NEW_BUTTON', "Lägg till nytt ämne");
define('_SV_FORUMS_TOPICS_NEW_ERROR', "Ett problem uppstod när nytt ämne skulle läggas till");
define('_SV_FORUMS_TOPICS_NEW_NOTIFICATION_SUBJECT', "Nytt ämne inlagt i forum - {0}");
define('_SV_FORUMS_TOPICS_NEW_NOTIFICATION_MESSAGE', "Följande ämne lades till i forumet av {0}.");
define('_SV_FORUMS_TOPICS_EDIT', "Ändra ämne");
define('_SV_FORUMS_TOPICS_EDIT_TITLE', "Ändra ämne");
define('_SV_FORUMS_TOPICS_EDIT_BUTTON', "Uppdatera ämne");
define('_SV_FORUMS_TOPICS_EDIT_ERROR', "Ett problem uppstod när ämnet skulle uppdateras");
define('_SV_FORUMS_TOPICS_EDIT_NOTIFICATION_SUBJECT', "Forumämne ändrat - {0}");
define('_SV_FORUMS_TOPICS_EDIT_NOTIFICATION_MESSAGE', "Följande ämne ändrades i forumet av {0}.");
define('_SV_FORUMS_TOPICS_MOVE_NOTIFICATION_SUBJECT', "Forumämne flyttat - {0}");
define('_SV_FORUMS_TOPICS_MOVE_NOTIFICATION_MESSAGE', "Följande ämne flyttades i forumet av {0}.");
define('_SV_FORUMS_TOPICS_DELETE', "Radera ämne");
define('_SV_FORUMS_TOPICS_DELETE_TITLE', "Radera ämne");
define('_SV_FORUMS_TOPICS_DELETE_BUTTON', "Radera ämne");
define('_SV_FORUMS_TOPICS_DELETE_ERROR', "Ett problem uppstod när ämnet skulle raderas");
define('_SV_FORUMS_TOPICS_DELETE_NOTIFICATION_SUBJECT', "Forumämne raderat - {0}");
define('_SV_FORUMS_TOPICS_DELETE_NOTIFICATION_MESSAGE', "Följande ämne raderades i forumet av {0}.");
define('_SV_FORUMS_TOPICS_LOCK', "Lås Ämne");
define('_SV_FORUMS_TOPICS_LOCK_NOTIFICATION_SUBJECT', "Forumämne låst - {0}");
define('_SV_FORUMS_TOPICS_LOCK_NOTIFICATION_MESSAGE', "Följande ämne låstes i forumet av {0}.");
define('_SV_FORUMS_TOPICS_UNLOCK', "Lås upp Ämne");
define('_SV_FORUMS_TOPICS_UNLOCK_NOTIFICATION_SUBJECT', "Forumämne upplåst - {0}");
define('_SV_FORUMS_TOPICS_UNLOCK_NOTIFICATION_MESSAGE', "Följande ämne låstes upp i forumet av {0}.");
define('_SV_FORUMS_TOPICS_COUNT', "{0} ämnen");

/* Inlägg */
define('_SV_FORUMS_POSTS_NEW', "Nytt inlägg");
define('_SV_FORUMS_POSTS_NEW_TITLE', "Nytt inlägg");
define('_SV_FORUMS_POSTS_NEW_BUTTON', "Lägg till nytt inlägg");
define('_SV_FORUMS_POSTS_NEW_ERROR', "Ett problem uppstod när nytt inlägg skulle läggas till");
define('_SV_FORUMS_POSTS_NEW_NOTIFICATION_SUBJECT', "Nytt inlägg i forumsämnet - {0}");
define('_SV_FORUMS_POSTS_NEW_NOTIFICATION_MESSAGE', "Följande inlägg lades till i ämnet av {0}.");
define('_SV_FORUMS_POSTS_EDIT', "Ändra inlägg");
define('_SV_FORUMS_POSTS_EDIT_TITLE', "Ändra inlägg");
define('_SV_FORUMS_POSTS_EDIT_BUTTON', "Uppdatera inlägg");
define('_SV_FORUMS_POSTS_EDIT_ERROR', "Ett problem uppstod när inlägget skulle uppdateras");
define('_SV_FORUMS_POSTS_EDIT_NOTIFICATION_SUBJECT', "Foruminlägg ändrat - {0}");
define('_SV_FORUMS_POSTS_EDIT_NOTIFICATION_MESSAGE', "Följande inlägg ändrades i ämnet av {0}.");
define('_SV_FORUMS_POSTS_REPLY', "Svara på inlägg");
define('_SV_FORUMS_POSTS_REPLY_TITLE', "Svara på inlägg");
define('_SV_FORUMS_POSTS_DELETE', "Radera Inlägg");
define('_SV_FORUMS_POSTS_DELETE_TITLE', "Radera inlägg");
define('_SV_FORUMS_POSTS_DELETE_BUTTON', "Radera inlägg");
define('_SV_FORUMS_POSTS_DELETE_ERROR', "Ett problem uppstod när inlägg skulle raderas");
define('_SV_FORUMS_POSTS_DELETE_NOTIFICATION_SUBJECT', "Foruminlägg raderat - {0}");
define('_SV_FORUMS_POSTS_DELETE_NOTIFICATION_MESSAGE', "Ett inlägg raderades i ämnet av {0}.");
define('_SV_FORUMS_POSTS_MESSAGE', "Meddelande");
define('_SV_FORUMS_POSTS_ATTACHMENT', "Bifogad fil");
define('_SV_FORUMS_POSTS_ATTACHMENT_HITS', "Nedladdad {0} ggr");
define('_SV_FORUMS_POSTS_ATTACHMENT_REMOVE', "Ta bort bifogad fil");
define('_SV_FORUMS_POSTS_EDIT_REASON', "Ändra orsak");
define('_SV_FORUMS_POSTS_UPDATEDBY', "Uppdaterad av");
define('_SV_FORUMS_POSTS_COUNT', "{0} inlägg");

/* Users */
define('_SV_FORUMS_USERS_POSTS_COUNT', "Räkna inlägg");
define('_SV_FORUMS_USERS_REGISTERED_DATE', "Registreringsdatum");
define('_SV_FORUMS_USER_POSTS', "{0}'s inlägg");

