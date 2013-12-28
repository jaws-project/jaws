<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: ian <ianthelazy[at]gmail[dot]com>"
 * "Language-Team: FR"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_FR_UPGRADE_INTRODUCTION', "Introduction");
define('_FR_UPGRADE_AUTHENTICATION', "Identification");
define('_FR_UPGRADE_REQUIREMENTS', "Éléments requis");
define('_FR_UPGRADE_DATABASE', "Base de données");
define('_FR_UPGRADE_REPORT', "Rapport");
define('_FR_UPGRADE_VER_TO_VER', "{0} to {1}");
define('_FR_UPGRADE_SETTINGS', "Préférences");
define('_FR_UPGRADE_WRITECONFIG', "Sauvegarder la configuration");
define('_FR_UPGRADE_FINISHED', "Terminé");
define('_FR_UPGRADE_INTRO_WELCOME', "Bienvenu dans la mise à jour de Jaws");
define('_FR_UPGRADE_INTRO_UPGRADER', "En utilisant la mise à jour, vous pouvez actualiser une ancienne version pour la nouvelle version. Soyez juste sûr d'avoir les éléments suivant disponibles");
define('_FR_UPGRADE_INTRO_DATABASE', "Informations de la base de données - hôte, utilisateur, mot de passe, nom de la base de données.");
define('_FR_UPGRADE_INTRO_FTP', "Une façon d'envoyer des fichiers, probablement par FTP.");
define('_FR_UPGRADE_INTRO_LOG', "Enregistrer le processus de mise à jour (et les erreurs) dans un fichier journal ({0})");
define('_FR_UPGRADE_INTRO_LOG_ERROR', "Note: si vous voulez enregistrer le processus d'installation dans un fichier journal, vous devez d'abord donner les droits en écriture au répertoire ({0}) et rafraichir cette page");
define('_FR_UPGRADE_AUTH_PATH_INFO', "Pour être sûr que vous êtes le propriétaire de ce site, créez un fichier nommé <strong>{0}</strong> dans le répertoire de mise à jour de Jaws (<strong>{1}</strong>).");
define('_FR_UPGRADE_AUTH_UPLOAD', "Vous pouvez transférer le fichier de la même façon que vous avez transféré Jaws.");
define('_FR_UPGRADE_AUTH_KEY_INFO', "Le fichier doit contenir le code ci-dessous, et rien d'autre.");
define('_FR_UPGRADE_AUTH_ENABLE_SECURITY', "Activer la mise à jour sécurisée (Propulsé par RSA)");
define('_FR_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Erreur lors de la génération de la clé RSA. Réessayez.");
define('_FR_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Erreur lors de la génération de la clé RSA. Pas d'extension mathématique disponible.");
define('_FR_UPGRADE_AUTH_ERROR_KEY_FILE', "Le fichier contenant la clé ({0}) n'a pas été trouvé, vérifiez qu'il est bien créé, et qu'il est accessible en lecture.");
define('_FR_UPGRADE_AUTH_ERROR_KEY_MATCH', "La clé trouvée ({0}), ne correspond pas avec celle ci-dessous, vérifiez que vous avez correctement entré la clé.");
define('_FR_UPGRADE_REQ_REQUIREMENT', "Requis");
define('_FR_UPGRADE_REQ_OPTIONAL', "Optionnel mais recommandé");
define('_FR_UPGRADE_REQ_RECOMMENDED', "Recommandé");
define('_FR_UPGRADE_REQ_DIRECTIVE', "Directive");
define('_FR_UPGRADE_REQ_ACTUAL', "Actuel");
define('_FR_UPGRADE_REQ_RESULT', "Résultat");
define('_FR_UPGRADE_REQ_PHP_VERSION', "Version de PHP");
define('_FR_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_FR_UPGRADE_REQ_DIRECTORY', "{0} répertoire");
define('_FR_UPGRADE_REQ_EXTENSION', "{0} extension");
define('_FR_UPGRADE_REQ_FILE_UPLOAD', "Transfert de fichiers");
define('_FR_UPGRADE_REQ_SAFE_MODE', "Safe mode");
define('_FR_UPGRADE_REQ_READABLE', "Accessible en lecture");
define('_FR_UPGRADE_REQ_WRITABLE', "Accessible en écriture");
define('_FR_UPGRADE_REQ_OK', "OK");
define('_FR_UPGRADE_REQ_BAD', "Erreur");
define('_FR_UPGRADE_REQ_OFF', "Off");
define('_FR_UPGRADE_REQ_ON', "On");
define('_FR_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "Le répertoire {0} n'est pas accessible ni en lecture ni en écriture, veuillez corriger les permissions.");
define('_FR_UPGRADE_REQ_RESPONSE_PHP_VERSION', "La version minimum de PHP pour mettre à jour Jaws est {0}, vous devez donc mettre à jour votre version de PHP.");
define('_FR_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Les répertoires listés ci-dessous {0} ne sont pas accessible ni en lecture ni en écriture, veuillez corriger les permissions.");
define('_FR_UPGRADE_REQ_RESPONSE_EXTENSION', "L'extension {0} est nécessaire pour utiliser Jaws.");
define('_FR_UPGRADE_DB_INFO', "Vous devez maintenant configurer votre base de données, ");
define('_FR_UPGRADE_DB_HOST', "Hôte");
define('_FR_UPGRADE_DB_HOST_INFO', "Si vous ne connaissez pas ça, c'est probablement plus sûr de le laisser à {0}.");
define('_FR_UPGRADE_DB_DRIVER', "Driver");
define('_FR_UPGRADE_DB_USER', "Utilisateur");
define('_FR_UPGRADE_DB_PASS', "Mot de passe");
define('_FR_UPGRADE_DB_IS_ADMIN', "est l'Admin ?");
define('_FR_UPGRADE_DB_NAME', "Nom de la base de données");
define('_FR_UPGRADE_DB_PATH', "Chemin de la base de données");
define('_FR_UPGRADE_DB_PATH_INFO', "Remplissez ce champ uniquement si vous voulez changer le chemin de votre base de données en SQLite, Interbase et Firebird.");
define('_FR_UPGRADE_DB_PORT', "Port de la base de données");
define('_FR_UPGRADE_DB_PORT_INFO', "Remplissez ce champ uniquement si votre base de données est accessible sur un autre port que celui par défaut.<br />Si vous n'avez <strong>aucune idée</strong> du port que vous utilisez, alors vous devez probablement utiliser le port par défaut et nous vous <strong>conseillons</strong> de laisser ce champ vide.");
define('_FR_UPGRADE_DB_PREFIX', "Préfixe des tables");
define('_FR_UPGRADE_DB_PREFIX_INFO', "Texte qui sera placé devant le nom des tables, vous pouvez donc utiliser plusieurs site Jaws avec la même base de données, par exemple <strong>blog_</strong>");
define('_FR_UPGRADE_DB_RESPONSE_PATH', "Le chemin de la base de données n'existe pas");
define('_FR_UPGRADE_DB_RESPONSE_PORT', "Le port doit être une valeur numérique");
define('_FR_UPGRADE_DB_RESPONSE_INCOMPLETE', "Vous devez remplir tous les champs à l’exception du chemin de la base de données, le préfixe des tables et le port.");
define('_FR_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Un problème est survenu lors de la connexion à la base de données, vérifiez les détails et réessayez.");
define('_FR_UPGRADE_REPORT_INFO', "Comparaison de votre version de Jaws installée et l'actuelle {0}");
define('_FR_UPGRADE_REPORT_NOTICE', "Ci-dessous vous trouverez les versions que le système de mise à jour peut gérer.\nVous utilisez peut-être une très ancienne version, nous allons donc nous occuper des autres.");
define('_FR_UPGRADE_REPORT_NEED', "Mise à jour requise");
define('_FR_UPGRADE_REPORT_NO_NEED', "Ne nécessite pas de mise à jour");
define('_FR_UPGRADE_REPORT_NO_NEED_CURRENT', "Ne nécessite pas de mise à jour (c'est la version actuelle)");
define('_FR_UPGRADE_REPORT_MESSAGE', "Si le système de mise à jour trouve que la version de Jaws est ancienne, il va l'actualiser, sinon il se terminera.");
define('_FR_UPGRADE_VER_INFO', "Mise à jour {0} à {1} va");
define('_FR_UPGRADE_VER_NOTES', "<strong>Note:</strong> Une fois que la mise à jour sera terminée d'autres gadgets (comme le Blog, Phoo, etc) vont nécessiter une mise à jour. Vous pouvez le faire en vous connectant au Panneau de Configuration;");
define('_FR_UPGRADE_VER_RESPONSE_GADGET_FAILED', "Un problème est survenu à l'installation du gadget principal (noyau) {0}");
define('_FR_UPGRADE_CONFIG_INFO', "Vous devez maintenant enregistrer le fichier de configuration.");
define('_FR_UPGRADE_CONFIG_SOLUTION', "Vous pouvez le faire de deux façons");
define('_FR_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Assurez-vous que <strong>{0}</strong> soit accessible en écriture, et cliquez sur Suivant, ce qui va autoriser l'installeur à enregistrer le fichier lui-même.");
define('_FR_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Copiez et collez le contenu du cadre ci-dessous dans un fichier et enregistrez-le dans <strong>{0}</strong>");
define('_FR_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Une erreur inconnue est survenue lors de l'écriture du fichier de configuration.");
define('_FR_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Soit vous autorisez l'accès en écriture au répertoire de config, soit vous créez {0} à la main.");
define('_FR_UPGRADE_FINISH_INFO', "Vous avez terminé la configuration de votre site !");
define('_FR_UPGRADE_FINISH_CHOICES', "Vous avez maintenant deux choix, soit vous <a href=\"{0}\">visualisez votre site</a>, soit vous vous <a href=\"{1}\">connectez au Panneau de Configuration</a>.");
define('_FR_UPGRADE_FINISH_MOVE_LOG', "Note: Si vous avez autorisé l'option d'identification à la première étape, nous vous suggérons de le sauvegarder et de le déplacer/supprimer");
define('_FR_UPGRADE_FINISH_THANKS', "Merci d'utiliser Jaws!");
