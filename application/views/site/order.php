<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-order w3-container w3-margin-bottom">
            <div class="w3-padding-8 w3-white w3-border w3-border-theme w3-round <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>">
                <div class="w3-row-padding" id="heroBanner">
                    <h2 class="w3-text-upper w3-col w3-block"><?php echo $store_info['name'] ?></h2>
                    <div class="w3-col l6 m9 w3-row-padding-4">
                        <div class="w3-col w3-third w3-section-tiny">
                            <a href="javascript:void(0)" class="w3-button w3-hover-theme w3-border w3-border-theme w3-round w3-block" onclick="openPopup('daysOpeningTimePopup')"><i class="fa fa-clock-o" aria-hidden="true">&nbsp;</i><?php echo lang('opening_hours') ?></a>
                        </div>
                        <div class="w3-col w3-third w3-section-tiny">
                            <a href="javascript:void(0)" class="w3-button w3-hover-theme w3-border w3-border-theme w3-round w3-block" onclick="openPopup('daysDiscountOfferPopup')"><i class="fa fa-bookmark-o" aria-hidden="true">&nbsp;</i><?php echo sprintf(lang('latest_option'), lang('offer')) ?></a>
                        </div>
                        <?php if (config_item('store_type') === 'multiple') : ?>
                            <div class="w3-col w3-third w3-section-tiny">
                                <a href="javascript:void(0)" class="w3-button w3-hover-theme w3-border w3-border-theme w3-round w3-block change-outlet"><i class="fa fa-map-marker" aria-hidden="true">&nbsp;</i><?php echo sprintf(lang('change_option'), lang('outlet')) ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <article class="w3-col w3-block w3-section-small">
                        <?php echo $page['description'] ?>
                        <?php echo sprintf(lang('call_us_at'), '<a class="w3-border-bottom" href="tel:' . $store_info['phone'] . '">' . $store_info['phone'] . '</a>') ?>
                        <?php if (config_item('store_type') === 'multiple') : ?>
                            <?php echo sprintf(lang('branch_change_text'), "<a href='javascript:void(0)' class='w3-border-bottom change-outlet w3-text-bold'>" . $store_info['house'] . ', ' . $store_info['area'] . "</a>") ?>
                            <a class="w3-border-bottom change-outlet w3-text-bold" href="javascript:void(0)"><?php echo sprintf(lang('change_option'), lang('outlet')) ?></a>
                        <?php endif; ?>
                    </article>
                </div>
            </div>
        </div>

        <div class="cart-hooker w3-hide-large w3-hide-medium w3-cursor-pointer" onclick="scrollToPosition('#menuPageRightSidebar')" id="cartHooker" style="z-index: 1!important;">
            <div class="w3-row">
                <div class="w3-white w3-border w3-border-theme" style="width:160px; height:40px; border-radius:15px 0px 0px 15px;border-right:0!important">
                    <div style="width:30px;float:right;padding:4px;"><i class="fa fa-shopping-cart fa-2x fa-flip-horizontal w3-text-theme" aria-hidden="true"></i></div>
                    <div style="width:100px;float:right;padding:8px 0px;"><span class="cart-hooker-text"></span></div>
                    <div style="width:27px;float:left;padding:4px 0px;"><span class="spinning" style="display: none"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle"></span></div>
                </div>
            </div>
        </div>
        <div class="w3-row-padding w3-mobile">
            <div class="w3-col m2 w3-hide-small w3-margin-bottom" id="menuPageLeftSidebar">
                <?php $this->load->view('partials/order-category') ?>
            </div>
            <div class="w3-col m6 w3-margin-bottom" id="menuList">
                <?php if ($page_style === "grid") : ?>
                    <?php $this->load->view('partials/order-grid') ?>
                <?php else : ?>
                    <?php $this->load->view('partials/order-list') ?>
                <?php endif; ?>
            </div>
            <div class="w3-col m4" id="menuPageRightSidebar">
                <?php $this->load->view('partials/order-cart') ?>
            </div>
        </div>
        <?php echo $this->load->view('popups/store-closed') ?>
        <?php echo $this->load->view('popups/store-temp-closed') ?>
        <?php echo $this->load->view('popups/preorder-permission') ?>
        <?php echo $this->load->view('popups/order-service') ?>
        <?php echo $this->load->view('popups/order-delivery-area') ?>
        <?php echo $this->load->view('popups/order-delivery-home') ?>
        <?php echo $this->load->view('popups/order-receive-time') ?>
        <?php echo $this->load->view('popups/order-itembox') ?>
        <?php echo $this->load->view('popups/order-custsets') ?>
        <?php echo $this->load->view('popups/order-freeitem') ?>
        <?php echo $this->load->view('popups/order-outlet') ?>
        <script>
            $(function() {
                Render.init();
            })
        </script>
    </main>
</div>