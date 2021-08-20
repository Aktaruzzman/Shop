<div class="tp-banner-container" id="heroBanner">
    <div class="tp-banner">
        <ul>
            <?php $sliders = get_rows('pagesliders', ['status' => 'active'], "*", "id", "DESC") ?>
            <?php foreach ($sliders as $index => $slider) : ?>
                <?php $slider = objectToArray($slider) ?>
                <li class="revolution-mch-1 w3-center" data-slotamount="10" data-speed="1000" data-masterspeed="1500" data-title="SLIDER-<?php echo  $index + 1 ?>">
                    <img src="<?php echo UPLOAD_PATH . 'slider/' . $slider['photo'] ?>" alt="darkblurbg" data-bgfit="cover" data-bgposition="center center" data-bgrepeat="no-repeat" class="w3-opacity">
                    <div class="tp-caption sft start w3-text-white revolution-ch1" data-x="center" data-y="75" data-speed="500" data-start="500">
                        <strong><?php echo !empty($slider['title_' . $lang]) ? $slider['title_' . $lang] : '&nbsp;' ?></strong>
                    </div>
                    <div class="tp-caption sft start w3-text-white revolution-ch2" data-x="center" data-y="140" data-speed="1000" data-start="700" data-easing="Power4.easeInOut" data-endeasing="Power4.easeInOut" data-endspeed="500">
                        <?php if (!empty($slider['subtitle_' . $lang])) : ?>
                            <?php echo $slider['subtitle_' . $lang] ?>
                        <?php else : ?>
                            <?php echo '' . '<br/>' . '' ?>
                        <?php endif; ?>
                    </div>
                    <div class="tp-caption sft start w3-text-white revolution-ch3" data-x="center" data-y="240" data-speed="1500" data-start="900" data-easing="Power4.easeInOut" data-endeasing="Power4.easeInOut" data-endspeed="500">
                        <?php if (!empty($slider['message_' . $lang])) : ?>
                            <?php echo $slider['message_' . $lang] ?>
                        <?php else : ?>
                            <?php echo '' . '<br/>' . '' ?>
                        <?php endif; ?>
                    </div>
                    <div class="tp-caption sft start w3-text-white w3-small" data-x="center" data-y="320" data-speed="2000" data-start="1100" data-easing="Power4.easeInOut" data-endeasing="Power4.easeInOut" data-captionhidden="off" data-endspeed="600">
                        <?php $this->load->view('partials/home-button') ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="tp-bannertimer tp-bottom"></div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function() {
        RevolutionSlider.initRSfullWidth();
    });
</script>