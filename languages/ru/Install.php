<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Dennis Burakov <dennis.burakov@yahoo.com>"
 * "Language-Team: RU"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_RU_INSTALL_INTRODUCTION', "Введение");
define('_RU_INSTALL_AUTHENTICATION', "Аутентификация");
define('_RU_INSTALL_REQUIREMENTS', "Требования");
define('_RU_INSTALL_DATABASE', "База данных");
define('_RU_INSTALL_CREATEUSER', "Создание пользователя");
define('_RU_INSTALL_SETTINGS', "Настройки");
define('_RU_INSTALL_WRITECONFIG', "Сохранение конфигурации");
define('_RU_INSTALL_FINISHED', "Готово");
define('_RU_INSTALL_INTRO_WELCOME', "Добро пожаловать в установку Jaws.");
define('_RU_INSTALL_INTRO_INSTALLER', "Инсталлятор проведет вас через процесс настройки вашего сайта, пожалуйста, убедитесь в доступности следующих компонентов");
define('_RU_INSTALL_INTRO_DATABASE', "Настройка базы данных - хост, логин, пароль, имя базы данных.");
define('_RU_INSTALL_INTRO_FTP', "Способ загрузки файлов, возможно FTP.");
define('_RU_INSTALL_INTRO_MAIL', "Информация о вашем почтовом сервере (хост, логин, пароль), если вы его используете.");
define('_RU_INSTALL_INTRO_LOG', "Записать журнал процесса (и ошибок) установки в файл ({0})");
define('_RU_INSTALL_INTRO_LOG_ERROR', "Внимание: Если вы хотите записать журнал процесса (и ошибок) установки в файл, сначала нужно разрешить доступ для записи в каталог ({0}), а затем обновить эту страницу в браузере");
define('_RU_INSTALL_AUTH_PATH_INFO', "Чтобы убедиться, что вы действительно владелец этого сайта, пожалуйста, создайте файл <strong>{0}</strong> в каталоге установки Jaws (<strong>{1}</strong>).");
define('_RU_INSTALL_AUTH_UPLOAD', "Вы можете загрузить файл таким же образом, как загружали ваш дистрибутив Jaws.");
define('_RU_INSTALL_AUTH_KEY_INFO', "Файл должен сожержать код, показанный в рамке снизу, и ничего больше.");
define('_RU_INSTALL_AUTH_ENABLE_SECURITY', "Включить защищённую инсталляцию (На основе RSA)");
define('_RU_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "Ошибка генерации ключа RSA. Пожалуйста, попробуйте ещё раз.");
define('_RU_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "Ошибка генерации ключа RSA. Не найдено математических расширений.");
define('_RU_INSTALL_AUTH_ERROR_KEY_FILE', "Ваш файл с ключом ({0}) не найден, пожалуйста, убедитесь, что создали его, и что веб-сервер может его прочитать.");
define('_RU_INSTALL_AUTH_ERROR_KEY_MATCH', "Найденный ключ ({0}) не совпадает с указанным ниже, пожалуйста, проверьте, что ввели ключ правильно.");
define('_RU_INSTALL_REQ_REQUIREMENT', "Требование");
define('_RU_INSTALL_REQ_OPTIONAL', "Необязательно, но рекомендуется");
define('_RU_INSTALL_REQ_RECOMMENDED', "Рекомендуется");
define('_RU_INSTALL_REQ_DIRECTIVE', "Указание");
define('_RU_INSTALL_REQ_ACTUAL', "Текущий");
define('_RU_INSTALL_REQ_RESULT', "Результат");
define('_RU_INSTALL_REQ_PHP_VERSION', "Версия PHP");
define('_RU_INSTALL_REQ_GREATER_THAN', ">= {0}");
define('_RU_INSTALL_REQ_DIRECTORY', "каталог {0}");
define('_RU_INSTALL_REQ_EXTENSION', "расширение {0}");
define('_RU_INSTALL_REQ_FILE_UPLOAD', "Загрузка файлов");
define('_RU_INSTALL_REQ_SAFE_MODE', "Безопасный режим");
define('_RU_INSTALL_REQ_READABLE', "Доступен для чтения");
define('_RU_INSTALL_REQ_WRITABLE', "Доступен для записи");
define('_RU_INSTALL_REQ_OK', "OK");
define('_RU_INSTALL_REQ_BAD', "НЕТ");
define('_RU_INSTALL_REQ_OFF', "Выкл");
define('_RU_INSTALL_REQ_ON', "Вкл");
define('_RU_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "Каталог {0} недоступен либо для чтения, либо для записи, пожалуйста, измените права доступа.");
define('_RU_INSTALL_REQ_RESPONSE_PHP_VERSION', "Минимальная версия PHP для установки Jaws - {0}, поэтому вы должны обновить вашу версию PHP.");
define('_RU_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "Следующие каталоги: {0} недоступны либо для чтения, либо для записи, пожалуйста, измените права доступа.");
define('_RU_INSTALL_REQ_RESPONSE_EXTENSION', "Расширение {0} необходимо для использования Jaws.");
define('_RU_INSTALL_DB_INFO', "Теперь вам нужно настроить базу данных, которая хранит информацию для последующего отображения.");
define('_RU_INSTALL_DB_NOTICE', "База данных, информацию о которой вы предоставили, должна быть уже создана для работы этого процесса.");
define('_RU_INSTALL_DB_HOST', "Хост");
define('_RU_INSTALL_DB_HOST_INFO', "Если вы не знаете этого, возможно, безопасно оставить это как {0}.");
define('_RU_INSTALL_DB_DRIVER', "Драйвер");
define('_RU_INSTALL_DB_USER', "Пользователь");
define('_RU_INSTALL_DB_PASS', "Пароль");
define('_RU_INSTALL_DB_IS_ADMIN', "Администратор БД?");
define('_RU_INSTALL_DB_NAME', "Название БД");
define('_RU_INSTALL_DB_PATH', "Путь к базе данных");
define('_RU_INSTALL_DB_PATH_INFO', "Заполняйте это поле только в том случае, если хотите изменить путь к базе данных в драйвере SQLite, Interbase или Firebird.");
define('_RU_INSTALL_DB_PORT', "Порт БД");
define('_RU_INSTALL_DB_PORT_INFO', "Заполняйте это поле только в случае, если ваша база данных использует порт, отличный от порта по умолчанию.Если вы <strong>не знаете</strong>, какой порт использует база данных, скорее всего она использует порт по умолчанию, и поэтому мы <strong>рекомендуем</strong> вам оставить это поле пустым.");
define('_RU_INSTALL_DB_PREFIX', "Префикс таблиц");
define('_RU_INSTALL_DB_PREFIX_INFO', "Какой-нибудь текст, который будет предварять названия таблиц, так что вы сможете иметь больше одного сайта Jaws на одной базе данных, например <strong>blog_</strong>");
define('_RU_INSTALL_DB_RESPONSE_PATH', "Путь к базе данных не существует");
define('_RU_INSTALL_DB_RESPONSE_PORT', "Порт может быть только числовым значением");
define('_RU_INSTALL_DB_RESPONSE_INCOMPLETE', "Вы должны заполнить все поля, кроме пути к базе данных, префикса таблиц и порта.");
define('_RU_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Ошибка соединения с базой данных, пожалуйста, проверьте данные и попытайтесь снова.");
define('_RU_INSTALL_DB_RESPONSE_GADGET_INSTALL', "произошла ошибка установки компонента ядра {0}");
define('_RU_INSTALL_DB_RESPONSE_SETTINGS', "Ошибка установки базы данных.");
define('_RU_INSTALL_USER_INFO', "Теперь вы можете создать аккаунт пользователя для себя.");
define('_RU_INSTALL_USER_NOTICE', "Не выбирайте легкий для угадывания пароль, потому что любой, кто имеет ваш пароль, обладает полным контролем над вашим сайтом.");
define('_RU_INSTALL_USER_USER', "Пользователь");
define('_RU_INSTALL_USER_USER_INFO', "Ваш логин, который будет отображаться в отправленных вами сообщениях.");
define('_RU_INSTALL_USER_PASS', "Пароль");
define('_RU_INSTALL_USER_REPEAT', "Повтор");
define('_RU_INSTALL_USER_REPEAT_INFO', "Повторите ваш пароль, чтобы убедиться, что нет опечаток.");
define('_RU_INSTALL_USER_NAME', "Имя");
define('_RU_INSTALL_USER_NAME_INFO', "Ваше настоящее имя");
define('_RU_INSTALL_USER_EMAIL', "Адрес e-mail");
define('_RU_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Пароль и повтор не совпадают, пожалуйста, попытайтесь снова.");
define('_RU_INSTALL_USER_RESPONSE_INCOMPLETE', "Вы должны заполнить поля Пользователь, Пароль и Повтор");
define('_RU_INSTALL_USER_RESPONSE_CREATE_FAILED', "Ошибка создания пользователя.");
define('_RU_INSTALL_SETTINGS_INFO', "Теперь вы можете определить установки по умолчанию для вашего сайта. Вы можете изменить любые из них позже, зайдя в Панель Управления и выбрав Настройки.");
define('_RU_INSTALL_SETTINGS_SITE_NAME', "Название сайта");
define('_RU_INSTALL_SETTINGS_SITE_NAME_INFO', "Название для отображения на вашем сайте");
define('_RU_INSTALL_SETTINGS_SLOGAN', "Описание");
define('_RU_INSTALL_SETTINGS_SLOGAN_INFO', "Длинное описание сайта");
define('_RU_INSTALL_SETTINGS_DEFAULT_GADGET', "Компонент по умолчанию");
define('_RU_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Компонент для отображения когда кто-либо посещает главную страницу.");
define('_RU_INSTALL_SETTINGS_SITE_LANGUAGE', "Язык сайта");
define('_RU_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Основной язык отображения сайта");
define('_RU_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Вы должны заполнить поле названия сайта.");
define('_RU_INSTALL_CONFIG_INFO', "Теперь вам нужно сохранить файл конфигурации.");
define('_RU_INSTALL_CONFIG_SOLUTION', "Вы можете сделать это двумя способами");
define('_RU_INSTALL_CONFIG_SOLUTION_PERMISSION', "Разрешите запись в <strong>{0}</strong>, и нажмите далее, что позволит инсталлятору сохранить конфигурацию самостоятельно.");
define('_RU_INSTALL_CONFIG_SOLUTION_UPLOAD', "Скопируйте содержимое поля ниже в файл и сохраните его как <strong>{0}</strong>");
define('_RU_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Произошла неизвестная ошибка при записи файла конфигурации.");
define('_RU_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "Вам нужно либо сделать каталог config доступным для записи, либо создать файл {0} самостоятельно.");
define('_RU_INSTALL_FINISH_INFO', "Вы только что завершили установку вашего сайта!");
define('_RU_INSTALL_FINISH_CHOICES', "Теперь у вас два варианта, вы можете либо <a href=\"{0}\">просмотреть ваш сайт</a>, либо <a href=\"{1}\">войти в панель управления</a>.");
define('_RU_INSTALL_FINISH_MOVE_LOG', "Внимание: если вы включили ведение журнала на первом шаге установки, мы советуем вам сохранить его, а затем переместить/удалить.");
define('_RU_INSTALL_FINISH_THANKS', "Спасибо за использование Jaws!");
