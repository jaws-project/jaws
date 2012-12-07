<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Poll"
 * "Last-Translator: Christian Schmitt <christian@idkfa.de>, C.Tuemer <info@exceptionz.net>"
 * "Language-Team: DE"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_DE_POLL_NAME', "Umfrage");
define('_DE_POLL_DESCRIPTION', "Ein Umfragesystem, bei dem Besucher ihre Stimme abgeben können");
define('_DE_POLL_ACTION_POLL_TITLE', "Umfrage");
define('_DE_POLL_ACTION_POLLS_TITLE', "Liste der Umfragen");
define('_DE_POLL_ACTION_RESULT_TITLE', "Ergebnisse");
define('_DE_POLL_ACTION_POLLS_INGROUP_TITLE', "{0} Umfrageliste");
define('_DE_POLL_ACL_DEFAULT', "Umfragen verwalten");
define('_DE_POLL_ACL_MANAGEPOLLS', "Umfrage Hinzufügen/Bearbeiten");
define('_DE_POLL_ACL_MANAGEGROUPS', "Umfragegruppe Hinzufügen/Bearbeiten");
define('_DE_POLL_ACL_VIEWREPORTS', "Berichte Anzeigen");
define('_DE_POLL_POLLS', "Umfragen");
define('_DE_POLL_POLLS_QUESTION', "Frage");
define('_DE_POLL_POLLS_TYPE', "Typ");
define('_DE_POLL_POLLS_TYPE_COOKIE', "Cookiebasiert");
define('_DE_POLL_POLLS_TYPE_FREE', "Nicht limitiert");
define('_DE_POLL_POLLS_SELECT_TYPE', "Typ auswählen");
define('_DE_POLL_POLLS_SELECT_SINGLE', "Einzelauswahl");
define('_DE_POLL_POLLS_SELECT_MULTI', "Mehrfachauswahl");
define('_DE_POLL_POLLS_RESULT_VIEW', "Ergebnisanzeige");
define('_DE_POLL_POLLS_ANSWER', "Antwort");
define('_DE_POLL_POLLS_ANSWERS', "Antworten");
define('_DE_POLL_POLLS_ADD', "Umfragen hinzufügen");
define('_DE_POLL_POLLS_ADD_TITLE', "Umfrage hinzufügen");
define('_DE_POLL_POLLS_EDIT_TITLE', "Umfrage bearbeiten");
define('_DE_POLL_POLLS_ANSWERS_TITLE', "Antworten bearbeiten");
define('_DE_POLL_POLLS_CONFIRM_DELETE', "Diese Umfrage löschen?");
define('_DE_POLL_POLLS_INCOMPLETE_FIELDS', "Einige Felder wurden nicht (korrekt) ausgefüllt.");
define('_DE_POLL_GROUPS', "Umfragegruppen");
define('_DE_POLL_GROUPS_ADD', "Gruppen hinzufügen");
define('_DE_POLL_GROUPS_ADD_TITLE', "Gruppe hinzufügen");
define('_DE_POLL_GROUPS_EDIT_TITLE', "Gruppe bearbeiten");
define('_DE_POLL_GROUPS_POLLS_TITLE', "Umfragen gruppieren");
define('_DE_POLL_GROUPS_CONFIRM_DELETE', "Wollen sie diese Gruppe wirklich löschen?");
define('_DE_POLL_REPORTS', "Berichte");
define('_DE_POLL_REPORTS_VOTE', "Stimmen");
define('_DE_POLL_REPORTS_RESULTS', "Ergebnisse");
define('_DE_POLL_REPORTS_PERCENT', "{0}%");
define('_DE_POLL_REPORTS_TOTAL_VOTES', "Anzahl der Stimmen");
define('_DE_POLL_LAYOUT_DISPLAY_LAST', "Letzte Umfrage anzeigen");
define('_DE_POLL_LAYOUT_DISPLAY_LAST_DESC', "Zeigt die letzte Umfrage an");
define('_DE_POLL_LAYOUT_LIST_POLLS', "Umfragen anzeigen");
define('_DE_POLL_LAYOUT_LIST_POLLS_DESC', "Zeigt eine Liste aller bisheriger Umfragen");
define('_DE_POLL_LAYOUT_LIST_INGROUP_POLLS_DESC', "Zeige eine Liste aller aktiven Umfragen in dieser Kategorie");
define('_DE_POLL_VOTE', "Stimmen");
define('_DE_POLL_THANKS', "Vielen Dank für Ihre Teilnahme");
define('_DE_POLL_ALREADY_VOTED', "Sie haben bereits an dieser Umfrage teilgenommen");
define('_DE_POLL_RESULT_DISABLED', "Umfrageergebnisse wurden deaktiviert");
define('_DE_POLL_POLLS_ADDED', "Die Umfrage wurde erstellt");
define('_DE_POLL_POLLS_UPDATED', "Die Umfrage wurde aktualisiert");
define('_DE_POLL_POLLS_DELETED', "Die Umfrage wurde gelöscht");
define('_DE_POLL_GROUPS_CREATED', "Die Gruppe {0} wurde erstellt.");
define('_DE_POLL_GROUPS_UPDATED', "Die Gruppe {0} wurde aktualisiert.");
define('_DE_POLL_GROUPS_DELETED', "Die Gruppe {0} wurde gelöscht.");
define('_DE_POLL_GROUPS_UPDATED_POLLS', "Die Beziehungen zwischen Umfragen und Gruppen wurden aktualisiert");
define('_DE_POLL_ANSWERS_UPDATED', "Antworten wurden aktualisiert");
define('_DE_POLL_ERROR_POLL_NOT_ADDED', "Die Umfrage konnte nicht hinzugefügt werden");
define('_DE_POLL_ERROR_POLL_NOT_UPDATED', "Die Umfrage konnte nicht aktualisiert werden");
define('_DE_POLL_ERROR_POLL_NOT_DELETED', "Es trat ein Fehler beim Löschen der Umfrage auf");
define('_DE_POLL_ERROR_GROUP_NOT_ADDED', "Es trat ein Fehler beim Erstellen der Gruppe {0} auf.");
define('_DE_POLL_ERROR_GROUP_NOT_UPDATED', "Es trat ein Fehler beim Aktualisieren der Gruppe {0} auf.");
define('_DE_POLL_ERROR_GROUP_NOT_DELETED', "Es trat ein Fehler beim Löschen der Gruppe {0} auf.");
define('_DE_POLL_ERROR_GROUP_DOES_NOT_EXISTS', "Die Gruppe wurde nicht gefunden.");
define('_DE_POLL_ERROR_GROUP_TITLE_DUPLICATE', "Der Gruppentitel existiert bereits.");
define('_DE_POLL_ERROR_REQUIRES_TWO_ANSWERS', "Mindestens zwei Antworten");
define('_DE_POLL_ERROR_ANSWERS_NOT_UPDATED', "Es trat ein Fehler beim Aktualisieren der Antworten auf.");
define('_DE_POLL_ERROR_ANSWER_NOT_DELETED', "Die Antwort konnte nicht gelöscht werden");
define('_DE_POLL_ERROR_ANSWER_NOT_UPDATED', "Die Antwort konnte nicht aktualisiert werden");
define('_DE_POLL_ERROR_EXCEPTION_ANSWER_NOT_DELETED', "Die Antwort kann nicht gelöscht werden während die Umfrage gelöscht wird");
define('_DE_POLL_ERROR_ANSWER_NOT_ADDED', "Die Antwort konnte nicht zur Umfrage hinzugefügt werden");
define('_DE_POLL_ERROR_VOTE_NOT_ADDED', "Die Umfrage konnte nicht gelöscht werden");
