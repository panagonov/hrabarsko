<!--
# ===============================
# Classic SLiMS Template
# ===============================
# @Author: Waris Agung Widodo
# @Email:  ido.alit@gmail.com
# @Date:   2018-01-23T11:25:57+07:00
# @Last modified by:   Waris Agung Widodo
# @Last modified time: 2019-01-03T11:25:57+07:00
-->
<?php
// clean request uri from xss
$request_uri = urlencode(strip_tags(urldecode($_SERVER['REQUEST_URI'])));

// Мета данни за споделяне: Facebook, Viber, WhatsApp и др. искат пълни (абсолютни) адреси
$meta = fn($str) => htmlspecialchars(trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string)$str, ENT_QUOTES, 'UTF-8')))), ENT_QUOTES, 'UTF-8');
$site_base = (($_SERVER['HTTPS'] ?? '') === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME']) . SWB;
$page_url = rtrim($site_base, '/') . strip_tags(urldecode($_SERVER['REQUEST_URI']));
$is_detail = isset($_GET['p']) && $_GET['p'] == 'show_detail';

$full_title = $page_title;
if (mb_strpos($full_title, $sysconf['library_name']) === false) $full_title .= ' | ' . $sysconf['library_name'];

$page_description = $sysconf['template']['prosveta_description'];
$share_image = $site_base . $sysconf['template']['dir'] . '/default/assets/images/prosveta/og-image.jpg';
$share_image_size = [1200, 630];
if ($is_detail) {
    $notes_text = $meta($notes ?? '');
    if ($notes_text !== '') $page_description = mb_substr($notes_text, 0, 155) . (mb_strlen($notes_text) > 155 ? '…' : '');
    // корицата на книгата, ако има качена
    if (!empty($image_src) && strpos($image_src, 'images/default/') !== 0) {
        $share_image = $site_base . str_replace(['&amp;', 'width=200'], ['&', 'width=600'], $image_src);
        $share_image_size = null;
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="utf-8">
    <title><?= $meta($full_title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Pragma" content="no-cache"/>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0"/>
    <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT"/>
    <?php echo $metadata;?>
    <meta name="description" content="<?= $meta($page_description); ?>">
    <?php if ($is_detail): ?>
        <meta name="keywords" content="<?= $meta($subject ?? ''); ?>">
    <?php else: ?>
        <meta name="keywords" content="<?= $meta($sysconf['template']['prosveta_keywords']); ?>">
    <?php endif; ?>
    <meta name="generator" content="<?php echo SENAYAN_VERSION ?>">
    <meta name="theme-color" content="#7d3632">
    <link rel="canonical" href="<?= $meta($page_url); ?>">

    <meta property="og:locale" content="bg_BG"/>
    <meta property="og:type" content="<?= $is_detail ? 'book' : 'website'; ?>"/>
    <meta property="og:site_name" content="<?= $meta($sysconf['template']['prosveta_full_name']); ?>"/>
    <meta property="og:title" content="<?= $meta($full_title); ?>"/>
    <meta property="og:description" content="<?= $meta($page_description); ?>"/>
    <meta property="og:url" content="<?= $meta($page_url); ?>"/>
    <meta property="og:image" content="<?= $meta($share_image); ?>"/>
    <?php if ($share_image_size): ?>
    <meta property="og:image:type" content="image/jpeg"/>
    <meta property="og:image:width" content="<?= $share_image_size[0]; ?>"/>
    <meta property="og:image:height" content="<?= $share_image_size[1]; ?>"/>
    <meta property="og:image:alt" content="<?= $meta($sysconf['template']['prosveta_full_name']); ?>"/>
    <?php endif; ?>

    <meta name="twitter:card" content="<?= $share_image_size ? 'summary_large_image' : 'summary'; ?>">
    <meta name="twitter:title" content="<?= $meta($full_title); ?>"/>
    <meta name="twitter:description" content="<?= $meta($page_description); ?>"/>
    <meta name="twitter:image" content="<?= $meta($share_image); ?>"/>

    <?php
    $icon = SWB . 'webicon.ico';
    if (isset($sysconf['webicon']) && !empty($sysconf['webicon']) && $imagesDisk->isExists($path = 'default/' . $sysconf['webicon'])):
        $icon = SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path . '&width=130';
    ?>
    <link rel="shortcut icon" href="<?= $icon ?>" type="image/x-icon"/>
    <?php else: ?>
    <link rel="icon" href="<?= SWB ?>favicon.ico" sizes="32x32">
    <link rel="icon" href="<?= SWB ?>favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="<?= SWB ?>apple-touch-icon.png">
    <link rel="manifest" href="<?= SWB ?>site.webmanifest">
    <?php endif; ?>

    <?php if (!isset($_GET['p']) && !isset($_GET['search'])):
        // Структурирани данни за търсачките (Google показва адрес, телефон и работно време)
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'Library',
            'name' => $sysconf['template']['prosveta_full_name'],
            'alternateName' => 'НЧ „Просвета 1925“',
            'url' => $site_base,
            'logo' => $site_base . $sysconf['template']['dir'] . '/default/assets/images/prosveta/emblema-pozitiv-1000.png',
            'image' => $share_image,
            'foundingDate' => '1925',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $sysconf['template']['prosveta_street'],
                'addressLocality' => 'с. Храбърско',
                'postalCode' => $sysconf['template']['prosveta_postcode'],
                'addressRegion' => 'Софийска област',
                'addressCountry' => 'BG',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float)$sysconf['template']['prosveta_lat'],
                'longitude' => (float)$sysconf['template']['prosveta_lon'],
            ],
        ];
        if (!empty($sysconf['template']['prosveta_phone'])) $ld['telephone'] = preg_replace('/^0/', '+359 ', $sysconf['template']['prosveta_phone']);
        if (!empty($sysconf['template']['prosveta_email'])) $ld['email'] = $sysconf['template']['prosveta_email'];
        if (!empty($sysconf['template']['classic_fb_link'])) $ld['sameAs'] = [$sysconf['template']['classic_fb_link']];
    ?>
    <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?></script>
    <?php endif; ?>

    <!-- // load bootstrap style -->
    <link rel="stylesheet" href="<?php echo assets('css/bootstrap.min.css'); ?>">
    <!-- // font awesome -->
    <link rel="stylesheet" href="<?php echo assets('plugin/font-awesome/css/fontawesome-all.min.css'); ?>">
    <!-- Tailwind CSS -->
    <link rel="stylesheet" href="<?php echo assets('css/tailwind.min.css'); ?>">
    <!-- Vegas CSS -->
    <link rel="stylesheet" href="<?php echo assets('plugin/vegas/vegas.min.css'); ?>">
    <link href="<?php echo JWB; ?>toastr/toastr.min.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css"/>
    <!-- CKEditor5 CSS -->
    <link rel="stylesheet" href="<?= JWB; ?>ckeditor5/ckeditor5.css">
    <!-- SLiMS CSS -->
    <link rel="stylesheet" href="<?= JWB; ?>colorbox/colorbox.css">
    <link rel="stylesheet" href="<?= JWB; ?>ion.rangeSlider/css/ion.rangeSlider.min.css">
    <!-- // Flag css -->
    <link rel="stylesheet" href="<?php echo assets('css/flag-icon.min.css'); ?>">
    <!-- // my custom style -->
    <link rel="stylesheet" href="<?php echo assets('css/style.css?v=' . date('Ymd-his')); ?>">


    <!-- // load vue js -->
    <script src="<?php echo assets('js/vue.min.js'); ?>"></script>
    <!-- // load jquery library -->
    <script src="<?php echo assets('js/jquery.min.js'); ?>"></script>
    <script src="<?php echo assets('js/masonry.pkgd.min.js'); ?>"></script>
    <!-- // load bootstrap javascript -->
    <script src="<?php echo assets('js/bootstrap.bundle.min.js'); ?>"></script>
    <!-- // load vegas javascript -->
    <script src="<?php echo assets('plugin/vegas/vegas.min.js'); ?>"></script>
    <script src="<?php echo JWB; ?>toastr/toastr.min.js"></script>
    <!-- // load SLiMS javascript -->
    <script src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
    <script src="<?php echo JWB . v('gui.js'); ?>"></script>
    <script src="<?php echo JWB; ?>fancywebsocket.js"></script>
    <script src="<?php echo JWB; ?>ion.rangeSlider/js/ion.rangeSlider.min.js"></script>
    <?php
    if (isset($js)):
        echo $js;
    endif;
    ?>

</head>
<body class="bg-grey-lightest">
