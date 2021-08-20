<footer class="w3-theme-d2 <?php echo $slug !== "order" ? "w3-border-top" : "" ?> w3-border-theme-header">
    <?php if ($slug !== "order") : ?>
        <div class="w3-center" style="margin-top:-10px" onclick="scrollToPosition('#heroBanner')"><i class="fa fa-angle-double-up fa-2x w3-theme-d2" style="padding:0px 16px; border-radius: 50% / 100% 100% 0 0;"></i></div>
        <div class="w3-footer">
            <div class="w3-content w3-container w3-wide-container">
                <h5 class=""><?php echo lang('subscribe_to_know_latest_offer') ?>&nbsp;<span id="subscriptionSuccess"></span></h5>
                <form id="subscribeForm" class="w3-round-large w3-border w3-border-theme-header" method="post" action="<?php echo site_url('site/subscribe') ?>">
                    <div class="w3-row-padding-4 w3-padding-tiny">
                        <div class="w3-col l5 m5 s12 w3-section-tiny">
                            <input class="w3-input w3-border w3-border-theme-header w3-round" name="phone" type="tel" placeholder="<?php echo lang('phone') ?>">
                        </div>
                        <div class="w3-col l5 m5 s12 w3-section-tiny">
                            <input class="w3-input w3-border w3-border-theme-header w3-round" name="email" type="email" placeholder="<?php echo lang('email') ?>">
                        </div>
                        <div class="w3-col l2 m2 s12 w3-section-tiny">
                            <?php if ($isLoggedin) : ?><input type="hidden" name="cust_id" value="<?php echo $this->session->userdata('customerId'); ?>"><?php endif ?>
                            <button type="submit" class="w3-button w3-hover-theme w3-border w3-border-theme-header w3-round w3-block "><b><img src="<?php echo ASSET_PATH ?>img/ajax-loader.gif" class="w3-image w3-circle ajax-loader" id="subscribe-ajax-loader" style="display: none;">&nbsp;<?php echo lang('subscribe') ?></b></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="footer-rest w3-footer">
            <div class="w3-content w3-container w3-wide-container w3-center">
                <div class="w3-section w3-row">
                    <div class="w3-col m4 social-icons w3-left-align">
                        <?php $this->load->view('partials/social-media') ?>
                    </div>
                    <div class="w3-col m8 shift-align-to-left w3-right-align w3-small">

                        <?php if (is_active_page('contact')) : ?><a href="<?php echo site_url('contact') ?>" class="w3-section-tiny w3-button w3-padding-small w3-border w3-border-theme-header w3-round w3-hover-theme"><?php echo lang('contact') ?></a><?php endif; ?>
                        <?php if (is_active_page('termsofuse')) : ?><a href="<?php echo site_url('terms-of-use') ?>" class="w3-section-tiny w3-button w3-padding-small w3-border w3-border-theme-header w3-round w3-hover-theme"><?php echo lang('terms_of_use') ?></a><?php endif; ?>
                        <?php if (is_active_page('privacypolicy')) : ?><a href="<?php echo site_url('privacy-policy') ?>" class="w3-section-tiny w3-button w3-padding-small w3-border w3-border-theme-header w3-round w3-hover-theme"><?php echo lang('privacy_policy') ?></a><?php endif; ?>
                        <?php if (is_active_page('cookiepolicy')) : ?><a href="<?php echo site_url('cookie-policy') ?>" class="w3-section-tiny w3-button w3-padding-small w3-border w3-border-theme-header w3-round w3-hover-theme"><?php echo lang('cookie_policy') ?></a><?php endif; ?>
                    </div>
                </div>
                <div class="w3-row w3-section">
                    <div class="w3-col m6 w3-left">
                        <div class="payment-buttons w3-left-align w3-row">
                            <img src="<?php echo ASSET_PATH ?>img/cash.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny">
                            <img src="<?php echo ASSET_PATH ?>img/visa.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny" style='height: 33px;'>
                            <img src="<?php echo ASSET_PATH ?>img/master.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny" style='height: 33px;'>
                            <img src="<?php echo ASSET_PATH ?>img/american.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny" style='height: 33px;'>
                            <!--<img src="<?php echo ASSET_PATH ?>img/bikash.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny" style='background: #fff; padding:0px 3px;'>-->
                            <!--<img src="<?php echo ASSET_PATH ?>img/rocket.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny" style='background: #fff;'>-->
                        </div>
                    </div>
                    <div class="w3-col m6 w3-right">
                        <div class="feedback-icons shift-align-to-left w3-right-align">
                            <?php if (!empty(config_item('app_android'))) : ?>
                                <a href="<?php echo config_item('app_android') ?>" download><img style="height: 40px;" src="<?php echo ASSET_PATH ?>img/google-play-icon_2x.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny"></a>
                            <?php endif ?>
                            <?php if (!empty(config_item('app_apple'))) : ?>
                                <a href="<?php echo config_item('app_apple') ?>" download><img style="height: 40px;" src="<?php echo ASSET_PATH ?>img/app-store-icon_2x.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny"></a>
                            <?php endif ?>
                            <?php if (!empty(config_item('app_windows'))) : ?>
                                <a href="<?php echo config_item('app_windows') ?>" download><img style="height: 40px;" src="<?php echo ASSET_PATH ?>img/windows-store-icon_2x.png" class="w3-image w3-round w3-border w3-border-theme-header w3-section-tiny"></a>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="w3-row w3-theme-d3 w3-border-top w3-border-theme-header">
        <div class="w3-content w3-container w3-wide-container w3-tiny w3-row">
            <div class="w3-col s5">
                <div class="w3-padding-8 w3-left"><span class="w3-hide-small">Copyright&nbsp;</span>&copy;&nbsp;<?php echo date('Y') ?><a href="<?php echo site_url() ?>">&nbsp;<?php echo get_domain() ?></a></div>
            </div>
            <div class="w3-col s2 w3-center w3-cell">
                <a class="w3-cell-middle" href="javascript:void(0)" onclick="scrollToPosition('#heroBanner')"><?php if ($slug == "order") : ?><i class="fa fa-angle-double-up fa-2x"></i><?php else : ?>&nbsp;<?php endif; ?></a>
            </div>
            <div class="w3-col s5">
                <div class="w3-padding-8 w3-right w3-right-align"><span class="w3-hide-small">Powered by</span> <a href="https://www.bazaar-soft.com" target="_blank">bazaar-soft.com</a></div>
            </div>
        </div>
    </div>
    <?php echo $this->load->view('popups/opening-time') ?>
    <?php echo $this->load->view('popups/discount-offer') ?>
