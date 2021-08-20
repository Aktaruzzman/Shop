<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align"><?php echo $page['description'] ?></article>
            </div>
            <div class="w3-block">
                <?php $this->load->view('partials/review-rating') ?>
                <?php $this->load->view('partials/review-msg') ?>
                <?php if (!empty($pagination)) : ?> <div class="w3-section w3-row-padding w3-center"><?php echo $pagination ?></div><?php endif; ?>
            </div>
            <div class="w3-row">
                <div class="w3-col w3-block w3-container"><?php echo $page['description'] ?></div>
                <div class="w3-col w3-half">
                    <figure><img src="<?php echo UPLOAD_PATH . 'page/' . $page['photo'] ?>" alt="<?php echo $page['subtitle'] ?>" class="w3-image w3-block w3-border-white w3-border w3-round" /></figure>
                </div>
                <?php if ($page['photo']) : ?>
                    <div class="w3-col w3-half">
                        <?php $this->load->view('partials/review-form') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>