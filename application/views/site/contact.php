<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align"><?php echo $page['description'] ?></article>
            </div>
            <div class="w3-row w3-mobile">
                <div class="w3-col w3-twothird">
                    <?php $this->load->view('partials/contact-form') ?>
                </div>
                <div class="w3-col w3-third">
                    <div class="w3-container w3-section-tiny">
                        <h3 class="w3-text-bold-500"><?php echo $store_info['name'] ?></h3>
                        <?php $this->load->view('partials/contact-info') ?>
                        <?php $this->load->view('partials/opening-hours') ?>
                    </div>
                </div>
            </div>
            <div class="w3-container">
                <?php $this->load->view('partials/contact-map') ?>
            </div>
        </div>
    </main>
</div>