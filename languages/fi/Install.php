<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Tatu Patronen <dragonic@dragonic.org>"
 * "Language-Team: FI"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_FI_INSTALL_INTRODUCTION', "Esittely");
define('_FI_INSTALL_AUTHENTICATION', "Tarkastus");
define('_FI_INSTALL_REQUIREMENTS', "Vaatimukset");
define('_FI_INSTALL_DATABASE', "Tietokanta");
define('_FI_INSTALL_CREATEUSER', "Luo uusi käyttäjä");
define('_FI_INSTALL_SETTINGS', "Asetukset");
define('_FI_INSTALL_WRITECONFIG', "TallennaT");
define('_FI_INSTALL_FINISHED', "Valmis");
define('_FI_INSTALL_INTRO_WELCOME', "Tervetuloa Jaws asennukseen.");
define('_FI_INSTALL_INTRO_INSTALLER', "Käyttämällä asennusta sinut ohjataan Jaws käyttöönotossa, ole hyvä ja varmista että sinulla on seuraavat asiat käytössäsi");
define('_FI_INSTALL_INTRO_DATABASE', "Tietokannan tiedot - palvelimen osoite, käyttäjätunnus, salasana, tietokannan nimi.");
define('_FI_INSTALL_INTRO_FTP', "Tapa siirtää tiedosto, kuten FTP.");
define('_FI_INSTALL_INTRO_MAIL', "Sähköpostipalvelimen tiedot (palvelimen osoite, käyttäjätunnus, salasana) jos olet käyttämässä sähköpostipalvelinta.");
define('_FI_INSTALL_INTRO_LOG', "Kirjaa prosessi (ja virheet) asennuksesta tiedostoon ({0})");
define('_FI_INSTALL_INTRO_LOG_ERROR', "Huom: Jos haluat kirjata asennusprosessin (ja virheet) tiedostoon sinuun täytyy asettaa kirjoitusoikeudet {0} hakemistoon ja avata tämä sivu uudelleen.");
define('_FI_INSTALL_AUTH_PATH_INFO', "Varmistukseksi että olet tämän sivuston oikea omistaja, ole hyvä ja luo tiedosto nimeltään {0} Jaws asennuskansioon ({1}).");
define('_FI_INSTALL_AUTH_UPLOAD', "Voit siirtää tiedoston samalla tavalla kuin siirsit Jaws asennuspaketin.");
define('_FI_INSTALL_AUTH_KEY_INFO', "Tiedoston tulee sisältään ainoastaan edellä mainittu koodi, ei mitään muuta.");
define('_FI_INSTALL_AUTH_ENABLE_SECURITY', "Käytä turvattua asennusta (RSA)");
define('_FI_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Tapahtui virhe RSA avaimen luomisessa. Ole hyvä ja yritä uudelleen.");
define('_FI_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Tapahtui virhe RSA avaimen luomisessa. Ei matematiikkalaajennusta");
define('_FI_INSTALL_AUTH_ERROR_KEY_FILE', "Avainta ({0}) ei löytynyt, ole hyvä ja tarkista että olet luonut sen ja että palvelin pystyy lukemaan sen.");
define('_FI_INSTALL_AUTH_ERROR_KEY_MATCH', "Avain ({0}) ei vastaa alla olevaan koodiin, ole hyvä ja tarkista että annoit oikean avaimen.");
define('_FI_INSTALL_REQ_REQUIREMENT', "Vaatii");
define('_FI_INSTALL_REQ_OPTIONAL', "Vaihtoehtoiset mutta suositeltavat");
define('_FI_INSTALL_REQ_RECOMMENDED', "Suositeltavat");
define('_FI_INSTALL_REQ_DIRECTIVE', "Asetus");
define('_FI_INSTALL_REQ_ACTUAL', "Löytyi");
define('_FI_INSTALL_REQ_RESULT', "Tila");
define('_FI_INSTALL_REQ_PHP_VERSION', "PHP versio");
define('_FI_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_FI_INSTALL_REQ_DIRECTORY', "{0} hakemisto");
define('_FI_INSTALL_REQ_EXTENSION', "{0} laajennus");
define('_FI_INSTALL_REQ_FILE_UPLOAD', "Tiedostojen lähetys");
define('_FI_INSTALL_REQ_SAFE_MODE', "Vikasietotila");
define('_FI_INSTALL_REQ_READABLE', "Lukuoikeus");
define('_FI_INSTALL_REQ_WRITABLE', "Kirjoitusoikeus");
define('_FI_INSTALL_REQ_OK', "OK");
define('_FI_INSTALL_REQ_BAD', "HUONO");
define('_FI_INSTALL_REQ_OFF', "Pois");
define('_FI_INSTALL_REQ_ON', "Päällä");
define('_FI_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "Hakemistossa {0} ei ole joko lukuoikeutta tai kirjoitusoikeutta. Ole hyvä ja korjaa oikeudet.");
define('_FI_INSTALL_REQ_RESPONSE_PHP_VERSION', "Vanhin tuettu PHP versio asentaaksesi Jaws on {0}. Sinun täytyy päivittää PHP versiotasi.");
define('_FI_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Hakemistoissa {0} ei ole joko lukuoikeutta tai kirjoitusoikeutta. Ole hyvä ja korjaa oikeudet.");
define('_FI_INSTALL_REQ_RESPONSE_EXTENSION', "Laajennus {0} on pakollinen käyttääksesi Jawsia");
define('_FI_INSTALL_DB_INFO', "Sinun täytyy nyt antaa tietokannan tiedot, johon tallennetaan tietosi myöhempää käyttöä varten.");
define('_FI_INSTALL_DB_NOTICE', "Tietokanta jonka tiedot annat täytyy olla asennettuna jotta voit edetä tästä vaiheesta.");
define('_FI_INSTALL_DB_HOST', "Palvelimen osoite");
define('_FI_INSTALL_DB_HOST_INFO', "Jos et tiedä tätä, voit jättää tämä kohdan {0}:ksi");
define('_FI_INSTALL_DB_DRIVER', "Tietokanta");
define('_FI_INSTALL_DB_USER', "Käyttäjätunnus");
define('_FI_INSTALL_DB_PASS', "Salasana");
define('_FI_INSTALL_DB_IS_ADMIN', "Tietokannan ylläpitäjä?");
define('_FI_INSTALL_DB_NAME', "Tietokannan nimi");
define('_FI_INSTALL_DB_PATH', "Tietokannan polku");
define('_FI_INSTALL_DB_PATH_INFO', "Täytä tämä vain jos haluat vaihtaa tietokannan polkua SQLite, Interbase ja Firebird kannoissa");
define('_FI_INSTALL_DB_PORT', "Tietokannan portti");
define('_FI_INSTALL_DB_PORT_INFO', "Ainoastaan täytä tämä kenttä jos tietokanta käyttää toista porttia kuin oletusportti.\nJos sinulla <b>ei ole tietoa</b> mitä porttia tietokanta käyttää, niin on hyvin todennäköistä että se käyttää oletusporttia täten <b>suosittelemme</b> että jätät tämän kohdan tyhjäksi.");
define('_FI_INSTALL_DB_PREFIX', "Taulukon etuliite");
define('_FI_INSTALL_DB_PREFIX_INFO', "Teksti joka laitetaan taulukoiden eteen jotta voit ajaa useampia Jaws sivuja samasta tietokannasta, esimerkiksi <b>blog_</b>");
define('_FI_INSTALL_DB_RESPONSE_PATH', "Tietokannan polkua ole olemassa");
define('_FI_INSTALL_DB_RESPONSE_PORT', "Portti voi olla vain numeroarvo");
define('_FI_INSTALL_DB_RESPONSE_INCOMPLETE', "Sinun täytyy täyttää kaikki kentät lukuunottamatta tietokantapolkua, taulukon etuliitettä ja porttia.");
define('_FI_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Tapahtui virhe yrittäessä ottaa yhteyttä tietokantaan, tarkista asetukset ja yritä uudelleen.");
define('_FI_INSTALL_DB_RESPONSE_GADGET_INSTALL', "Tapahtui virhe asentaessa ydinlaajennusta {0}");
define('_FI_INSTALL_DB_RESPONSE_SETTINGS', "Tapahtui virhe pystyessä tietokantaa.");
define('_FI_INSTALL_USER_INFO', "Voit nyt luoda käyttäjätunnuksen itsellesi.");
define('_FI_INSTALL_USER_NOTICE', "Älä valitse salasanaa joka on helposti arvattavissa sillä käyttäjätunnuksellasi on täydet oikeudet nettisivuillesi.");
define('_FI_INSTALL_USER_USER', "Käyttäjätunnus");
define('_FI_INSTALL_USER_USER_INFO', "Nimesi, joka näkyy kun julkaiset jotain");
define('_FI_INSTALL_USER_PASS', "Salasana");
define('_FI_INSTALL_USER_REPEAT', "Toista");
define('_FI_INSTALL_USER_REPEAT_INFO', "Toista salasana varmistuksen vuoksi.");
define('_FI_INSTALL_USER_NAME', "Nimi");
define('_FI_INSTALL_USER_NAME_INFO', "Oikea nimesi");
define('_FI_INSTALL_USER_EMAIL', "Sähköpostiosoite");
define('_FI_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Salasana ei vastaa. Ole hyvä ja yritä uudelleen.");
define('_FI_INSTALL_USER_RESPONSE_INCOMPLETE', "Sinun täytyy täyttää käyttäjätunnus, salasana ja salasana uudestaan kentät.");
define('_FI_INSTALL_USER_RESPONSE_CREATE_FAILED', "Tapahtui virhe luotaessa käyttäjää.");
define('_FI_INSTALL_SETTINGS_INFO', "Voit nyt asettaa oletusasetukset nettisivuillesi. Voit vaihtaa asetuksia myöhemmin kirjautumalla ohjauspaneeliin ja valitsemalla Asetukset.");
define('_FI_INSTALL_SETTINGS_SITE_NAME', "Sivuston nimi");
define('_FI_INSTALL_SETTINGS_SITE_NAME_INFO', "Nimi joka näytetään sivulla.");
define('_FI_INSTALL_SETTINGS_SLOGAN', "Kuvaus");
define('_FI_INSTALL_SETTINGS_SLOGAN_INFO', "Tarkempi kuvaus sivustosta.");
define('_FI_INSTALL_SETTINGS_DEFAULT_GADGET', "Oletuslaajennus");
define('_FI_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Laajennus joka näytetään etusivulla");
define('_FI_INSTALL_SETTINGS_SITE_LANGUAGE', "Sivuston kieli");
define('_FI_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Oletuskieli jota sivuilla käytetään.");
define('_FI_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Sinun täytyy antaa sivustolle nimi");
define('_FI_INSTALL_CONFIG_INFO', "Sinun täytyy tallentaa asetukset tiedostoon.");
define('_FI_INSTALL_CONFIG_SOLUTION', "Voit tehdä tämän kahdella tavalla");
define('_FI_INSTALL_CONFIG_SOLUTION_PERMISSION', "Laita hakemistoon {0} kirjoitusoikeudet paina seuraava. Asennus tallentaa asetukset itse.");
define('_FI_INSTALL_CONFIG_SOLUTION_UPLOAD', "Kopio ja liitä kentän sisältö tiedostoon ja tallenna se {0}");
define('_FI_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Tapahtui virhe kirjoittaessa asetukset tiedostoa.");
define('_FI_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Sinun täytyy joko tehdä asetukset kansio kirjoitettavaksi tai luoda {0} käsin.");
define('_FI_INSTALL_FINISH_INFO', "Olet suorittanut asennuksen!");
define('_FI_INSTALL_FINISH_CHOICES', "Voit siirtyä <a href=\"{0}\">katsomaan sivustoa</a> tai <a href=\"{1}\">kirjautua ohjauspaneeliin</a>");
define('_FI_INSTALL_FINISH_MOVE_LOG', "Huom: jos olet laitoit kirjaa prosessi vaihtoehton ensimmäisessä vaiheessa, suosittelemme että tallennat sen ja siirrät / poistat sen");
define('_FI_INSTALL_FINISH_THANKS', "Kiitos kun valitsit Jaws!");
