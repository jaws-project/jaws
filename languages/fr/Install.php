<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: ian <ianthelazy[at]gmail[dot]com>"
 * "Language-Team: FR"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_FR_INSTALL_INTRODUCTION', "Introduction");
define('_FR_INSTALL_AUTHENTICATION', "Authentification");
define('_FR_INSTALL_REQUIREMENTS', "Configuration requise");
define('_FR_INSTALL_DATABASE', "Base de données");
define('_FR_INSTALL_CREATEUSER', "Créer un utilisateur");
define('_FR_INSTALL_SETTINGS', "Paramètres");
define('_FR_INSTALL_WRITECONFIG', "Enregistrer la configuration");
define('_FR_INSTALL_FINISHED', "Terminé");
define('_FR_INSTALL_INTRO_WELCOME', "Bienvenue dans l'installation de Jaws");
define('_FR_INSTALL_INTRO_INSTALLER', "En utilisant l'installeur vous allez être guidé dans le paramétrage de votre site web, assurez vous que vous disposez des éléments suivants");
define('_FR_INSTALL_INTRO_DATABASE', "Base de données - Hôte, nom d'utilisateur, mot de passe, nom de la base");
define('_FR_INSTALL_INTRO_FTP', "Une méthode pour envoyer les fichiers, probablement FTP.");
define('_FR_INSTALL_INTRO_MAIL', "Les informations de votre serveur email (hôte, nom d'utilisateur, mot de passe) si vous en utilisez un.");
define('_FR_INSTALL_INTRO_LOG', "Enregistre les erreurs et le journal d'installation dans un fichier ({0})");
define('_FR_INSTALL_INTRO_LOG_ERROR', "Note: Si vous voulez enregistrer le journal (et les erreurs) d'installation dans un fichier vous devez d'abord donner l'accès en écriture au dossier ({0}) puis rafraichir cette page");
define('_FR_INSTALL_AUTH_PATH_INFO', "Pour vérifier que vous êtes bien le possesseur de ce site, veuillez créer un fichier appelé {0} dans le dossier ou vous avez placé Jaws ({1}).");
define('_FR_INSTALL_AUTH_UPLOAD', "Vous pouvez envoyer ce fichier de la même manière que vous avez envoyé les fichiers d'installation de Jaws");
define('_FR_INSTALL_AUTH_KEY_INFO', "Le fichier doit contenir le code indiqué dans le champ ci dessous, et rien d'autre.");
define('_FR_INSTALL_AUTH_ENABLE_SECURITY', "Activer l'installation sécurisée (Fourni par RSA)");
define('_FR_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Erreur dans la création de la clé RSA. Essayez de nouveau.");
define('_FR_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Erreur dans la création de la clé RSA. pas d'extension math disponible.");
define('_FR_INSTALL_AUTH_ERROR_KEY_FILE', "Votre fichier-clé ({0}) est introuvable, merci de vous assurer que vous l'avez créé, et que le serveur peut le lire.");
define('_FR_INSTALL_AUTH_ERROR_KEY_MATCH', "Le fichier-clé trouvé ({0}) ne correspond pas au code ci dessous, merci de vérifier que vous avez entré la clé correctement.");
define('_FR_INSTALL_REQ_REQUIREMENT', "Configuration requise");
define('_FR_INSTALL_REQ_OPTIONAL', "Optionnel mais recommandé");
define('_FR_INSTALL_REQ_RECOMMENDED', "Recommandé");
define('_FR_INSTALL_REQ_DIRECTIVE', "Directive");
define('_FR_INSTALL_REQ_ACTUAL', "Actuel");
define('_FR_INSTALL_REQ_RESULT', "Résultat");
define('_FR_INSTALL_REQ_PHP_VERSION', "Version de PHP");
define('_FR_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_FR_INSTALL_REQ_DIRECTORY', "dossier {0}");
define('_FR_INSTALL_REQ_EXTENSION', "extension {0}");
define('_FR_INSTALL_REQ_FILE_UPLOAD', "Envoi de fichier");
define('_FR_INSTALL_REQ_SAFE_MODE', "Mode sans échec");
define('_FR_INSTALL_REQ_READABLE', "Accessible en lecture");
define('_FR_INSTALL_REQ_WRITABLE', "Accessible en écriture");
define('_FR_INSTALL_REQ_OK', "OK");
define('_FR_INSTALL_REQ_BAD', "Mauvais");
define('_FR_INSTALL_REQ_OFF', "OFF");
define('_FR_INSTALL_REQ_ON', "ON");
define('_FR_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "Le dossier {0} n'a pas les droits en lecture ou en écriture, vérifiez les permissions.");
define('_FR_INSTALL_REQ_RESPONSE_PHP_VERSION', "La version minimum de PHP pour installer JAWS est {0}, merci de mettre à jour votre version de PHP.");
define('_FR_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Les dossiers listés ci dessous comme {0} ne sont pas accessible en lecture ou en écriture, vérifiez les permissions.");
define('_FR_INSTALL_REQ_RESPONSE_EXTENSION', "L'extension {0} est nécessaire pour utiliser Jaws.");
define('_FR_INSTALL_DB_INFO', "Vous devez a présent configurer votre base de données, qui est utilisé pour enregistrer les informations qui seront affichées plus tard.");
define('_FR_INSTALL_DB_NOTICE', "La base de données dont vous avez entré les détails doit d'abord être créée pour que cette procédure fonctionne.");
define('_FR_INSTALL_DB_HOST', "hôte");
define('_FR_INSTALL_DB_HOST_INFO', "Si vous n'êtes pas sur, il est probablement plus sur de le laisser comme {0}");
define('_FR_INSTALL_DB_DRIVER', "Pilote");
define('_FR_INSTALL_DB_USER', "Nom d'utilisateur");
define('_FR_INSTALL_DB_PASS', "Mot de passe");
define('_FR_INSTALL_DB_IS_ADMIN', "Est-ce l'administrateur de la BDD?");
define('_FR_INSTALL_DB_NAME', "Nom de la base de données");
define('_FR_INSTALL_DB_PATH', "Chemin de la base de données");
define('_FR_INSTALL_DB_PATH_INFO', "Remplir ce champ si vous voulez changer le chemin de la base de données en SQLite, Interbase et Firebird.");
define('_FR_INSTALL_DB_PORT', "Port de la base de données");
define('_FR_INSTALL_DB_PORT_INFO', "Ne remplissez ce champ que si la base de données fonctionne sur un autre port que celui par défaut.\nSi vous n'avez aucune idée du port, ce doit très certainement être celui par défaut et nous vous conseillons de laisser le champ vide.");
define('_FR_INSTALL_DB_PREFIX', "Préfixe des tables");
define('_FR_INSTALL_DB_PREFIX_INFO', "Un texte qui sera placé au début du nom des tables, cela permet de faire fonctionner plusieurs sites Jaws sur la même base, par exemple site1_, blog_...");
define('_FR_INSTALL_DB_RESPONSE_PATH', "Le chemin de la base de donnéed n'existe pas");
define('_FR_INSTALL_DB_RESPONSE_PORT', "Le port peut être uniquement une valeur numérique");
define('_FR_INSTALL_DB_RESPONSE_INCOMPLETE', "Tous les champs sauf le port et le préfixe doivent être remplis.");
define('_FR_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Impossible de se connecter à la base de données, merci de vérifier les détails et essayez de nouveau.");
define('_FR_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Un problème est apparu lors de l'installation du gadget Core {0}");
define('_FR_INSTALL_DB_RESPONSE_SETTINGS', "Un problème est apparu lors de la création de la base de données.");
define('_FR_INSTALL_USER_INFO', "Vous pouvez à présent créer votre compte utilisateur.");
define('_FR_INSTALL_USER_NOTICE', "Ne choisissez pas un mot de passe facile à deviner puisque toute personne possédant votre mot de passe a un accès complet à votre site web.");
define('_FR_INSTALL_USER_USER', "Nom d'utilisateur");
define('_FR_INSTALL_USER_USER_INFO', "Nom d'affichage, qui sera noté sur les éléments que vous posterez");
define('_FR_INSTALL_USER_PASS', "Mot de passe");
define('_FR_INSTALL_USER_REPEAT', "Répéter le mot de passe");
define('_FR_INSTALL_USER_REPEAT_INFO', "Répétez votre mot de passe pour être sûr qu'il n'y a de faute de frappe");
define('_FR_INSTALL_USER_NAME', "Nom");
define('_FR_INSTALL_USER_NAME_INFO', "Votre nom réel");
define('_FR_INSTALL_USER_EMAIL', "Adresse email");
define('_FR_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Le mot de passe ne correspond pas a la vérification, essayez de nouveau.");
define('_FR_INSTALL_USER_RESPONSE_INCOMPLETE', "Vous devez remplir les champs Nom d'utilisateur, Mot de passe et Répéter le mot de passe.");
define('_FR_INSTALL_USER_RESPONSE_CREATE_FAILED', "Un problème est survenu lors de la création de votre compte utilisateur.");
define('_FR_INSTALL_SETTINGS_INFO', "Vous pouvez maintenant configurer les paramètres par défaut pour votre site. Cela peut être modifié ultérieurement en vous connectant au panneau de configuration.");
define('_FR_INSTALL_SETTINGS_SITE_NAME', "Nom du site");
define('_FR_INSTALL_SETTINGS_SITE_NAME_INFO', "Le nom a afficher sur votre site");
define('_FR_INSTALL_SETTINGS_SLOGAN', "Description");
define('_FR_INSTALL_SETTINGS_SLOGAN_INFO', "Une description de votre nouveau site web");
define('_FR_INSTALL_SETTINGS_DEFAULT_GADGET', "Gadget par défaut");
define('_FR_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Le gadget à afficher quand une personne visite le site");
define('_FR_INSTALL_SETTINGS_SITE_LANGUAGE', "Langage du site");
define('_FR_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Le langage principale que le site affiche");
define('_FR_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Vous devez entrer le nom du site");
define('_FR_INSTALL_CONFIG_INFO', "Vous devez maintenant sauvegarder le fichier de configuration");
define('_FR_INSTALL_CONFIG_SOLUTION', "Deux méthodes sont possibles");
define('_FR_INSTALL_CONFIG_SOLUTION_PERMISSION', "Rendre {0} accessible en écriture, ce qui permettra à l'installeur d'enregistrer lui même le fichier.");
define('_FR_INSTALL_CONFIG_SOLUTION_UPLOAD', "Copier et coller le contenu du champ ci dessous et l'enregistrer dans le fichier {0}");
define('_FR_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Une erreur s'est produite lors de l'écriture du fichier de configuration.");
define('_FR_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Vous devez rendre le dossier de configuration accessible en écriture, ou creer {0} a la main.");
define('_FR_INSTALL_FINISH_INFO', "Votre site web est maintenant configuré!");
define('_FR_INSTALL_FINISH_CHOICES', "Vous avez à présent deux choix: vous pouvez visiter <a href=\"{0}\">visiter votre site</a> ou <a href=\"{1}\">vous connecter au panneau de configuration</a>.");
define('_FR_INSTALL_FINISH_MOVE_LOG', "Note: si vous aviez activé l'option d'enregistrement du journal, nous suggérons que vous le sauvegardiez et que vous le déplaciez/supprimiez.");
define('_FR_INSTALL_FINISH_THANKS', "Merci d'utiliser Jaws !");
