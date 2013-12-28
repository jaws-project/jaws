<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Yehuda Deutsch <yeh@uda.co.il>"
 * "Language-Team: HE"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_HE_INSTALL_INTRODUCTION', "היכרות");
define('_HE_INSTALL_AUTHENTICATION', "אימות");
define('_HE_INSTALL_REQUIREMENTS', "דרישות");
define('_HE_INSTALL_DATABASE', "מסד נתונים");
define('_HE_INSTALL_CREATEUSER', "צור משתמש");
define('_HE_INSTALL_SETTINGS', "הגדרות");
define('_HE_INSTALL_WRITECONFIG', "שמור הגדרות");
define('_HE_INSTALL_FINISHED', "סיים");
define('_HE_INSTALL_INTRO_WELCOME', "ברוך הבא להתקנת Jaws.");
define('_HE_INSTALL_INTRO_INSTALLER', "בשימוש בהתקנה המערכת תדריך דרך השלבים להתקנת האתר שלך, נא לבדוק שהפריטים הבאים זמינים");
define('_HE_INSTALL_INTRO_DATABASE', "פרטי מסד נתונים - שם שרת, שם משתמש, סיסמה, שם בסיס הנתונים");
define('_HE_INSTALL_INTRO_FTP', "שיטה להעלאת נתונים, ככל הנראה FTP.");
define('_HE_INSTALL_INTRO_MAIL', "מידע על שרת הדואר שלך (שרת, משתמש וסיסמה) אם בשימושך שרת דואר.");
define('_HE_INSTALL_INTRO_LOG', "רשום את תהליך (ושגיאות) ההתקנה לקובץ ({0})");
define('_HE_INSTALL_INTRO_LOG_ERROR', "הערה: במידה וברצונך לרשום את תהליך (ושגיאות) ההתקנה לקובץ עליך קודם להגדיר הרשאות כתיבה לתיקייה ({0}) ואז לרענן דף זה בדפדפן");
define('_HE_INSTALL_AUTH_PATH_INFO', "על מנת לוודא שהינך אכן הבעלים של האתר, עליך ליצור קובץ בשם <strong>{0}</strong> בתיקיית ההתקנה של Jaws (<strong>{1}</strong>).");
define('_HE_INSTALL_AUTH_UPLOAD', "ביכולתך להעלות את הקובץ באותה דרך בה העלית את ההתקנה של Jaws.");
define('_HE_INSTALL_AUTH_KEY_INFO', "על הקובץ להכיל את הקוד המופיע בתיבה מתחת, ללא שום דבר נוסף.");
define('_HE_INSTALL_AUTH_ENABLE_SECURITY', "אפשר התקנה מאובטחת (פועל ע\"י RSA)");
define('_HE_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "שגיאה במחולל המפתחות של RSA. אנא נסה שוב.");
define('_HE_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "שגיאה במחולל הקוד RSA. אין תוסף מתמטי זמין.");
define('_HE_INSTALL_AUTH_ERROR_KEY_FILE', "קובץ המפתח ({0}) לא נמצא, אנא וודא שיצרת אותו, ושלשרת יש הרשאות קריאה עליו");
define('_HE_INSTALL_AUTH_ERROR_KEY_MATCH', "קובץ המפתח ({0}), אינו מכיל את הקוד המופיע מתחת, נא וודא שהזנת את המפתח הנכון.");
define('_HE_INSTALL_REQ_REQUIREMENT', "דרישות");
define('_HE_INSTALL_REQ_OPTIONAL', "אופציונלי אבל מומלץ");
define('_HE_INSTALL_REQ_RECOMMENDED', "מומלץ");
define('_HE_INSTALL_REQ_DIRECTIVE', "הגדרה");
define('_HE_INSTALL_REQ_ACTUAL', "קיים");
define('_HE_INSTALL_REQ_RESULT', "תוצאה");
define('_HE_INSTALL_REQ_PHP_VERSION', "גירסת PHP");
define('_HE_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_HE_INSTALL_REQ_DIRECTORY', "תיקייה {0}");
define('_HE_INSTALL_REQ_EXTENSION', "תוסף {0}");
define('_HE_INSTALL_REQ_FILE_UPLOAD', "העלאת קבצים");
define('_HE_INSTALL_REQ_SAFE_MODE', "מצב בטוח");
define('_HE_INSTALL_REQ_READABLE', "ניתן לקריאה");
define('_HE_INSTALL_REQ_WRITABLE', "ניתן לכתיבה");
define('_HE_INSTALL_REQ_OK', "תקין");
define('_HE_INSTALL_REQ_BAD', "לא תקין");
define('_HE_INSTALL_REQ_OFF', "כבוי");
define('_HE_INSTALL_REQ_ON', "פעיל");
define('_HE_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "לא ניתן לקרוא או לכתוב בתיקייה {0}, נא לתקן את ההרשאות.");
define('_HE_INSTALL_REQ_RESPONSE_PHP_VERSION', "גירסת PHP המינימלית להתקנת Jaws הינה {0}, לפיכך עליך לשדרג את גירסת ה-PHP.");
define('_HE_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "התיקיות המופיעות למטה כ{0} אינן ניתנות לקריאה או לכתיבה, נא לתקן את ההרשאות.");
define('_HE_INSTALL_REQ_RESPONSE_EXTENSION', "התוסף {0} חיוני לשימוש ב-Jaws.");
define('_HE_INSTALL_DB_INFO', "עליך להתקין כעת את מסד הנתונים, המשמש לאחסן את המידע להצגה אח\"כ.");
define('_HE_INSTALL_DB_NOTICE', "על מסד הנתונים המפורט על ידך להיות מותקן על מנת שתהליך זה יעבוד.");
define('_HE_INSTALL_DB_HOST', "שרת");
define('_HE_INSTALL_DB_HOST_INFO', "אם אינך יודע זאת, כנראה בטוח להשאיר אותו {0}.");
define('_HE_INSTALL_DB_DRIVER', "התקן");
define('_HE_INSTALL_DB_USER', "שם משתמש");
define('_HE_INSTALL_DB_PASS', "סיסמה");
define('_HE_INSTALL_DB_IS_ADMIN', "האם מנהל מסד נתונים");
define('_HE_INSTALL_DB_NAME', "שם מסד הנתונים");
define('_HE_INSTALL_DB_PATH', "נתיב למסד הנתונים");
define('_HE_INSTALL_DB_PATH_INFO', "מלא שדה זה רק אם ברצונך לשנות את נתיב מסד הנתונים עבור התקני SQLite, Interbase או firebird.");
define('_HE_INSTALL_DB_PORT', "יציאת מסד נתונים");
define('_HE_INSTALL_DB_PORT_INFO', "מלא שדה זה רק אם מסד הנתונים פועל על יציאה אחרת מאשר ברירת המחדל.\nאם אין לך <strong>שום מושג</strong> על איזו יציאה מסד הנתונים שלך מאזין, ככל הנראה הוא מאזין ליציאת ברירת המחדל, אנו ממליצים להשאיר שדה זה ריק.");
define('_HE_INSTALL_DB_PREFIX', "קידומת טבלאות");
define('_HE_INSTALL_DB_PREFIX_INFO', "טקסט שיוכנס לפני שמות טבלאות, כך שתוכל להריץ מספר התקנות של Jaws על אותו מסד נתונים, לדומה <strong>blog_</strong>");
define('_HE_INSTALL_DB_RESPONSE_PATH', "נתיב מסד הנתונים לא קיים");
define('_HE_INSTALL_DB_RESPONSE_PORT', "הפורט יכול להיות ערך מספרי בלבד");
define('_HE_INSTALL_DB_RESPONSE_INCOMPLETE', "יש למלא את כל השדות מלבד נתיב ויציאת מסד הנתונים וקידומת טבלאות.");
define('_HE_INSTALL_DB_RESPONSE_CONNECT_FAILED', "אירעה תקלה בהתחברות למסד הנתונים, נא לבדוק את הפרטים ולנסות שוב.");
define('_HE_INSTALL_DB_RESPONSE_GADGET_INSTALL', "אירעה תקלה בהתקנת גאדג\'ט עיקרי {0}");
define('_HE_INSTALL_DB_RESPONSE_SETTINGS', "אירעה תקלה בהגדרת מסד הנתונים.");
define('_HE_INSTALL_USER_INFO', "ביכולתך כעת להגדיר חשבון משתמש עבורך.");
define('_HE_INSTALL_USER_NOTICE', "יש לזכור לא לבחור סיסמה קלה לניחוש מאחר ולכל אחד שיש את הסיסמה יש שליטה מלאה על האתר.");
define('_HE_INSTALL_USER_USER', "שם משתמש");
define('_HE_INSTALL_USER_USER_INFO', "שם המשתמש לצורך התחברות, אשר יוצג בפריטים שיתווספו על ידך.");
define('_HE_INSTALL_USER_PASS', "סיסמה");
define('_HE_INSTALL_USER_REPEAT', "שוב");
define('_HE_INSTALL_USER_REPEAT_INFO', "יש להקיש את הסיסמה שוב על מנת לוודא שאין שגיאות הקלדה.");
define('_HE_INSTALL_USER_NAME', "שם");
define('_HE_INSTALL_USER_NAME_INFO', "שם אמיתי");
define('_HE_INSTALL_USER_EMAIL', "כתובת דוא\"ל");
define('_HE_INSTALL_USER_RESPONSE_PASS_MISMATCH', "שתי שדות הסיסמה אינן תואמות, יש לנסות שוב.");
define('_HE_INSTALL_USER_RESPONSE_INCOMPLETE', "יש למלא את השדות שם משתמש, סיסמה ושוב.");
define('_HE_INSTALL_USER_RESPONSE_CREATE_FAILED', "אירעה תקלה בהגדרת חשבון המשתמש שלך.");
define('_HE_INSTALL_SETTINGS_INFO', "אפשר עכשיו להגדיר את הגדרות ברירת המחדל עבור האתר. ניתן אח\"כ לשנות כל אחת מההגדרות האלה בהתחברות ללוח הבקרה וכניסה להגדרות.");
define('_HE_INSTALL_SETTINGS_SITE_NAME', "שם האתר");
define('_HE_INSTALL_SETTINGS_SITE_NAME_INFO', "השם להצגה עבור האתר שלך.");
define('_HE_INSTALL_SETTINGS_SLOGAN', "תיאור");
define('_HE_INSTALL_SETTINGS_SLOGAN_INFO', "תיאור ארוך יותר של האתר שלך.");
define('_HE_INSTALL_SETTINGS_DEFAULT_GADGET', "גאדג\'ט ברירת מחדל");
define('_HE_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "הגאדג\'ט להצגה כאשר גולש מבקר בדף הבית.");
define('_HE_INSTALL_SETTINGS_SITE_LANGUAGE', "שפת אתר");
define('_HE_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "השפה העיקרית שתשמש את האתר.");
define('_HE_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "יש למלא את השדה שם האתר.");
define('_HE_INSTALL_CONFIG_INFO', "עליך לשמור את קובץ ההגדרות.");
define('_HE_INSTALL_CONFIG_SOLUTION', "ניתן לבצע זאת בשתי דרכים");
define('_HE_INSTALL_CONFIG_SOLUTION_PERMISSION', "עשה את <strong>{0}</strong> ניתן לכתיבה, ולחץ על הבא, מה שיאפשר להתקנה לשמור את קובץ ההגדרות לבד.");
define('_HE_INSTALL_CONFIG_SOLUTION_UPLOAD', "העתק והדבק את תוכן התיבה מתחת לתוך קובץ ושמור אותו בשם <strong>{0}</strong>");
define('_HE_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "אירעה שגיאה לא ידועה בכתיבת קובץ ההגדרות.");
define('_HE_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "עליך לאפשר כתיבה לתיקיית ההגדרות או ליצור ידנית את קובץ ההגדרות.");
define('_HE_INSTALL_FINISH_INFO', "סיימת בהצלחה את הגדרת האתר שלך.");
define('_HE_INSTALL_FINISH_CHOICES', "לפניך שתי אפשרויות, ביכולתך <a href=\"{1}\">להכנס אל האתר</a> או <a href=\"{0}\">להתחבר ללוח הבקרה</a>.");
define('_HE_INSTALL_FINISH_MOVE_LOG', "הערה: אם הפעלת את אפשרות הרישום בשלב הראשון אנו ממליצים לך לשמור אותו למקום אחר ולהעביר אותו או למחוק אותו.");
define('_HE_INSTALL_FINISH_THANKS', "תודה על שימוש ב-Jaws!<br />התרגום בעברית צריך עוד שיפורים, אנא עקבו אחרי הנושא בפורום שפות.");
