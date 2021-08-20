<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
            </div>
            <div class="w3-row-padding w3-mobile">
                <div class="w3-col w3-quarter w3-margin-bottom">
                    <?php $this->load->view('customer/sidebar') ?>
                </div>
                <div class="w3-col w3-threequarter w3-margin-bottom">
                    <div class="w3-white w3-border w3-border-light-gray w3-round w3-mobile">
                        <div class="w3-container">
                            <h4><?php echo sprintf(lang('my_option'), lang('point')) ?></h4>
                            <article>-----</article>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>