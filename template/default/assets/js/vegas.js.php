<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-29 23:33
 * @File name           : vegas.js.php
 */

if (!$sysconf['template']['classic_library_disableslide']):
?>
<script>
  $('.c-header, .vegas-slide').vegas({
        delay: <?= $sysconf['template']['classic_slide_delay']; ?>,
        timer: false,
        transition: '<?= $sysconf['template']['classic_slide_transition']; ?>',
        animation: '<?= $sysconf['template']['classic_slide_animation']; ?>',
        slides: [
<?php
            // Снимките за карусела: всички .jpg файлове в assets/images/prosveta/slides/ (по азбучен ред)
            $slides = glob(__DIR__ . '/../images/prosveta/slides/*.jpg') ?: [];
            sort($slides);
            foreach ($slides as $slide) {
                echo '            { src: "' . CURRENT_TEMPLATE_DIR . v('assets/images/prosveta/slides/' . basename($slide)) . '" },' . "\n";
            }
?>
        ]
    });
</script>
<?php
endif;