<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-02 15:12
 * @File name           : tinfo.inc.php
 */

$sysconf['template']['base'] = 'php';
// 1 = същият шаблон и на телефони (иначе SLiMS показва олекотения шаблон „lightweight“ без брандиране)
$sysconf['template']['responsive'] = 1;

$sysconf['template']['classic_library_subname'] = 1;
$sysconf['template']['classic_slide_transition'] = 'fade';
$sysconf['template']['classic_slide_animation'] = 'none';
$sysconf['template']['classic_slide_delay'] = 7000;
$sysconf['template']['classic_popular_collection'] = 1;
$sysconf['template']['classic_popular_collection_item'] = 6;
$sysconf['template']['classic_new_collection'] = 1;
$sysconf['template']['classic_new_collection_item'] = 6;
// Класацията на читателите показва имена на хора публично (GDPR), затова е изключена
$sysconf['template']['classic_top_reader'] = 0;
$sysconf['template']['classic_suggestion'] = 1;
$sysconf['template']['classic_map'] = 1;

// ---------------------------------------------------------------------------
// НЧ „Просвета 1925“ – с. Храбърско
// Източници: Регистър на читалищата (МК, рег. № 1401), ТРРЮЛНЦ (ЕИК 000760761),
// Община Божурище; координати – сградата на кметството в OpenStreetMap (way 336131099).
// Всички полета се редактират и от администрацията: Система → Шаблон → Персонализиране.
// ---------------------------------------------------------------------------
$sysconf['template']['prosveta_full_name'] = 'Народно читалище „Просвета 1925“ – с. Храбърско';
$sysconf['template']['prosveta_description'] = 'Библиотеката на НЧ „Просвета 1925“ в с. Храбърско, община Божурище: онлайн каталог с над 9000 тома, дигитален клуб и читалищни дейности от 1925 г.';
$sysconf['template']['prosveta_keywords'] = 'читалище, библиотека, Храбърско, Просвета 1925, Божурище, онлайн каталог, книги, дигитален клуб';
$sysconf['template']['prosveta_street'] = 'ул. „Липа“ № 6';
$sysconf['template']['prosveta_postcode'] = '2224';
$sysconf['template']['prosveta_address_html'] = 'ул. „Липа“ № 6 (сградата на кметството)<br>2224 с. Храбърско, общ. Божурище, обл. София';
$sysconf['template']['prosveta_phone'] = '0899 819 134';
$sysconf['template']['prosveta_email'] = 'n.ch.prosveta1925@abv.bg';
$sysconf['template']['prosveta_hours'] = 'Понеделник – петък, пълен работен ден<br><span class="prosveta-muted">Преди посещение уточнете часовете на тел. 0899 819 134.</span>';
$sysconf['template']['prosveta_lat'] = '42.80530';
$sysconf['template']['prosveta_lon'] = '23.06017';

$sysconf['template']['classic_map_link'] = 'https://maps.google.com/maps?q=42.80530,23.06017&hl=bg&z=17&output=embed';
$sysconf['template']['classic_map_desc'] = '<p>Читалището и библиотеката се помещават в сградата на кметството, на няколко минути пеша от ЖП гара Храбърско.</p><p><strong>Адрес:</strong> ул. „Липа“ № 6, 2224 с. Храбърско, общ. Божурище<br><strong>Телефон:</strong> <a href="tel:0899819134">0899 819 134</a><br><strong>E-mail:</strong> <a href="mailto:n.ch.prosveta1925@abv.bg">n.ch.prosveta1925@abv.bg</a></p>';
// Социалните мрежи се показват само ако има попълнен адрес
$sysconf['template']['classic_fb_link'] = '';
$sysconf['template']['classic_twitter_link'] = '';
$sysconf['template']['classic_youtube_link'] = '';
$sysconf['template']['classic_instagram_link'] = '';
$sysconf['template']['visitor_log_voice'] = 1;
$sysconf['template']['classic_footer_about_us'] = <<<HTML
<p>Основано през 1925 г. от учителите в селото, читалището и днес събира хората от Храбърско около книгата, самодейността и местните обичаи. Библиотеката му пази над 9000 тома за деца и възрастни.</p>
HTML;
$sysconf['template']['classic_library_disableslide'] = 0;


