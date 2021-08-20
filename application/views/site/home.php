<div class="content-area" style="margin-top: 73px;">
    <div class="w3-margin-top">
        <?php $this->load->view('partials/home-slider') ?>
    </div>
    <div class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container' : '' ?>">
        <div class="w3-white w3-padding-16"><?php $this->load->view('partials/home-step') ?></div>
    </div>
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container' : '' ?>">
        <div class="w3-row-padding w3-white">
            <h2 class="w3-col w3-block w3-center w3-text-upper w3-text-bold-500 w3-hide-large w3-hide-medium"><?php echo $page['subtitle'] ?></h2>
            <h2 class="w3-col w3-block w3-center w3-text-upper w3-text-bold-500 fancy-words w3-hide-small"><?php echo $page['subtitle'] ?></h2>
            <div class="w3-col w3-half w3-round">
                <div class=" w3-display-container">
                    <img src="<?php echo UPLOAD_PATH . 'page/' . $page['photo'] ?>" alt="<?php echo $page['subtitle'] ?>" class="w3-image w3-block" style="height:<?php echo config_item('web_theme_layout') === "wide" ? '365px' : '405px' ?>" />
                </div>
                <div class="w3-center">
                    <?php echo $store_info['name'] ?>
                    <?php if (config_item('store_type') === 'single') : ?><br /><i class="fa fa-map-marker">&nbsp;</i><?php echo $store_info['house'] . ', ' . $store_info['area'] ?><?php endif; ?>
                </div>
            </div>
            <div class="w3-col w3-half ">
                <?php echo $page['description'] ?>
                <?php $this->load->view('partials/home-button') ?>
            </div>
        </div>
    </main>

    <?php if (!empty($stores)) : ?>
        <div class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container' : '' ?>">
            <div class="w3-white w3-padding-16"><?php $this->load->view('partials/home-outlet') ?></div>
        </div>
    <?php endif; ?>

    <div class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container' : '' ?>">
        <div class="w3-white w3-padding-16"><?php $this->load->view('partials/home-promotion') ?></div>
    </div>

</div>

<script>
    $(function() {
        $('.fancy-words').lettering('words').children('span').each(function(i) {
            var rotation = i % 2 == 0 ? -4 : 5;
            $(this).css({
                'letter-spacing': '3px',
                'display': 'inline-block',
                '-webkit-transform': 'rotate(' + rotation + 'deg)'
            }).addClass('w3-text-bold');
        });
    });
</script>