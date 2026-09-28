<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-23T11:26:05+07:00
# @Email:  ido.alit@gmail.com
# @Filename: footer.php
# @Last modified by:   user
# @Last modified time: 2018-01-23T11:26:47+07:00
?>


<footer class="prosveta-footer py-4">
    <div class="container">
        <div class="row py-4">
            <div class="col-lg-4">
              <?php
              if(isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && $imagesDisk->isExists($path = 'default/'.$sysconf['logo_image'])){
                echo '<img class="h-10 w-15 mb-3" src="'.SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path.'&width=350">';
              } else {
                echo '<img class="prosveta-footer-logo mb-3" src="' . assets('images/prosveta/emblema-negativ.png') . '" alt="Емблема на читалището" loading="lazy">';
              }
              ?>
                <h4 class="mb-3"><?= $sysconf['template']['prosveta_full_name']; ?></h4>
                <div class="prosveta-footer-about"><?= $sysconf['template']['classic_footer_about_us']; ?></div>
            </div>
            <div class="col-lg-3 col-md-5 pt-8 lg:pt-0">
                <h4 class="mb-3">Страници</h4>
                <ul class="list-reset prosveta-footer-links">
                    <li><a href="index.php?p=chitalishte">За читалището</a></li>
                    <li><a href="index.php?p=libinfo">Библиотеката</a></li>
                    <li><a href="index.php?p=news"><?= __('News'); ?></a></li>
                    <li><a href="index.php?p=kontakti">Контакти</a></li>
                    <li><a href="index.php?p=help">Как да търся в каталога</a></li>
                    <li><a href="index.php?p=member"><?= __('Member Area'); ?></a></li>
                </ul>
            </div>
            <div class="col-lg-5 col-md-7 pt-8 lg:pt-0">
                <h4 class="mb-3">Контакти</h4>
                <ul class="list-reset prosveta-contact-list">
                    <li><i class="fas fa-map-marker-alt"></i><span><?= $sysconf['template']['prosveta_address_html']; ?></span></li>
                    <?php if (!empty($sysconf['template']['prosveta_phone'])): ?>
                    <li><i class="fas fa-phone"></i><span><a href="tel:<?= preg_replace('/[^0-9+]/', '', $sysconf['template']['prosveta_phone']); ?>"><?= $sysconf['template']['prosveta_phone']; ?></a></span></li>
                    <?php endif; ?>
                    <?php if (!empty($sysconf['template']['prosveta_email'])): ?>
                    <li><i class="fas fa-envelope"></i><span><a href="mailto:<?= $sysconf['template']['prosveta_email']; ?>"><?= $sysconf['template']['prosveta_email']; ?></a></span></li>
                    <?php endif; ?>
                    <?php if (!empty($sysconf['template']['classic_fb_link'])): ?>
                    <li><i class="fab fa-facebook-square"></i><span><a target="_blank" rel="noopener" href="<?= $sysconf['template']['classic_fb_link']; ?>">Facebook</a></span></li>
                    <?php endif; ?>
                    <li><i class="far fa-clock"></i><span><?= $sysconf['template']['prosveta_hours']; ?></span></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="flex flex-wrap font-thin text-sm">
            <p class="flex-1">&copy; <?php echo date('Y'); ?> &mdash; <?= $sysconf['template']['prosveta_full_name']; ?></p>
            <div class="flex-1 text-right prosveta-credit">Снимки: <a target="_blank" rel="noopener" href="https://www.bozhurishte.bg/">Община Божурище</a> · Електронният каталог работи със <a target="_blank" rel="noopener" href="https://slims.web.id/">SLiMS</a></div>
        </div>
    </div>
</footer>

<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <div id="show-pchat2" style="position: fixed; bottom: 16px; right: 16px" class="shadow rounded">
        <button title="Chat" class="btn btn-primary"><i class="fas fa-comments mr-2"></i><?= __('Chat'); ?></button>
    </div>
<?php endif; ?>

<?php
// Chat Engine
include LIB . "contents/chat.php"; ?>

<!-- // Load modal -->
<?php include "_modal_topic.php"; ?>
<?php include "_modal_advanced.php"; ?>
<?php include "_modal_social_media.php"; ?>

<!-- // Load highlight -->
<script src="<?= JWB; ?>highlight.js"></script>
<?php if(isset($engine) && $searchableInJsArray = $this->generateKeywords($engine->searchable_fields)) : ?>
<script>
  $('.card-body > *').highlight(<?= $searchableInJsArray ?>);
</script>
<?php endif; ?>

<!-- // load our vue app.js -->
<script src="<?php echo assets(v('js/app.js')); ?>"></script>
<script src="<?php echo assets(v('js/app_jquery.js')); ?>"></script>
<?php include __DIR__ . "/../assets/js/vegas.js.php"; ?>
<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <script>
        $('#show-pchat').click(() => {
            $('.s-chat').hide()
            $('#show-pchat2').show()
        })
        $('#show-pchat2').click(() => {
            $('.s-chat').show(300, () => {
                $('#show-pchat2').hide()
            })
        })
    </script>
<?php endif; ?>
</body>
</html>
