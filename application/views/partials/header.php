<header class="header w3-top w3-theme-d1 w3-border-bottom w3-border-light-gray w3-card" id="header">
    <nav class="w3-content w3-row">
        <div class="w3-bar w3-container w3-wide-container">
            <div class="w3-left w3-col l4 m10 s10">
                <a href="<?php echo site_url(); ?>"><img src="<?php echo UPLOAD_PATH ?><?php echo config_item('shop_logo') ?>" alt="<?php echo config_item('shop_logo') ?>" class="w3-image" style="height: 75px" /></a>
            </div>
            <div class="w3-col l8 m8 w3-hide-small w3-hide-medium">
                <div class="w3-right">
                    <?php if (is_active_page('home')) : ?><a href="<?php echo site_url($default === 'home' ? '/' : 'home'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center <?php 'home' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-home" aria-hidden="true"></i><br /><?php echo lang('home') ?></a><?php endif; ?>
                    <?php if (is_active_page('order')) : ?><a href="<?php echo site_url($default === 'order' ? '/' : order_slug()); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center <?php 'order' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-shopping-cart" aria-hidden="true"></i><br /><?php echo lang(order_slug()) ?></a><?php endif; ?>
                    <?php if (is_active_page('booking')) : ?><a href="<?php echo site_url($default === 'booking' ? '/' : 'booking'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center <?php 'booking' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-bookmark-o" aria-hidden="true"></i><br /><?php echo lang('booking') ?></a><?php endif; ?>
                    <?php if (is_active_page('review')) : ?><a href="<?php echo site_url($default === 'review' ? '/' : 'review'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center <?php 'review' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-comments-o" aria-hidden="true"></i><br /><?php echo lang('review') ?></a><?php endif; ?>
                    <?php if (is_active_page('contact')) : ?><a href="<?php echo site_url($default === 'contact' ? '/' : 'contact'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center  <?php 'contact' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-envelope-o" aria-hidden="true"></i><br /><?php echo lang('contact') ?></a><?php endif; ?>
                    <?php if (is_active_page('gallery')) : ?><a href="<?php echo site_url('gallery'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center <?php 'gallery' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-image" aria-hidden="true"></i><br /><?php echo lang('gallery') ?></a><?php endif; ?>
                    <?php if ($isLoggedin) : ?>
                        <div class="w3-dropdown-hover w3-transparent">
                            <a href="javascript:void(0)" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center w3-text-capitalize <?php 'customer' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-user-o" aria-hidden="true"></i><br /><?php echo substr(get_name('customers', array('id' => $this->session->userdata('customerId')), 'en'), 0, 2) ?>&nbsp;<i class="fa fa-angle-down"></i></a>
                            <div class="w3-dropdown-content w3-bar-block" style="margin-top:75px">
                                <ul class="w3-ul w3-white">
                                    <li class="w3-border-light-gray w3-hover-theme"><a class="w3-block" href="<?php echo site_url('customer') ?>"><?php echo lang('dashboard')  ?></a></li>
                                    <li class="w3-border-light-gray w3-hover-theme"><a class="w3-block" href="<?php echo site_url('customer/orderlist') ?>"><?php echo sprintf(lang('my_option'), lang('order'))  ?></a></li>
                                    <li class="w3-border-light-gray w3-hover-theme"><a class="w3-block" href="<?php echo site_url('customer/profile') ?>"><?php echo sprintf(lang('my_option'), lang('profile')) ?></a></li>
                                    <li class="w3-border-0 w3-hover-theme"><a class="w3-block" href="<?php echo site_url(array('customer', 'logout')) ?>"><?php echo lang('logout') ?></a></li>
                                </ul>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php if (is_active_page('signup')) : ?><a href="<?php echo site_url('signup'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center<?php 'signup' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-address-book" aria-hidden="true"></i><br /><?php echo lang('signup') ?></a><?php endif; ?>
                        <?php if (is_active_page('login')) : ?><a href="<?php echo site_url('login'); ?>" class="w3-bar-item w3-hover-white w3-border-right w3-border-theme-header w3-bar-item-custom w3-center<?php 'login' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-sign-in"></i><br /><?php echo lang('login') ?></a><?php endif; ?>
                    <?php endif; ?>
                    <div class="w3-dropdown-hover w3-transparent">
                        <a href="javascript:void(0)" class="w3-bar-item w3-hover-white w3-border-theme-header w3-bar-item-custom w3-text-capitalize"><i class="fa fa-language" aria-hidden="true"></i><br /><?php echo lang($lang) ?>&nbsp;<i class="fa fa-angle-down"></i></a>
                        <div class="w3-dropdown-content w3-bar-block" style="margin-top:75px">
                            <ul class="w3-ul w3-white">
                                <li class="w3-border-light-gray w3-hover-theme"><a href="<?php echo site_url('english'); ?>" class="w3-block"><?php echo lang('english') ?></a></li>
                                <li class="w3-border-light-gray w3-hover-theme"><a href="<?php echo site_url('bengali'); ?>" class="w3-block"><?php echo lang('bangla') ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w3-col m2 s2 w3-hide-large">
                <div class="w3-padding-samll w3-padding-16 w3-right"><a href="javascript:void(0);" onclick="dropdown('mobileResponsiveMenu')"><i class="fa fa-bars fa fa-3x" aria-hidden="true"></i></a></div>
            </div>
        </div>
    </nav>
    <nav class="w3-content w3-row w3-hide-large w3-theme-d1" style="margin-top: -2.2px; display: none;" id="mobileResponsiveMenu">
        <div class="w3-bar">
            <?php if (is_active_page('home')) : ?><a href="<?php echo site_url($default === 'home' ? '/' : 'home'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-top w3-border-theme <?php 'home' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-home" aria-hidden="true">&nbsp;</i><?php echo lang('home') ?></a><?php endif; ?>
            <?php if (is_active_page('order')) : ?><a href="<?php echo site_url($default === 'order' ? '/' : order_slug()); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'order' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-shopping-cart" aria-hidden="true">&nbsp;</i><?php echo lang(order_slug()) ?></a><?php endif; ?>
            <?php if (is_active_page('booking')) : ?><a href="<?php echo site_url($default === 'booking' ? '/' : 'booking'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'booking' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-bookmark-o" aria-hidden="true">&nbsp;</i><?php echo lang('booking') ?></a><?php endif; ?>
            <?php if (is_active_page('review')) : ?><a href="<?php echo site_url($default === 'review' ? '/' : 'review'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'review' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-comments-o" aria-hidden="true">&nbsp;</i><?php echo lang('review') ?></a><?php endif; ?>
            <?php if (is_active_page('contact')) : ?><a href="<?php echo site_url($default === 'contact' ? '/' : 'contact'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'contact' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-envelope-o" aria-hidden="true">&nbsp;</i><?php echo lang('contact') ?></a><?php endif; ?>
            <?php if (is_active_page('gallery')) : ?><a href="<?php echo site_url('gallery'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'gallery' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-image">&nbsp;</i><?php echo lang('gallery') ?></a><?php endif; ?>
            <?php if ($isLoggedin) : ?>
                <div class="w3-dropdown-hover w3-transparent w3-block">
                    <a href="javascript:void(0)" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize  <?php 'customer' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-user-o" aria-hidden="true">&nbsp;</i><?php echo substr(get_name('customers', array('id' => $this->session->userdata('customerId')), 'en'), 0, 2) ?>&nbsp;<i class="fa fa-caret-down"></i></a>
                    <div class="w3-dropdown-content w3-white w3-animate-left">
                        <a href="<?php echo site_url('customer') ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><?php echo lang('dashboard')  ?></a>
                        <a href="<?php echo site_url('customer/orderlist') ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><?php echo sprintf(lang('my_option'), lang('order'))  ?></a>
                        <a href="<?php echo site_url('customer/profile') ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><?php echo sprintf(lang('my_option'), lang('profile')) ?></a>
                        <a href="<?php echo site_url('customer/logout') ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><?php echo lang('logout') ?></a>
                    </div>
                </div>
            <?php else : ?>
                <?php if (is_active_page('signup')) : ?><a href="<?php echo site_url('signup'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'signup' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-address-book">&nbsp;</i><?php echo lang('signup') ?></span></a><?php endif; ?>
                <?php if (is_active_page('login')) : ?><a href="<?php echo site_url('login'); ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize <?php 'login' === $header_nav ? print ' w3-white' : print '' ?>"><i class="fa fa-sign-in">&nbsp;</i><?php echo lang('login') ?></span></a><?php endif; ?>
            <?php endif; ?>
            <div class="w3-dropdown-hover w3-block w3-transparent">
                <a href="javascript:void(0)" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><i class="fa fa-language" aria-hidden="true">&nbsp;</i><?php echo lang($lang) ?>&nbsp;<i class="fa fa-angle-down"></i></a>
                <div class="w3-dropdown-content w3-white w3-animate-left">
                    <a href="<?php echo site_url('english') ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><?php echo lang('english') ?></a>
                    <a href="<?php echo site_url('bengali') ?>" class="w3-bar-item w3-button w3-block w3-left-align w3-hover-white w3-border-bottom w3-border-theme w3-text-capitalize"><?php echo lang('bangla') ?></a>
                </div>
            </div>
        </div>
    </nav>
</header>