$sysconf['template']['option'][$sysconf['template']['theme']] = [
    'responsive' => [
        'dbfield' => 'responsive',
        'label' => __('Enable this theme for mobile?'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Yes, please!')],
            [0, __('No, I want use lighweight theme')]
        ]
    ],
    'subtitle' => [
        'dbfield' => 'classic_library_subname',
        'label' => __('Library Sub Name'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'disableslide' => [
        'dbfield' => 'classic_library_disableslide',
        'label' => __('Disable Slide'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, __('Yes')],
            [0, __('No')]
        ]
    ],
    'transition' => [
        'dbfield' => 'classic_slide_transition',
        'label' => __('Slide Transition'),
        'type' => 'dropdown',
        'default' => 'fade',
        'data' => [
            ['fade', __('Fade')],
            ['slideLeft', __('Slide Left')],
            ['slideRight', __('Slide Right')],
            ['slideUp', __('Slide Up')],
            ['slideDown', __('Slide Down')],
            ['zoomIn', __('Zoom In')],
            ['burn', __('Burn')],
            ['flash', __('Flash')]
        ]
    ],
    'animation' => [
        'dbfield' => 'classic_slide_animation',
        'label' => __('Slide Animation'),
        'type' => 'dropdown',
        'default' => 'none',
        'data' => [
            ['none', __('None')],
            ['random', 'Random'],
            ['kenburns', 'Kenburns'],
            ['kenburnsUp', 'Kenburns Up'],
            ['kenburnsDown', 'Kenburns Down'],
            ['kenburnsLeft', 'Kenburns Left'],
            ['kenburnsRight', 'Kenburns Right'],
            ['kenburnsUpLeft', 'Kenburns Up-Left'],
            ['kenburnsUpRight', 'Kenburns Up-Right'],
            ['kenburnsDownLeft', 'Kenburns Down-Left'],
            ['kenburnsDownRight', 'Kenburns Down-Right'],
        ]
    ],
    'delay' => [
        'dbfield' => 'classic_slide_delay',
        'label' => __('Delay'),
        'type' => 'text',
        'default' => 5000
    ],
    'popular-collection' => [
        'dbfield' => 'classic_popular_collection',
        'label' => __('Popular Collection'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'popular-collection-item' => [
        'dbfield' => 'classic_popular_collection_item',
        'label' => __('Popular Items'),
        'type' => 'text',
        'default' => 6
    ],
    'new-collection' => [
        'dbfield' => 'classic_new_collection',
        'label' => __('New Collection'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'new-collection-item' => [
        'dbfield' => 'classic_new_collection_item',
        'label' => __('New Items'),
        'type' => 'text',
        'default' => 6
    ],
    'top-reader' => [
        'dbfield' => 'classic_top_reader',
        'label' => __('Top Reader'),
        'type' => 'dropdown',
        'default' => 0,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'suggestion' => [
        'dbfield' => 'classic_suggestion',
        'label' => __('Suggestion'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'map' => [
        'dbfield' => 'classic_map',
        'label' => __('Map'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Show')],
            [0, __('Hide')]
        ]
    ],
    'map-link' => [
        'dbfield' => 'classic_map_link',
        'label' => __('Map URL'),
        'type' => 'longtext',
        'default' => $sysconf['template']['classic_map_link'],
        'width' => '100',
        'max' => 1000
    ],
    'map-desc' => [
        'dbfield' => 'classic_map_desc',
        'label' => __('Map Description'),
        'type' => 'ckeditor',
        'default' => $sysconf['template']['classic_map_desc'],
    ],
    'fb-link' => [
        'dbfield' => 'classic_fb_link',
        'label' => __('Facebook URL'),
        'type' => 'longtext',
        'default' => '',
        'width' => '100',
        'max' => 1000
    ],
    'twitter-link' => [
        'dbfield' => 'classic_twitter_link',
        'label' => __('Twitter URL'),
        'type' => 'longtext',
        'default' => '',
        'width' => '100',
        'max' => 1000
    ],
    'youtube-link' => [
        'dbfield' => 'classic_youtube_link',
        'label' => __('Youtube URL'),
        'type' => 'longtext',
        'default' => '',
        'width' => '100',
        'max' => 1000
    ],
    'instagram-link' => [
        'dbfield' => 'classic_instagram_link',
        'label' => __('Instagram URL'),
        'type' => 'longtext',
        'default' => '',
        'width' => '100',
        'max' => 1000
    ],
    'footer_about_us' => [
        'dbfield' => 'classic_footer_about_us',
        'label' => __('Footer About Us'),
        'type' => 'ckeditor',
        'default' => $sysconf['template']['classic_footer_about_us'],
    ],
    // --- Данни на читалището (показват се в началото, футъра, контактите и мета данните) ---
    'prosveta-full-name' => [
        'dbfield' => 'prosveta_full_name',
        'label' => 'Пълно име на читалището',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_full_name'],
        'width' => '100',
        'max' => 200
    ],
    'prosveta-description' => [
        'dbfield' => 'prosveta_description',
        'label' => 'Описание за търсачки и споделяне (до 160 знака)',
        'type' => 'longtext',
        'default' => $sysconf['template']['prosveta_description'],
        'width' => '100',
        'max' => 300
    ],
    'prosveta-keywords' => [
        'dbfield' => 'prosveta_keywords',
        'label' => 'Ключови думи',
        'type' => 'longtext',
        'default' => $sysconf['template']['prosveta_keywords'],
        'width' => '100',
        'max' => 300
    ],
    'prosveta-street' => [
        'dbfield' => 'prosveta_street',
        'label' => 'Улица и номер',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_street'],
        'width' => '60',
        'max' => 100
    ],
    'prosveta-postcode' => [
        'dbfield' => 'prosveta_postcode',
        'label' => 'Пощенски код',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_postcode'],
        'width' => '20',
        'max' => 10
    ],
    'prosveta-address-html' => [
        'dbfield' => 'prosveta_address_html',
        'label' => 'Адрес (както се показва на сайта)',
        'type' => 'ckeditor',
        'default' => $sysconf['template']['prosveta_address_html'],
    ],
    'prosveta-phone' => [
        'dbfield' => 'prosveta_phone',
        'label' => 'Телефон',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_phone'],
        'width' => '40',
        'max' => 50
    ],
    'prosveta-email' => [
        'dbfield' => 'prosveta_email',
        'label' => 'E-mail',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_email'],
        'width' => '60',
        'max' => 100
    ],
    'prosveta-hours' => [
        'dbfield' => 'prosveta_hours',
        'label' => 'Работно време',
        'type' => 'ckeditor',
        'default' => $sysconf['template']['prosveta_hours'],
    ],
    'prosveta-lat' => [
        'dbfield' => 'prosveta_lat',
        'label' => 'Географска ширина (за упътване и търсачки)',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_lat'],
        'width' => '30',
        'max' => 20
    ],
    'prosveta-lon' => [
        'dbfield' => 'prosveta_lon',
        'label' => 'Географска дължина',
        'type' => 'text',
        'default' => $sysconf['template']['prosveta_lon'],
        'width' => '30',
        'max' => 20
    ],
    'visitor_voice' => [
        'dbfield' => 'visitor_log_voice',
        'label' => __('Visitor log voice'),
        'type' => 'dropdown',
        'default' => 1,
        'data' => [
            [1, __('Enable')],
            [0, __('Disable')]
        ]
    ],
];
