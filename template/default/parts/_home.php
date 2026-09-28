<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-23T11:27:04+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _home.php
# @Last modified by:   user
# @Last modified time: 2018-01-26T18:43:45+07:00

?>

<section id="section1 container-fluid">
    <header class="c-header">
        <div class="mask"></div>
      <?php
      // ------------------------------------------------------------------------
      // include navbar
      // ------------------------------------------------------------------------
      include '_navbar.php'; ?>
    </header>
  <?php
  // --------------------------------------------------------------------------
  // include search form part
  // --------------------------------------------------------------------------
  include '_search-form.php'; ?>
</section>

<div id="slims-home">
<?php
// Приветствен текст: страница „headerinfo“ от Съдържание (редактира се от администрацията)
if (!class_exists('Content')) include LIB . 'content.inc.php';
$welcome = (new Content())->get($dbs, 'headerinfo');
?>
<section class="mt-8 container">
    <div class="prosveta-welcome p-4 p-md-5">
        <div class="row">
            <div class="col-lg-7">
                <div class="prosveta-kicker mb-2">Народно читалище · основано 1925 г. · с. Храбърско</div>
                <h3 class="mb-3">Добре дошли в библиотеката на НЧ „Просвета 1925“</h3>
                <?= $welcome ? $welcome['Content'] : ''; ?>
                <div class="mt-4">
                    <a href="index.php?p=chitalishte" class="btn btn-primary mr-2 mb-2">За читалището</a>
                    <a href="index.php?p=libinfo" class="btn btn-outline-primary mr-2 mb-2">Как да стана читател</a>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="prosveta-info-card p-4 h-100">
                    <h5 class="mb-3"><i class="far fa-clock mr-2"></i>Работно време</h5>
                    <div class="mb-4"><?= $sysconf['template']['prosveta_hours']; ?></div>
                    <h5 class="mb-3"><i class="fas fa-map-marker-alt mr-2"></i>Къде се намираме</h5>
                    <ul class="list-reset prosveta-contact-list mb-0">
                        <li><i class="fas fa-home"></i><span><?= $sysconf['template']['prosveta_address_html']; ?></span></li>
                        <?php if (!empty($sysconf['template']['prosveta_phone'])): ?>
                        <li><i class="fas fa-phone"></i><span><a href="tel:<?= preg_replace('/[^0-9+]/', '', $sysconf['template']['prosveta_phone']); ?>"><?= $sysconf['template']['prosveta_phone']; ?></a></span></li>
                        <?php endif; ?>
                        <?php if (!empty($sysconf['template']['prosveta_email'])): ?>
                        <li><i class="fas fa-envelope"></i><span><a href="mailto:<?= $sysconf['template']['prosveta_email']; ?>"><?= $sysconf['template']['prosveta_email']; ?></a></span></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="mt-5 container">
    <h4 class="text-secondary text-center text-thin mt-5 mb-4"><?php echo __('Select the topic you are interested in'); ?></h4>
    <ul class="topic d-flex flex-wrap justify-content-center px-0">
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=8&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/8-books.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Literature'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=3&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/3-diploma.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Social Sciences'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=6&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/6-blackboard.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Applied Sciences'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="index.php?callnumber=7&search=search" class="d-flex flex-column">
                <img src="<?php echo assets('images/7-quill.png'); ?>" width="80" class="mb-3 mx-auto"/>
                <?php echo __('Art & Recreation'); ?>
            </a>
        </li>
        <li class="d-flex justify-content-center align-items-center m-2">
            <a href="javascript:void(0)" class="d-flex flex-column" data-toggle="modal" data-target="#exampleModal">
                <img src="<?php echo assets('images/icon/grid_icon.png'); ?>" width="80"
                     class="mb-3 mx-auto"/>
                <?php echo __('see more..'); ?>
            </a>
        </li>
    </ul>
</section>

<?php if ($sysconf['template']['classic_popular_collection']) : ?>
<section class="mt-5 container">
    <h4 class=" mb-4">
        <?php echo __('Popular among our collections'); ?>
        <br>
        <small class="subtitle-section"><?php echo __('Our library\'s line of collection that have been favoured by our users were shown here. Look for them. Borrow them. Hope you also like them');?></small>
    </h4>

    <slims-group-subject url="index.php?p=api/subject/popular"></slims-group-subject>
    <slims-collection url="index.php?p=api/biblio/popular"></slims-collection>

</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_new_collection']) : ?>
<section class="mt-5 container">
    <h4 class=" mb-4">
        <?php echo __('New collections + updated');?>
        <br>
        <small class="subtitle-section"><?php echo __('These are new collections list. Hope you like them. Maybe not all of them are new. But in term of time, we make sure that these are fresh from our processing oven');?></small>
    </h4>

    <slims-group-subject url="index.php?p=api/subject/latest"></slims-group-subject>
    <slims-collection url="index.php?p=api/biblio/latest"></slims-collection>

</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_top_reader']) : ?>
<section class="mt-5 bg-white">
    <div class="container py-5">
        <h4 class="mb-4">
            <?php echo __('Top reader of the year');?>
            <br>
            <small class="subtitle-section"><?php echo __('Our best users, readers, so far. Continue to read if you want your name being mentioned here');?></small>
        </h4>
        <slims-group-member url="index.php?p=api/member/top"></slims-group-member>
    </div>
</section>
<?php endif; ?>

<?php if ($sysconf['template']['classic_map']) : ?>
<section class="my-5 container prosveta-map" id="karta">
    <div class="row align-items-center">
        <div class="col-md-6">
            <iframe class="embed-responsive" title="Карта: <?= htmlspecialchars($sysconf['template']['prosveta_full_name']); ?>"
                    src="<?= $sysconf['template']['classic_map_link']; ?>"
                    height="420" frameborder="0" style="border:0" loading="lazy" allowfullscreen></iframe>
        </div>
        <div class="col-md-6 pt-8 md:pt-0">
            <h4 class="mb-3"><?= $sysconf['template']['prosveta_full_name']; ?></h4>
            <div><?= $sysconf['template']['classic_map_desc']; ?></div>
            <p class="d-flex flex-row flex-wrap pt-2">
                <a target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=<?= $sysconf['template']['prosveta_lat'] . ',' . $sysconf['template']['prosveta_lon']; ?>" class="btn btn-primary mr-2 mb-2"><i class="fas fa-location-arrow mr-2"></i>Упътване</a>
                <?php
                $socials = [
                    'classic_fb_link' => ['btn-outline-primary', 'fab fa-facebook-square', 'Facebook'],
                    'classic_youtube_link' => ['btn-outline-danger', 'fab fa-youtube', 'YouTube'],
                    'classic_instagram_link' => ['btn-outline-dark', 'fab fa-instagram', 'Instagram'],
                    'classic_twitter_link' => ['btn-outline-info', 'fab fa-twitter-square', 'X / Twitter'],
                ];
                foreach ($socials as $key => [$btn, $icon, $label]):
                    if (empty($sysconf['template'][$key])) continue; ?>
                <a target="_blank" rel="noopener" href="<?= $sysconf['template'][$key]; ?>" class="btn <?= $btn; ?> mr-2 mb-2" title="<?= $label; ?>"><i class="<?= $icon; ?> mr-1"></i><?= $label; ?></a>
                <?php endforeach; ?>
            </p>
        </div>
    </div>
</section>
<?php endif; ?>
</div>