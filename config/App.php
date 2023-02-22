<?php

return [
   'default_db' => 'mysql',
   
   // Партиционирование таблиц в mySQL. Выполнить для уменьшения нагрузки
   'db' => [
        'mysql' => [
            'DRIVER' => 'mysql',
            'DB_PERSISTENCY' => true,
            'DB_SERVER' => 'localhost',
            'DB_DATABASE' => 'user',
            'DB_USERNAME' => 'dbname',
            'DB_PASSWORD' => 'psw',
            'DB_CHARSET' => 'utf8',
        ],
    ],
    
    'loginadmin' => 'admin',
    'passwordadmin' => 'admin',

    'domain' => '',
    'is_ssl' => false,

    'LiqPayIsTest' => false,
    'LiqPayUrlRequest' => 'https://www.liqpay.ua/api/3/checkout',
    'LiqPayPublicKey' => '',
    'LiqPaySecretKey' => '',
    'LiqPayTestPublicKey' => '',
    'LiqPayTestSecretKey' => '',

    'InterkassaIsTest' => false,
    'InterkassaUrlRequest' => 'https://sci.interkassa.com/',
    'InterkassaId' => '',
    'InterkassaSecretKey' => '',
    'InterkassaTestKey' => '',
    
    'countSocialsByItem' => 20,
    
    // Получение постоянного токена по ссылке прочесть. Роут Route::get('/fb/get-token', 'fb.get_token', 'MainController@getFbTokensUser'); пошагово
    // http://qaru.site/questions/43534/facebook-permanent-page-access-token
    'FbAppId' => '381889972166020',
    'FbAppSecret' => '',
    'FbClientToken' => '',
    'FbAccessTokenShortLived' => '',
    'FbAccessTokenLongLived' => '',
    'FbAccessTokenUnlimitedLived' => '',
    'FbAccountId' => '',
    
    // https://cpa.rip/stati/push-on-site/
    'GooglePushApiKey' => '',
    'GooglePushAuthDomain' => '',
    'GooglePushDatabaseURL' => '',
    'GooglePushSendURL' => '',
    'GooglePushProjectId' => '',
    'GooglePushStorageBucket' => '',
    'GooglePushMessagingSenderId' => '',
    'GooglePushAppId' => '',
    'GooglePushServerKey' => '',
    'GooglePushPublicKey' => '',
    'GooglePushUrlSaveToken' => null,
    
    // https://ruseller.com/lessons.php?rub=37&id=1668
    'OAuthGoogleUrl' => 'https://accounts.google.com/o/oauth2/auth',
    'OAuthGoogleUrlToken' => 'https://accounts.google.com/o/oauth2/token',
    'OAuthGoogleUrlUserInfo' => 'https://www.googleapis.com/oauth2/v1/userinfo',
    'OAuthGoogleId' => '',
    'OAuthGoogleSecretKey' => '',

    // https://ruseller.com/lessons.php?rub=37&id=1659
    'OAuthVKUrl' => 'https://oauth.vk.com/authorize',
    'OAuthVKUrlAccessToken' => 'https://oauth.vk.com/access_token',
    'OAuthVKUrlUsersGet' => 'https://api.vk.com/method/users.get',
    'OAuthVKId' => '',
    'OAuthVKSecretKey' => '',

    // https://ruseller.com/lessons.php?id=1670
    'OAuthFbUrl' => 'https://www.facebook.com/dialog/oauth',
    'OAuthFbUrlAccessToken' => 'https://graph.facebook.com/oauth/access_token',
    'OAuthFbUrlAccessMe' => 'https://graph.facebook.com/me',
    'OAuthFbId' => '',
    'OAuthFbSecretKey' => '',

    // https://ruseller.com/lessons.php?id=2021
    'AppTwitterTokenUrl' => 'https://api.twitter.com/oauth/request_token',
    'AppTwitterAuthUrl' => 'https://api.twitter.com/oauth/authorize',
    'AppTwitterAccecTokenUrl' => 'https://api.twitter.com/oauth/access_token',
    'AppTwitterAccountDataUrl' => 'https://api.twitter.com/1.1/users/show.json',
    'AppTwitterId' => '',
    'AppTwitterKey' => '',
    'AppTwitterSecretKey' => '',
    'AppTwitterToken' => '',
    'AppTwitterSecretToken' => '',
    
    // https://docs.microsoft.com/en-us/linkedin/shared/authentication/authorization-code-flow?context=linkedin/context
    // https://qna.habr.com/q/562429
    
    'AppTwitterOAuthToken' => '',
    'AppTwitterOAuthSecretToken' => '',
    
    'GoogleApiKey' => 'AIzaSyCr6MhFHZ0EZmdQu9M4GvLtSnay7S6JIw4',
    'YandexMapApiKey' => '8766e143-f480-436e-8a02-d1dc271638a5',

    'isDBtesting' => true,

    'SITE_ROOT' => dirname(__dir__),
    'base_dir' => dirname(__dir__),
    'storage_dir' => dirname(__dir__) . '/storage',
    'public_dir' => dirname(__dir__) . '/public',
    'cache_dir' => dirname(__dir__) . '/storage/cache',
    'site_dir' => '', // Папка в которой сайт

    'DIR_TMP' => dirname(__dir__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'tmp',
    'DIR_DATA' => dirname(__dir__) . DIRECTORY_SEPARATOR . "storage" . DIRECTORY_SEPARATOR . "data",

    // Эти значения должны быть равны true на этапе разработки
    'IS_WARNING_FATAL' => false,
    'DEBUGGING' => true,

    // Типы ошибок, о которых должны составляться сообщения
    'ERROR_TYPES' => E_ERROR, // 0 E_ALL E_WARNING E_ERROR

    // По умолчанию мы не записываем сообщения в журнал
    'LOG_ERRORS' => true,
    'LOG_ERRORS_FILE' => dirname(__dir__) . DIRECTORY_SEPARATOR . 'errors_log.txt',

    // Настройки отправки сообщений об ошибках
    'SEND_ERROR_MAIL' => false, // Не отправляем
    'ADMIN_ERROR_MAIL' => 'user@gmail.com',
    'SENDMAIL_FROM' => 'user@gmail.com',
    
    // Если заблокировано то нужно перейти по ссылкам и дать доступ:
    // https://accounts.google.com/DisplayUnlockCaptcha
    // https://myaccount.google.com/lesssecureapps
    'SMTP_MAIL' => true,
    'SMTP_MAIL_FROM' => 'user@gmail.com',
    'SMTP_MAIL_REPLY' => 'user@gmail.com',
    'SMTP_MAIL_SERVER' => 'ssl://smtp.gmail.com',
    'SMTP_MAIL_LOGIN' => 'user',
    'SMTP_MAIL_PASSWORD' => 'psw',
    'SMTP_MAIL_PORT' => '465',

    // Есть ли опции.
    'TYPE_OPTIONS' => 'xml', // xml or array

    // Порт HTTP-сервера (можно пропустить, если используется порт 80)
    'HTTP_SERVER_PORT' => '80',
    
    'cache_view' => [
       // 'model/index' => 2,
    ],
    
    'cache_data' => [
        'dataArrayProjectsCache' => [
            'interval' => 60,
            'type' => 'url',
        ],
        'dataArrayFreelancersCache' => [
            'interval' => 60,
            'type' => 'url',
        ],
    ],
];
