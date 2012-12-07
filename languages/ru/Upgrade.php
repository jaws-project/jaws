<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Upgrade"
 * "Last-Translator: Dennis Burakov <dennis.burakov@yahoo.com>"
 * "Language-Team: RU"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_RU_UPGRADE_INTRODUCTION', "Введение");
define('_RU_UPGRADE_AUTHENTICATION', "Аутентификация");
define('_RU_UPGRADE_REQUIREMENTS', "Требования");
define('_RU_UPGRADE_DATABASE', "База данных");
define('_RU_UPGRADE_REPORT', "Отчет");
define('_RU_UPGRADE_VER_TO_VER', "{0} до {1}");
define('_RU_UPGRADE_SETTINGS', "Настройки");
define('_RU_UPGRADE_WRITECONFIG', "Сохранение конфигурации");
define('_RU_UPGRADE_FINISHED', "Готово");
define('_RU_UPGRADE_INTRO_WELCOME', "Добро пожаловать в модернизатор Jaws.");
define('_RU_UPGRADE_INTRO_UPGRADER', "Используя модернизатор, вы можете обновить старую установку до текущей. Просто убедитесь, что следующие компоненты доступны");
define('_RU_UPGRADE_INTRO_DATABASE', "Настройка базы данных - хост, имя пользователя, пароль, имя базы данных.");
define('_RU_UPGRADE_INTRO_FTP', "Способ загрузки файлов, возможно FTP.");
define('_RU_UPGRADE_INTRO_LOG', "Записать журнал процесса (и ошибок) обновления в файл ({0})");
define('_RU_UPGRADE_INTRO_LOG_ERROR', "Внимание: Если вы хотите записать журнал процесса (и ошибок) обновления в файл, сначала нужно разрешить доступ для записи в каталог ({0}), а затем обновить эту страницу в браузере");
define('_RU_UPGRADE_AUTH_PATH_INFO', "Чтобы убедиться, что вы действительно владелец этого сайта, пожалуйста, создайте файл <strong>{0}</strong> в каталоге upgrade вашей установки Jaws (<strong>{1}</strong>).");
define('_RU_UPGRADE_AUTH_UPLOAD', "Вы можете загрузить файл таким же образом, как загружали вашу инсталляцию Jaws.");
define('_RU_UPGRADE_AUTH_KEY_INFO', "Файл должен сожержать код, показанный в рамке снизу, и ничего больше.");
define('_RU_UPGRADE_AUTH_ENABLE_SECURITY', "Включить защищённое обновление (На основе RSA)");
define('_RU_UPGRADE_AUTH_ERROR_RSA_KEY_GENERATION', "Ошибка генерации ключа RSA. Пожалуйста, попробуйте ещё раз.");
define('_RU_UPGRADE_AUTH_ERROR_NO_MATH_EXTENSION', "Ошибка генерации ключа RSA. Не найдено математических расширений.");
define('_RU_UPGRADE_AUTH_ERROR_KEY_FILE', "Ваш файл с ключом ({0}) не найден, пожалуйста, убедитесь, что создали его, и что веб-сервер может его прочитать.");
define('_RU_UPGRADE_AUTH_ERROR_KEY_MATCH', "Найденный ключ ({0}) не совпадает с указанным ниже, пожалуйста, проверьте, что ввели ключ правильно.");
define('_RU_UPGRADE_REQ_REQUIREMENT', "Требование");
define('_RU_UPGRADE_REQ_OPTIONAL', "Необязательно, но рекомендуется");
define('_RU_UPGRADE_REQ_RECOMMENDED', "Рекомендуется");
define('_RU_UPGRADE_REQ_DIRECTIVE', "Указание");
define('_RU_UPGRADE_REQ_ACTUAL', "Текущий");
define('_RU_UPGRADE_REQ_RESULT', "Результат");
define('_RU_UPGRADE_REQ_PHP_VERSION', "Версия PHP");
define('_RU_UPGRADE_REQ_GREATER_THAN', ">= {0}");
define('_RU_UPGRADE_REQ_DIRECTORY', "каталог {0}");
define('_RU_UPGRADE_REQ_EXTENSION', "расширение {0}");
define('_RU_UPGRADE_REQ_FILE_UPLOAD', "Загрузка файлов");
define('_RU_UPGRADE_REQ_SAFE_MODE', "Безопасный режим");
define('_RU_UPGRADE_REQ_READABLE', "Доступен для чтения");
define('_RU_UPGRADE_REQ_WRITABLE', "Доступен для записи");
define('_RU_UPGRADE_REQ_OK', "OK");
define('_RU_UPGRADE_REQ_BAD', "НЕТ");
define('_RU_UPGRADE_REQ_OFF', "Выкл");
define('_RU_UPGRADE_REQ_ON', "Вкл");
define('_RU_UPGRADE_REQ_RESPONSE_DIR_PERMISSION', "Каталог {0} недоступен либо для чтения, либо для записи, пожалуйста, измените права доступа.");
define('_RU_UPGRADE_REQ_RESPONSE_PHP_VERSION', "Минимальная версия PHP для установки Jaws - {0}, поэтому вы должны обновить вашу версию PHP.");
define('_RU_UPGRADE_REQ_RESPONSE_DIRS_PERMISSION', "Следующие каталоги: {0} недоступны либо для чтения, либо для записи, пожалуйста, измените права доступа.");
define('_RU_UPGRADE_REQ_RESPONSE_EXTENSION', "Расширение {0} необходимо для использования Jaws.");
define('_RU_UPGRADE_DB_INFO', "Теперь вам нужно настроить базу данных, используйте информацию о текущей базе данных, чтобы обновить ее.");
define('_RU_UPGRADE_DB_HOST', "Хост");
define('_RU_UPGRADE_DB_HOST_INFO', "Если вы не знаете этого, возможно, безопасно оставить это как {0}.");
define('_RU_UPGRADE_DB_DRIVER', "Драйвер");
define('_RU_UPGRADE_DB_USER', "Пользователь");
define('_RU_UPGRADE_DB_PASS', "Пароль");
define('_RU_UPGRADE_DB_IS_ADMIN', "Администратор БД?");
define('_RU_UPGRADE_DB_NAME', "Название БД");
define('_RU_UPGRADE_DB_PATH', "Путь к базе данных");
define('_RU_UPGRADE_DB_PATH_INFO', "Заполняйте это поле только в том случае, если хотите изменить путь к базе данных в драйвере SQLite, Interbase или Firebird.");
define('_RU_UPGRADE_DB_PORT', "Порт БД");
define('_RU_UPGRADE_DB_PORT_INFO', "Заполняйте это поле только в случае, если ваша база данных использует порт, отличный от порта по умолчанию.Если вы <strong>не знаете</strong>, какой порт использует база данных, скорее всего она использует порт по умолчанию, и поэтому мы <strong>рекомендуем</strong> вам оставить это поле пустым.");
define('_RU_UPGRADE_DB_PREFIX', "Префикс таблиц");
define('_RU_UPGRADE_DB_PREFIX_INFO', "Какой-нибудь текст, который будет предварять названия таблиц, так что вы сможете иметь больше одного сайта Jaws на одной базе данных, например <strong>blog_</strong>");
define('_RU_UPGRADE_DB_RESPONSE_PATH', "Путь к базе данных не существует");
define('_RU_UPGRADE_DB_RESPONSE_PORT', "Порт может быть только числовым значением");
define('_RU_UPGRADE_DB_RESPONSE_INCOMPLETE', "Вы должны заполнить все поля, кроме пути к базе данных, префикса таблиц и порта.");
define('_RU_UPGRADE_DB_RESPONSE_CONNECT_FAILED', "Ошибка соединения с базой данных, пожалуйста, проверьте данные и попытайтесь снова.");
define('_RU_UPGRADE_REPORT_INFO', "Сравнение устанавливаемой версии Jaws с текущей {0}");
define('_RU_UPGRADE_REPORT_NOTICE', "Ниже вы найдете версии, которые поддерживаются этой системой обновления. Возможно, у вас очень старая версия, так что мы позаботимся об остальном.");
define('_RU_UPGRADE_REPORT_NEED', "Требует обновления");
define('_RU_UPGRADE_REPORT_NO_NEED', "Не требует обновления");
define('_RU_UPGRADE_REPORT_NO_NEED_CURRENT', "Не требует обновления (является текущей)");
define('_RU_UPGRADE_REPORT_MESSAGE', "Если система обновления обнаружит, что ваша версия Jaws устарела, она обновит её, если нет, закончит работу.");
define('_RU_UPGRADE_VER_INFO', "Обновление с {0} до {1} будет");
define('_RU_UPGRADE_VER_NOTES', "<strong>Внимание:</strong> После обновления вашей версии Jaws, другие компоненты (такие как Блог, ФотоОрганайзер и т.д.) также потребуют обновления. Вы можете обновить их, зайдя в Панель управления.");
define('_RU_UPGRADE_VER_RESPONSE_GADGET_FAILED', "произошла ошибка установки компонента ядра {0}");
define('_RU_UPGRADE_CONFIG_INFO', "Теперь вам нужно сохранить файл конфигурации.");
define('_RU_UPGRADE_CONFIG_SOLUTION', "Вы можете сделать это двумя способами");
define('_RU_UPGRADE_CONFIG_SOLUTION_PERMISSION', "Разрешите запись в <strong>{0}</strong>, и нажмите далее, что позволит инсталлятору сохранить конфигурацию самостоятельно.");
define('_RU_UPGRADE_CONFIG_SOLUTION_UPLOAD', "Скопируйте содержимое поля ниже в файл и сохраните его как <strong>{0}</strong>");
define('_RU_UPGRADE_CONFIG_RESPONSE_WRITE_FAILED', "Произошла неизвестная ошибка при записи файла конфигурации.");
define('_RU_UPGRADE_CONFIG_RESPONSE_MAKE_CONFIG', "Вам нужно либо сделать каталог config доступным для записи, либо создать файл {0} самостоятельно.");
define('_RU_UPGRADE_FINISH_INFO', "Вы завершили настройку вашего сайта!");
define('_RU_UPGRADE_FINISH_CHOICES', "Теперь у вас два варианта, вы можете либо <a href=\"{0}\">просмотреть ваш сайт</a>, либо <a href=\"{1}\">войти в панель управления</a>.");
define('_RU_UPGRADE_FINISH_MOVE_LOG', "Внимание: если вы включили ведение журнала на первом шаге установки, мы советуем вам сохранить его, а затем переместить/удалить.");
define('_RU_UPGRADE_FINISH_THANKS', "Спасибо за использование Jaws!");
