<?php
/**
 * Meta Data
 *
 * "Project-Id-Version: Install"
 * "Last-Translator: Uğur YILDIZ <uguryildiz@kocaeli.edu.tr>"
 * "Language-Team: TR"
 * "MIME-Version: 1.0"
 * "Content-Type: text/plain; charset=UTF-8"
 * "Content-Transfer-Encoding: 8bit"
 */

define('_TR_INSTALL_INTRODUCTION', "Giriş");
define('_TR_INSTALL_AUTHENTICATION', "Yetkilendirme");
define('_TR_INSTALL_REQUIREMENTS', "Gereklilikler");
define('_TR_INSTALL_DATABASE', "Veritabanı");
define('_TR_INSTALL_CREATEUSER', "Kullanıcı Oluşturma");
define('_TR_INSTALL_SETTINGS', "Ayarlar");
define('_TR_INSTALL_WRITECONFIG', "Ayarları Kaydetme");
define('_TR_INSTALL_FINISHED', "Bitti");
define('_TR_INSTALL_INTRO_WELCOME', "Jaws Yükleyicisine Hoşgeldiniz.");
define('_TR_INSTALL_INTRO_INSTALLER', "Yükleyiciyi kullanarak web sitenizi oluşturabilirsiniz, lütfen aşağıdaki işlemleri yaptığınızdan emin olun.");
define('_TR_INSTALL_INTRO_DATABASE', "Veritabanı ayrıntıları - sunucu adı, kullanıcı adı, şifre, veritabanı adı");
define('_TR_INSTALL_INTRO_FTP', "Dosya yükleme biçimi, muhtemelen FTP");
define('_TR_INSTALL_INTRO_MAIL', "E-İleti Sunucu Bilgileri ( sunucu adı, kullanıcı adı şifre) Eğer bir E-İleti sunucusu kullanıyorsanız tabiki");
define('_TR_INSTALL_INTRO_LOG', "İşlem kayıtları (ve hatalar) ({0}) yükleme dosyasına kaydedilir");
define('_TR_INSTALL_INTRO_LOG_ERROR', "Not: İşlem kayıtlarını (ve hataları) yükleme dosyasına kaydetmek istiyorsanız ({0}) dizin izinlerini yazma uygun olarak ayarlamalı ve bu sayfayı yenilemelisiniz. ");
define('_TR_INSTALL_AUTH_PATH_INFO', "Bu site nin size ait olduğundan emin olun, lütfen  çağrılan {0} dosyasını {1} Jaws yükleme dizininde oluşturunuz");
define('_TR_INSTALL_AUTH_UPLOAD', "Jaws dosyalarını yüklediğiniz aynı yöntemle dosyayı yükleyebilirsiniz.");
define('_TR_INSTALL_AUTH_KEY_INFO', "Dosya aşağıdaki kutuda gösterilen kodu içermelidir, başka birşey içermemelidir.");
define('_TR_INSTALL_AUTH_ENABLE_SECURITY', "Güvenli yüklemeyi etkinleştir ( RSA Tarafından Desteklendi)");
define('_TR_INSTALL_AUTH_ERROR_RSA_KEY_GENERATION', "RSA anahtar üretiminde hata, lütfen tekrar deneyin");
define('_TR_INSTALL_AUTH_ERROR_NO_MATH_EXTENSION', "RSA anahtar üretiminde hata, herhangi bir matematik eklentisi yok.");
define('_TR_INSTALL_AUTH_ERROR_KEY_FILE', "Anahtar(key) dosyası ({0}) bulunamadı,lütfen oluşturduğunuzdan emin olun ve web sunucusunun okuma izinleri bulunsun.");
define('_TR_INSTALL_AUTH_ERROR_KEY_MATCH', "Anahtar dosya (key) ({0}) bulundu, fakat aşağıdaki anahtar ile eşleşmedi, lütfen girdiğiniz anahtarı doğruluğunu kontrol ediniz.");
define('_TR_INSTALL_REQ_REQUIREMENT', "Gereksinim");
define('_TR_INSTALL_REQ_OPTIONAL', "İsteğe bağlı fakat önerilir");
define('_TR_INSTALL_REQ_RECOMMENDED', "Önerilen");
define('_TR_INSTALL_REQ_DIRECTIVE', "Yönerge");
define('_TR_INSTALL_REQ_ACTUAL', "Güncel");
define('_TR_INSTALL_REQ_RESULT', "Sonuç");
define('_TR_INSTALL_REQ_PHP_VERSION', "PHP Versiyonu");
define('_TR_INSTALL_REQ_GREATER_THAN', ">={0}");
define('_TR_INSTALL_REQ_DIRECTORY', "{0} dizini");
define('_TR_INSTALL_REQ_EXTENSION', "{0} eklentisi");
define('_TR_INSTALL_REQ_FILE_UPLOAD', "Dosya Yükleme");
define('_TR_INSTALL_REQ_SAFE_MODE', "Güvenli Kip (Safe Mode)");
define('_TR_INSTALL_REQ_READABLE', "Okunabilir");
define('_TR_INSTALL_REQ_WRITABLE', "Yazılabilir");
define('_TR_INSTALL_REQ_OK', "Tamam");
define('_TR_INSTALL_REQ_BAD', "Geçersiz");
define('_TR_INSTALL_REQ_OFF', "Kapalı");
define('_TR_INSTALL_REQ_ON', "Açık");
define('_TR_INSTALL_REQ_RESPONSE_DIR_PERMISSION', "{0} dizinlerinden biri okunabilir yada yazılabilir değil, lütfen izinleri düzeltin.");
define('_TR_INSTALL_REQ_RESPONSE_PHP_VERSION', "Jaws yükleyebilmek için en düşük PHP versiyonu {0}, bu nedenle PHP versiyonunuzu yükseltmelisiniz.");
define('_TR_INSTALL_REQ_RESPONSE_DIRS_PERMISSION', "{0} dizinlerinden herhangi biri okunabilir yada yazılabilir değil,  lütfen izinleri düzeltin. ");
define('_TR_INSTALL_REQ_RESPONSE_EXTENSION', "Jaws'ı kullanabilmek için {0} eklentisi gereklidir.");
define('_TR_INSTALL_DB_INFO', "Şimdi veritabanınızı ayarlamanız gerekli. Kullanılan bilgiler daha sonra gösterilecektir.");
define('_TR_INSTALL_DB_NOTICE', "Bu işlemin gerçekleşebilmesi için daha önceden oluşturulmuş veritabanının ayrıntıları sağlanmalıdır.");
define('_TR_INSTALL_DB_HOST', "Sunucu Adı");
define('_TR_INSTALL_DB_HOST_INFO', "Eğer bilmiyorsanız en güvenlisi {0} olarak bırakmanız");
define('_TR_INSTALL_DB_DRIVER', "Sürücü");
define('_TR_INSTALL_DB_USER', "Kullanıcı Adı");
define('_TR_INSTALL_DB_PASS', "Şifre");
define('_TR_INSTALL_DB_IS_ADMIN', "Veritabanı Yöneticisi Mi?");
define('_TR_INSTALL_DB_NAME', "Veritabanı Adı");
define('_TR_INSTALL_DB_PATH', "Veritabanı Yolu");
define('_TR_INSTALL_DB_PATH_INFO', "Sadece bu alanı doldurmayabilirsiniz. İsterseniz SQLite, Interbase ve Firebird sürücünden veritabanı yolunu değiştirebilirsiniz.");
define('_TR_INSTALL_DB_PORT', "Veritabanı Portu");
define('_TR_INSTALL_DB_PORT_INFO', "Sadece veritabanınız başka bir porttan çalışıyorsa doldurunuz. Eğer bir fikriniz yok ise genellikle  veritabanları varsayılan porttan çalışırlarve bu yüzden biz boş bırakmanızı tavsiye ederiz.");
define('_TR_INSTALL_DB_PREFIX', "Tablo Ön Eki");
define('_TR_INSTALL_DB_PREFIX_INFO', "Aynı veritabanında birden fazla Jaws sitesi çalıştıracaksanız Ön Ek veritabanı tablo isimlerinini önüne eklenecektir. Örneğin <b>blog_</b>");
define('_TR_INSTALL_DB_RESPONSE_PATH', "Veritabanı yolu mevcut değil");
define('_TR_INSTALL_DB_RESPONSE_PORT', "Port sadece sayısal bir değer olabilir");
define('_TR_INSTALL_DB_RESPONSE_INCOMPLETE', "Tablo Ön Eki ve Port dıındaki tüm alanları doldurmalısınız.");
define('_TR_INSTALL_DB_RESPONSE_CONNECT_FAILED', "Veritabanına bağlanırken bir sorun oluştu, lütfen ayrıntıları kontrol edin ve tekrar deneyin. ");
define('_TR_INSTALL_DB_RESPONSE_GADGET_INSTALL', "{0} Çekirdek araçları yüklemede sorun oluştu");
define('_TR_INSTALL_DB_RESPONSE_SETTINGS', "Veritabanı ayarlanırken bir sorun oluştu.");
define('_TR_INSTALL_USER_INFO', "Şimdi bir kullanıcı hesabı oluşturabilirsiniz. ");
define('_TR_INSTALL_USER_NOTICE', "Unutmayın başa birilerinin tahmin edebileceği kolay bir şifre sitenizin kontrolünü ele geçirmelerine neden olabilir.");
define('_TR_INSTALL_USER_USER', "Kullanıcı Adı");
define('_TR_INSTALL_USER_USER_INFO', "Oturum açma adınız");
define('_TR_INSTALL_USER_PASS', "Şifre");
define('_TR_INSTALL_USER_REPEAT', "Tekrar");
define('_TR_INSTALL_USER_REPEAT_INFO', "Şifrenizde yazma hatası yapmadığınızdan emin olun.");
define('_TR_INSTALL_USER_NAME', "Adınız");
define('_TR_INSTALL_USER_NAME_INFO', "Gerçek Adınız");
define('_TR_INSTALL_USER_EMAIL', "E-İleti Adresiniz");
define('_TR_INSTALL_USER_RESPONSE_PASS_MISMATCH', "Şifreniz tekrar kutusundaki ile eşleşmiyor, lütfen tekrar deneyin.");
define('_TR_INSTALL_USER_RESPONSE_INCOMPLETE', "Kullanıcı Adı, Şifre ve Tekrar kutusu doldurulmalı.");
define('_TR_INSTALL_USER_RESPONSE_CREATE_FAILED', "Kullanıcı oluşturulurken bir sorun oluştu.");
define('_TR_INSTALL_SETTINGS_INFO', "Şimdi siteniz için varsayılan ayarları yapabilirsiniz. Bu ayarları daha sonra Kontrol Panelinden ve seçilen Ayarlardan değiştirebilirsiniz.");
define('_TR_INSTALL_SETTINGS_SITE_NAME', "Site Adı");
define('_TR_INSTALL_SETTINGS_SITE_NAME_INFO', "Bu Ad sitenizde görüntülenecektir.");
define('_TR_INSTALL_SETTINGS_SLOGAN', "Açıklama");
define('_TR_INSTALL_SETTINGS_SLOGAN_INFO', "Siteniz için uzun bir açıklama");
define('_TR_INSTALL_SETTINGS_DEFAULT_GADGET', "Varsayılan Araç");
define('_TR_INSTALL_SETTINGS_DEFAULT_GADGET_INFO', "Bu araç siteniz herhangi birisi tarafından ziyaret edildiğinde gösterilecektir.");
define('_TR_INSTALL_SETTINGS_SITE_LANGUAGE', "Site Dili");
define('_TR_INSTALL_SETTINGS_SITE_LANGUAGE_INFO', "Ana dil sitede gösterilecektir");
define('_TR_INSTALL_USER_RESPONSE_SITE_NAME_EMPTY', "Site Adının doldurulması gereklidir.");
define('_TR_INSTALL_CONFIG_INFO', "Ayar dosyası kaydedilmelidir.");
define('_TR_INSTALL_CONFIG_SOLUTION', "Bunu iki yolla yapabilirsiniz.");
define('_TR_INSTALL_CONFIG_SOLUTION_PERMISSION', "{0} dosyasını yazılabilir yapın ve ileri düğmesine tıklayın, yükleyici ayar bilgilerini kendisi kaydedecektir");
define('_TR_INSTALL_CONFIG_SOLUTION_UPLOAD', "Aşağıdaki içeriği Kopyalayın ve {0} dosyasının içine yapıştırıp kaydedin.");
define('_TR_INSTALL_CONFIG_RESPONSE_WRITE_FAILED', "Ayar dosyası yazılırken bilinmeyen bir hata oluştu.");
define('_TR_INSTALL_CONFIG_RESPONSE_MAKE_CONFIG', "config dizini yada {0} dosyasını elle yazılabilir yapmalısınız.");
define('_TR_INSTALL_FINISH_INFO', "Web sitenizin ayaları bitti!");
define('_TR_INSTALL_FINISH_CHOICES', "Şimdi iki seçeneğiniz var, <a href=\"{0}\">sitenizi görüntüleyebilir</a> yada <a href=\"{1}\">kontrol panelinde oturum açabilirsiniz.</a>");
define('_TR_INSTALL_FINISH_MOVE_LOG', "Not:Eğer kayıt seçeneğini ilk bölünde etkinleştirdiyseniz tavsiyemiz dosyayı saklayın ve taşıyın yada silin.");
define('_TR_INSTALL_FINISH_THANKS', "Jaws kullandığınız için teşekkürler!");
