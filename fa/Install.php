<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: FA"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_FA_INSTALL_INTRODUCTION', "معرفی");
define('_FA_INSTALL_AUTHENTICATION', "تاییدیه");
define('_FA_INSTALL_REQUIREMENTS', "نیازمندیها");
define('_FA_INSTALL_DATABASE', "دیتابیس");
define('_FA_INSTALL_CREATEUSER', "ایجاد کاربر");
define('_FA_INSTALL_SETTINGS', "تنظیمات");
define('_FA_INSTALL_WRITECONFIG', "ثبت پیکربندی");
define('_FA_INSTALL_FINISHED', "پایان");
define('_FA_INSTALL_INTRO_WELCOME', "به ویزارد نصب جاوز خوش آمدید.");
define('_FA_INSTALL_INTRO_INSTALLER', "نصب کننده جاوز، به شما را در برپایی سایت خود کمک میکند، لطفا قبل از هر چیز از دارا بودن موارد زیر مطمئن شوید");
define('_FA_INSTALL_INTRO_DATABASE', "جزئیات ارتباط با دیتابیس - آدرس سرور، نام کاربری، گذرواژه و نام دیتابیس");
define('_FA_INSTALL_INTRO_FTP', "راهی برای آپلود فایلها، احتمالا FTP");
define('_FA_INSTALL_INTRO_MAIL', "جزئیات مربوط به سرور ایمیل (آدرس سرور، نام کاربری و گذرواژه)، اگر از سرور ایمیل استفاده می کنید.");
define('_FA_INSTALL_INTRO_LOG', "ثبت گزارش مراحل نصب و خطاهای احتمالی در فایل ({0})");
define('_FA_INSTALL_INTRO_LOG_ERROR', "اگر شما قصد ثبت گزارش مراحل نصب و خطاهای احتمالی را دارید، لطفا دسترسی به دایرکتوری ({0}) را به صورت قابل نوشتن تنظیم نمایید و این صفحه را دوباره بارگذاری نمایید. ");
define('_FA_INSTALL_AUTH_PATH_INFO', "برای اطمینان از اینکه شما صاحب این سایت هستید، لطفا فایلی به نام {0} در دایرکتوری نصب جاوز ({1}) بسازید.");
define('_FA_INSTALL_AUTH_UPLOAD', "برای آپلود فایل از همان طریقی که جاوز را آپلود کردید، اقدام نمایید.");
define('_FA_INSTALL_AUTH_KEY_INFO', "فایل میبایستی فقط حاوی متن زیر باشد.");
define('_FA_INSTALL_AUTH_ENABLE_SECURITY', "فعال سازی نصب جاوز در مد امن(توانمند شده با الگوریتم رمزنگاری RSA)");
define('_FA_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "خطا هنگام تولید کلید رمز. لطفا دوباره سعی کنید.");
define('_FA_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "خطا هنگام تولید کلید رمز. هیچکدام از الحاقی محاسباتی روی PHP فعال نیستند.");
define('_FA_INSTALL_AUTH_ERROR_KEY_FILE', "فایل کلید ({0}) پیدا نشد، لطفا از ساخت آن و همچنین از قابل خواندن بودن آن مطمئن شوید.");
define('_FA_INSTALL_AUTH_ERROR_KEY_MATCH', "متن فایل کلید ({0}) با متن زیر یکسان نمیباشد، از ورود صحیح متن زیر مطمئن شوید.");
define('_FA_INSTALL_REQ_REQUIREMENT', "نیازمندیها");
define('_FA_INSTALL_REQ_OPTIONAL', "موارد زیر ضروری نبوده ولی پیشنهاد میگردند");
define('_FA_INSTALL_REQ_RECOMMENDED', "پیشنهادی");
define('_FA_INSTALL_REQ_DIRECTIVE', "عنوان");
define('_FA_INSTALL_REQ_ACTUAL', "واقعی");
define('_FA_INSTALL_REQ_RESULT', "نتیجه");
define('_FA_INSTALL_REQ_PHP_VERSION', "نسخه PHP");
define('_FA_INSTALL_REQ_GREATER_THAN', "حداقل {0}");
define('_FA_INSTALL_REQ_DIRECTORY', "دایرکتوری {0}");
define('_FA_INSTALL_REQ_EXTENSION', "الحاقی {0}");
define('_FA_INSTALL_REQ_FILE_UPLOAD', "آپلود فایل");
define('_FA_INSTALL_REQ_SAFE_MODE', "حالت Safe mode");
define('_FA_INSTALL_REQ_READABLE', "خواندنی");
define('_FA_INSTALL_REQ_WRITABLE', "نوشتنی");
define('_FA_INSTALL_REQ_OK', "تایید");
define('_FA_INSTALL_REQ_BAD', "رد");
define('_FA_INSTALL_REQ_OFF', "غیرفعال");
define('_FA_INSTALL_REQ_ON', "فعال");
define('_FA_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "دایرکتوری {0} مشکل دسترسی (خواندنی، نوشتنی) دارد، لطفا مشکل آنرا مرتفع نمایید.");
define('_FA_INSTALL_REQ_RESPONSE_PHP_VERSION', "حداقل نسخه مورد نیاز برای نصب جاوز، {0} میباشد، بنابراین میبایستی نسخه PHP خود را ارتقاء دهید.");
define('_FA_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "دایرکتورهای زیر که با علامت ({0}) مشخص شده اند، نیاز است خواندنی یا نوشتنی باشند، لطفا مشکل دسترسیهای آنها را مرتفع سازید.");
define('_FA_INSTALL_REQ_RESPONSE_EXTENSION', "الحاقی {0} برای استفاده از جاوز ضروری است.");
define('_FA_INSTALL_DB_INFO', "شما الان در مرحله نصب دیتابیس هستید، که برای نگهداری اطلاعات و همچنین تنظیمات سایت شما، مورد استفاده قرار میگیرد.");
define('_FA_INSTALL_DB_NOTICE', "دیتابیسی که جزئیات آنرا در زیر وارد میکنید، میبایستی قبلا ساخته شده باشد تا این مرحله نیز با موفقیت انجام شود.");
define('_FA_INSTALL_DB_HOST', "آدرس سرور");
define('_FA_INSTALL_DB_HOST_INFO', "اگر درباره آن چیزی نمیدانید، آنرا با {0} پر نمایید.");
define('_FA_INSTALL_DB_DRIVER', "نوع دیتابیس");
define('_FA_INSTALL_DB_USER', "نام کاربری");
define('_FA_INSTALL_DB_PASS', "کلمه رمز");
define('_FA_INSTALL_DB_IS_ADMIN', "آیا دسترسی سوپروایزری دارد؟");
define('_FA_INSTALL_DB_NAME', "نام دیتابیس");
define('_FA_INSTALL_DB_PATH', "مسیر دیتابیس");
define('_FA_INSTALL_DB_PATH_INFO', "این فیلد در صورتی پر کنید که قصد تغییر مسیر دیتابیس در SQLite، Interbase و یا Firebird را داشته باشید.");
define('_FA_INSTALL_DB_PORT', "پورت دیتابیس");
define('_FA_INSTALL_DB_PORT_INFO', "این فیلد را فقط وقتی پر کنید که دیتابیس شما روی پورتی غیر از حالت پیش فرضش نصب شده باشد. البته به صورت معمول دیتابیس ها روی پورت پیش فرض نصب میشوند، لذا اگر اطلاع کافی در مورد آن ندارید آنرا بصورت خالی نگه دارید.");
define('_FA_INSTALL_DB_PREFIX', "پیش نام جدول");
define('_FA_INSTALL_DB_PREFIX_INFO', "کلمه ای که قبل از نام جداول قرار خواهد گرفت، بوسیله آن می توان بیش از یک جاوز را برروی یک دیتابیس نصب کرد، به عنوان مثال blog_");
define('_FA_INSTALL_DB_RESPONSE_PATH', "مسیر دیتابیس وجود ندارد.");
define('_FA_INSTALL_DB_RESPONSE_PORT', "پورت تنها می تواند شامل اعداد باشد.");
define('_FA_INSTALL_DB_RESPONSE_INCOMPLETE', "پر کردن همه فیلدها (بغیر از مسیر دیتابیس، پیش نام جداول و پورت دیتابیس) اجباری است.");
define('_FA_INSTALL_DB_RESPONSE_CONNECT_FAILED', "خطایی در هنگام اتصال به دیتابیس رخ داده است، لطفا جزئیات را بررسی نموده و دوباره سعی نمایید.");
define('_FA_INSTALL_DB_RESPONSE_GADGET_INSTALL', "اشکالی در نصب ابزار اصلی {0} رخ داده است.");
define('_FA_INSTALL_DB_RESPONSE_SETTINGS', "اشکالی در هنگام انجام تنظیمات دیتابیس رخ داده است.");
define('_FA_INSTALL_USER_INFO', "حالا شما می توانید برای خود یک نام کابری ایجاد نمایید.");
define('_FA_INSTALL_USER_NOTICE', "لطفا از کلمه رمز ساده، که براحتی قابل حدس زدن باشد پرهیز کنید. چون اگر کسی کلمه رمز شما را داشته باشد کنترل کامل وب سایت شما را در اختیار خواهد داشت.");
define('_FA_INSTALL_USER_USER', "کد کاربر");
define('_FA_INSTALL_USER_USER_INFO', "نامی که به وسیله آن به وب سایت خود وارد شده و مطالبی که نوشته می شود با این نام نمایش داده می شوند.");
define('_FA_INSTALL_USER_PASS', "کلمه رمز");
define('_FA_INSTALL_USER_REPEAT', "تکرار");
define('_FA_INSTALL_USER_REPEAT_INFO', "کلمه عبور خود را تکرار نمایید تا اطمینان حاصل کنید در ورود آن اشتباه نکرده اید.");
define('_FA_INSTALL_USER_NAME', "نام");
define('_FA_INSTALL_USER_NAME_INFO', "نام واقعی شما");
define('_FA_INSTALL_USER_EMAIL', "پست الکترونیکی");
define('_FA_INSTALL_USER_RESPONSE_PASS_MISMATCH', "کلمه رمز و تکرار کلمه رمز با هم مطابقت ندارند، لطفا یکبار دیگر سعی نمایید.");
define('_FA_INSTALL_USER_RESPONSE_INCOMPLETE', "کد کاربر، کلمه رمز و تکرار کلمه رمز را تکمیل نمایید.");
define('_FA_INSTALL_USER_RESPONSE_CREATE_FAILED', "اشکالی در هنگام ایجاد کاربر رخ داده است.");
define('_FA_INSTALL_SETTINGS_INFO', "حالا شما می توانید تنظیمات پیش فرض برای سایت خود را انجام دهید. بعدا می توانید با ورود به بخش مدیریت وب سایت خود، با استفاده از ابزار تنظیمات کلی سایت، آنها را تغییر دهید.");
define('_FA_INSTALL_SETTINGS_SITE_NAME', "عنوان وب سایت");
define('_FA_INSTALL_SETTINGS_SITE_NAME_INFO', "نامی که برای وب سایت شما نمایش داده می شود");
define('_FA_INSTALL_SETTINGS_SLOGAN', "شعار وب سایت");
define('_FA_INSTALL_SETTINGS_SLOGAN_INFO', "یک توضیح درباره وب سایت شما");
define('_FA_INSTALL_SETTINGS_DEFAULT_GADGET', "ابزار پیش فرض");
define('_FA_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "ابزاری که خروجی آن به صورت پیش فرض در صفحه اول وب سایت نمایش داده میشود.");
define('_FA_INSTALL_SETTINGS_SITE_LANGUAGE', "زبان سایت");
define('_FA_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "زبانی که سایت تحت آن نمایش داده می شود.");
define('_FA_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "نام سایت را وارد نمایید.");
define('_FA_INSTALL_CONFIG_INFO', "حالا شما نیاز دارید که فایل پیکربندی خود را ذخیره نمایید.");
define('_FA_INSTALL_CONFIG_SOLUTION', "شما این کار را می توانید  از دو طریق انجام دهید");
define('_FA_INSTALL_CONFIG_SOLUTION_PERMISSION', "{0} را قابل نوشتن کنید و برروی کلید بعدی کلیک نمایید، با اینکار اجازه می دهید تنظیمات بوسیله نصب کننده ذخیره شوند.");
define('_FA_INSTALL_CONFIG_SOLUTION_UPLOAD', "محتویات کادر زیر را کپی کرده و در یک فایل الصاق نمایید و با نام {0} ذخیره کنید");
define('_FA_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "یک خطای ناشناخته در هنگام نوشتن فایل پیکربندی رخ داده است.");
define('_FA_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "شما باید پوشه config را قابل نوشتن نمایید و یا {0} را خودتان ایجاد نماید. ");
define('_FA_INSTALL_FINISH_INFO', "نصب وب سایت شما با موفقیت به پایان رسید!");
define('_FA_INSTALL_FINISH_CHOICES', "حالا شما دو انتخاب دارید <a href=\"{0}\">دیدن سایت</a> یا <a href=\"{1}\">ورود به کنترل پنل </a>.");
define('_FA_INSTALL_FINISH_MOVE_LOG', "تذکر: اگر شما ویژگی ثبت گزارش مراحل نصب را در اولین مرحله فعال نموده بودید ما به پیشنهاد می کنیم آنرا در مسیر دیگری ذخیره نمایید و سپس فایل مورد نظر را جابجا ویا حذف نمایید.");
define('_FA_INSTALL_FINISH_THANKS', "از اینکه جاوز را برای استفاده انتخاب نموده اید، متشکریم!");
