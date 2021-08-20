<div class="content-area top-mutual" id="contentArea">
    <?php echo $this->load->view('partials/banner') ?>
    <main class="w3-content w3-mobile" style="<?php echo config_item('web_theme_content_bg') ?>">
        <div class="w3-wide-container">
            <div class="w3-row-padding">
                <p class="w3-center"><?php echo $page['description'] ?> <a class="w3-text-blue w3-border-bottom w3-border-blue" href="<?php echo site_url() ?>"><?php echo lang('back_homepage') ?></a></p>
                <div class="w3-section"><img src="<?php echo UPLOAD_PATH . 'page/' . $page['photo'] ?>" alt="<?php echo $page['subtitle'] ?>" class="w3-image w3-block w3-border-white w3-border w3-round" /></div>
            </div>
        </div>
    </main>
</div>