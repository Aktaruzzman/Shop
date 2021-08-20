<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
            </div>
            <div class="w3-row-padding w3-mobile">
                <div class="w3-col w3-quarter">
                    <?php $this->load->view('customer/sidebar') ?>
                </div>
                <div class="w3-col w3-threequarter">
                    <div class="w3-col w3-block w3-text-bold-500" style="margin-bottom: 8px;">
                        <div class="w3-white w3-padding-16 w3-container w3-border w3-border-light-gray w3-round w3-mobile">
                            <?php echo $page['description'] ?>
                        </div>
                    </div>
                    <div class="w3-col w3-block w3-row">
                        <div class="w3-col w3-third" style="padding:1px" onclick="window.location.href='<?php echo site_url('customer/orderlist') ?>'">
                            <div class="w3-white w3-center w3-border w3-border-light-gray w3-round w3-padding-32 w3-cursor-pointer w3-mobile">
                                <i class="fa fa-list-alt fa-5x w3-text-theme"></i>
                                <h3 class="w3-text-theme w3-text-bold"><?php echo sprintf(lang('my_option'), lang('order')) ?></h3>
                            </div>
                        </div>
                        <div class="w3-col w3-third" style="padding:1px" onclick="window.location.href='<?php echo site_url('customer/profile') ?>'">
                            <div class="w3-white w3-center w3-border w3-border-light-gray w3-round w3-padding-32 w3-cursor-pointer w3-mobile">
                                <i class="fa fa-user fa-5x w3-text-theme"></i>
                                <h3 class="w3-text-theme w3-text-bold"><?php echo sprintf(lang('my_option'), lang('profile')) ?></h3>
                            </div>
                        </div>
                        <div class="w3-col w3-third" style="padding:1px" onclick="window.location.href='<?php echo site_url('customer/changepassword') ?>'">
                            <div class="w3-white w3-center w3-border w3-border-light-gray w3-round w3-padding-32 w3-cursor-pointer w3-mobile">
                                <i class="fa fa-exchange fa-5x w3-text-theme"></i>
                                <h3 class="w3-text-theme w3-text-bold"><?php echo lang('password') . ' ' . lang('Change') ?></h3>
                            </div>
                        </div>
                        <div class="w3-col w3-third" style="padding:1px" onclick="window.location.href='<?php echo site_url('customer/point') ?>'">
                            <div class="w3-white w3-center w3-border w3-border-light-gray w3-round w3-padding-32 w3-cursor-pointer w3-mobile">
                                <i class="fa fa-gift fa-5x w3-text-theme"></i>
                                <h3 class="w3-text-theme w3-text-bold"><?php echo sprintf(lang('my_option'), lang('point')) ?></h3>
                            </div>
                        </div>
                        <div class="w3-col w3-third" style="padding:1px" onclick="window.location.href='<?php echo site_url('customer/addressbook') ?>'">
                            <div class="w3-white w3-center w3-border w3-border-light-gray w3-round w3-padding-32 w3-cursor-pointer w3-mobile">
                                <i class="fa fa-address-book fa-5x w3-text-theme"></i>
                                <h3 class="w3-text-theme w3-text-bold"><?php echo lang('delivery') . ' ' . lang('address') ?></h3>
                            </div>
                        </div>
                        <div class="w3-col w3-third" style="padding:1px" onclick="window.location.href='<?php echo site_url('customer/logout') ?>'">
                            <div class="w3-white w3-center w3-border w3-border-light-gray w3-round w3-padding-32 w3-cursor-pointer w3-mobile">
                                <i class="fa fa-sign-out fa-5x w3-text-theme"></i>
                                <h3 class="w3-text-theme w3-text-bold"><?php echo lang('logout') ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>