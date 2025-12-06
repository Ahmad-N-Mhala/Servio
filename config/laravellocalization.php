<?php

return [
    'supportedLocales' => [
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_US', 'rtl' => false],
        'ar' => ['name' => 'Arabic', 'script' => 'Arab', 'native' => 'العربية', 'regional' => 'ar_SA', 'rtl' => true],
        'fr' => ['name' => 'French', 'script' => 'Latn', 'native' => 'Français', 'regional' => 'fr_FR', 'rtl' => false],
        'es' => ['name' => 'Spanish', 'script' => 'Latn', 'native' => 'Español', 'regional' => 'es_ES', 'rtl' => false],
        'de' => ['name' => 'German', 'script' => 'Latn', 'native' => 'Deutsch', 'regional' => 'de_DE', 'rtl' => false],
        'zh' => ['name' => 'Chinese', 'script' => 'Hans', 'native' => '中文', 'regional' => 'zh_CN', 'rtl' => false],
    ],

    'useAcceptLanguageHeader' => true,

    'hideDefaultLocaleInURL' => false,

    'localesOrder' => ['en', 'ar', 'fr', 'es', 'de', 'zh'],

    'utf8suffix' => env('LARAVELLOCALIZATION_UTF8SUFFIX', '.UTF-8'),

    'urlsIgnored' => ['/skipped'],

    'httpMethodsIgnored' => ['POST', 'PUT', 'PATCH', 'DELETE'],

    'defaultLocale' => 'en',

    'locales' => [
        'en' => ['name' => 'English', 'script' => 'Latn', 'native' => 'English', 'regional' => 'en_US', 'rtl' => false],
        'ar' => ['name' => 'Arabic', 'script' => 'Arab', 'native' => 'العربية', 'regional' => 'ar_SA', 'rtl' => true],
        'fr' => ['name' => 'French', 'script' => 'Latn', 'native' => 'Français', 'regional' => 'fr_FR', 'rtl' => false],
        'es' => ['name' => 'Spanish', 'script' => 'Latn', 'native' => 'Español', 'regional' => 'es_ES', 'rtl' => false],
        'de' => ['name' => 'German', 'script' => 'Latn', 'native' => 'Deutsch', 'regional' => 'de_DE', 'rtl' => false],
        'zh' => ['name' => 'Chinese', 'script' => 'Hans', 'native' => '中文', 'regional' => 'zh_CN', 'rtl' => false],
    ],
];