</footer>

<script type="text/javascript">
    $('document').ready(function() {
        $('#subscribeForm').validate({
            submitHandler: function() {
                var myform = document.getElementById('subscribeForm');
                var formdata = new FormData(myform);
                $.ajax({
                    url: $('#subscribeForm').attr('action'),
                    data: formdata,
                    cache: false,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function() {
                        $('#subscribe-ajax-loader').show();
                    },
                    success: function(response) {
                        $('.ajax-loader').hide();
                        $('#subscriptionSuccess').text(" (" + response.message + " )");
                        $('#subscribeForm').clearForm();
                    }
                });
            },
            errorClass: "has-error",
            highlight: function(e) {
                $(e).closest('').addClass('has-error');
            },
            unhighlight: function(e) {
                $(e).closest('').removeClass('has-error');
            },
            rules: {
                phone: {
                    required: true,
                    phone: true,
                },
                email: {
                    email: true
                }
            },
            messages: {
                phone: {
                    required: "<?php echo sprintf(lang('field_required_msg'), lang('phone')) ?>",
                    phone: "<?php echo sprintf(lang('field_required_msg'), lang('phone')) ?>",
                },
                email: {
                    email: "<?php echo sprintf(lang('field_required_msg'), lang('email_address')) ?>",
                }
            },
            errorPlacement: function(error, element) {
                errorPlacement(error, element);
            }
        });
    });
</script>