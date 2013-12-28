<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Policy"
 * "Last-Translator: Ali Fazelzadeh <afz@php.net>"
 * "Language-Team: FA"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_FA_POLICY_NAME', "تدابیر مدیریتی");
define('_FA_POLICY_DESCRIPTION', "تدابیر مدیریتی - امنیتی وب سایت");
define('_FA_POLICY_ACL_MANAGEPOLICY', "دسترسی به ابزار تدابیر مدیریتی - امنیتی");
define('_FA_POLICY_ACL_IPBLOCKING', "بخش محدودیت IP ها");
define('_FA_POLICY_ACL_MANAGEIPS', "ویرایش IP های محدود شده");
define('_FA_POLICY_ACL_AGENTBLOCKING', "بخش محدویت Agent ها");
define('_FA_POLICY_ACL_MANAGEAGENTS', "ویرایش Agent های محدود شده");
define('_FA_POLICY_ACL_ENCRYPTION', "مدیریت رمزنگاری");
define('_FA_POLICY_ACL_MANAGEENCRYPTIONKEY', "مدیریت کلیدهای رمزنگاری");
define('_FA_POLICY_ACL_ANTISPAM', "مدیریت بخش ضد هرزنامه");
define('_FA_POLICY_ACL_ADVANCEDPOLICIES', "بخش تنظیمات پیشرفته");
define('_FA_POLICY_BLOCKED', "بسته شود");
define('_FA_POLICY_IP_BLOCKING', "محدودیت IP");
define('_FA_POLICY_IP_ADDRESS', "آدرس IP");
define('_FA_POLICY_IP_RANGE', "محدوده IP");
define('_FA_POLICY_IP_BLOCK_UNDEFINED', "بستن آدرسهای ناشناخته");
define('_FA_POLICY_AGENT_BLOCKING', "محدودیت Agent");
define('_FA_POLICY_AGENT', "Agent");
define('_FA_POLICY_AGENT_BLOCK_UNDEFINED', "بستن Agentهای ناشناخته");
define('_FA_POLICY_ENCRYPTION', "رمزنگاری");
define('_FA_POLICY_ENCRYPTION_KEY_AGE', "عمر کلید");
define('_FA_POLICY_ENCRYPTION_KEY_LEN', "طول کلید");
define('_FA_POLICY_ENCRYPTION_64BIT', "64 بیت");
define('_FA_POLICY_ENCRYPTION_128BIT', "128 بیت");
define('_FA_POLICY_ENCRYPTION_256BIT', " 256 بیت");
define('_FA_POLICY_ENCRYPTION_512BIT', "512 بیت");
define('_FA_POLICY_ENCRYPTION_1024BIT', "1024 بیت");
define('_FA_POLICY_ENCRYPTION_KEY_START_DATE', "تاریخ ایجاد کلید");
define('_FA_POLICY_ANTISPAM', "ضد هرزنامه");
define('_FA_POLICY_ANTISPAM_CAPTCHA', "تصویر امنیتی");
define('_FA_POLICY_ANTISPAM_CAPTCHA_ALWAYS', "همواره");
define('_FA_POLICY_ANTISPAM_CAPTCHA_ANONYMOUS', "فقط بازدیدکنندگان مهمان");
define('_FA_POLICY_ANTISPAM_FILTER', "فیلتر هرزنامه");
define('_FA_POLICY_ANTISPAM_PROTECTEMAIL', "حفاظت از آدرس های ایمیل");
define('_FA_POLICY_CAPTCHA_MATH_PLUS', "{0} بعلاوه {1} برابر است با؟");
define('_FA_POLICY_CAPTCHA_MATH_MINUS', "{0} منهای {1} برابر است با؟");
define('_FA_POLICY_CAPTCHA_MATH_MULTIPLY', "{0} ضربدر {1} برابر است با؟");
define('_FA_POLICY_ADVANCED_POLICIES', "تدابیر پیشرفته");
define('_FA_POLICY_PASSWD_COMPLEXITY', "پیچیدگی کلمه رمز");
define('_FA_POLICY_PASSWD_BAD_COUNT', "تعداد بار ورود اشتباه کلمه رمز");
define('_FA_POLICY_PASSWD_LOCKEDOUT_TIME', "مدت زمان قفل شده کلمه رمز");
define('_FA_POLICY_PASSWD_MAX_AGE', "حداکثر طول عمر کلمه رمز");
define('_FA_POLICY_PASSWD_RESISTANT', "پایدار");
define('_FA_POLICY_PASSWD_MIN_LEN', "حداقل طول کلمه رمز");
define('_FA_POLICY_LOGIN_CAPTCHA', "تصویر امنیتی بلوک لاگین");
define('_FA_POLICY_LOGIN_CAPTCHA_AFTER_WRONG', "فعالسازی پس از {0} بار ورود ناموفق");
define('_FA_POLICY_XSS_PARSING_LEVEL', "میزان بررسی XSS");
define('_FA_POLICY_XSS_PARSING_NORMAL', "معمولی");
define('_FA_POLICY_XSS_PARSING_PARANOID', "سخت‌گیری");
define('_FA_POLICY_SESSION_IDLE_TIMEOUT', "طول عمر نشست غیر فعال");
define('_FA_POLICY_SESSION_REMEMBER_TIMEOUT', "طول عمر (مرا به خاطر بسپار)");
define('_FA_POLICY_RESPONSE_IP_ADDED', "محدوده IP با موفقیت ثبت شد");
define('_FA_POLICY_RESPONSE_IP_NOT_ADDED', "خطا هنگام ثبت محدوده IP");
define('_FA_POLICY_RESPONSE_IP_EDITED', "تغییرات محدوده IP ثبت شد");
define('_FA_POLICY_RESPONSE_IP_NOT_EDITED', "خطا هنگام ثبت تغییرات");
define('_FA_POLICY_RESPONSE_IP_DELETED', "محدوده IP با موفقیت حذف شد");
define('_FA_POLICY_RESPONSE_IP_NOT_DELETED', "خطا هنگام حذف محدوده IP");
define('_FA_POLICY_RESPONSE_CONFIRM_DELETE_IP', "آیا قصد دارید این محدوده IP را حذف کنید؟");
define('_FA_POLICY_RESPONSE_AGENT_ADDED', "Agent با موفقیت اضافه شد");
define('_FA_POLICY_RESPONSE_AGENT_NOT_ADDEDD', "خطا هنگام افزودن Agent");
define('_FA_POLICY_RESPONSE_AGENT_EDITED', "تغییرات Agent ثبت شد");
define('_FA_POLICY_RESPONSE_AGENT_NOT_EDITED', "خطا هنگام ثبت تغییرات");
define('_FA_POLICY_RESPONSE_AGENT_DELETED', "Agent با موفقیت حذف شد");
define('_FA_POLICY_RESPONSE_AGENT_NOT_DELETED', "خطا هنگام حذف Agent");
define('_FA_POLICY_RESPONSE_CONFIRM_DELETE_AGENT', "آیا قصد دارید این Agent را حذف کنید؟");
define('_FA_POLICY_RESPONSE_ENCRYPTION_UPDATED', "تنظیمات رمزنگاری با موفقیت ثبت شد");
define('_FA_POLICY_RESPONSE_ANTISPAM_UPDATED', "تنظیمات ثبت شد");
define('_FA_POLICY_RESPONSE_ADVANCED_POLICIES_UPDATED', "تنظیمات پیشرفته با موفقیت ثبت شد");
define('_FA_POLICY_RESPONSE_PROPERTIES_UPDATED', "تنظیمات با موفقیت ثبت شد");
define('_FA_POLICY_RESPONSE_PROPERTIES_NOT_UPDATED', "خطا هنگام ثبت تنظیمات");
