<ul class="w3-ul sidebar w3-white w3-border w3-border-light-gray w3-round w3-mobile w3-text-bold-500">
    <li class="w3-center w3-large w3-text-upper w3-text-bold w3-text-theme"><?php echo $my['name'][lang_option()] ?> <?php if ($my['is_verified']) : ?><i class="fa fa-check-circle w3-text-green"></i><?php endif ?></li>
    <li class="w3-center w3-border-light-gray">
        <img src="<?php $my['photo'] ? print UPLOAD_PATH . 'customer/' . $my['photo'] : print ASSET_PATH . 'img/user.png' ?>" title="<?php echo $my['name']['en'] ?>" class="w3-image w3-auto  w3-circle" style="width: 150px; height: 130px;" /> <br />
        <?php if (!empty($my['phone'])) : ?>
            <i class="fa fa-phone">&nbsp;</i><?php echo $my['phone'] ?><br />
        <?php endif; ?>
        <?php if (!empty($my['email'])) : ?>
            <i class="fa fa-envelope-o">&nbsp;</i><?php echo $my['email'] ?>
        <?php endif; ?>
    </li>
    <li class="w3-border-light-gray <?php $sidebar_nav === 'dashboard' ? print ' w3-text-theme' : print ' not-active' ?>"><a href="<?php echo site_url('customer') ?>" class="w3-block"><?php echo lang('dashboard') ?></a></li>
    <li class="w3-border-light-gray <?php $sidebar_nav === 'orderlist' ? print ' w3-text-theme' : print ' not-active' ?>"><a href="<?php echo site_url('customer/orderlist') ?>" class="w3-block"><?php echo sprintf(lang('my_option'), lang('order')) ?></a></li>
    <li class="w3-border-light-gray <?php $sidebar_nav === 'point' ? print ' w3-text-theme' : print ' not-active' ?>"><a href="<?php echo site_url('customer/point') ?>" class="w3-block"><?php echo sprintf(lang('my_option'), lang('point')) ?></a></li>
    <li class="w3-border-light-gray <?php $sidebar_nav === 'profile' ? print ' w3-text-theme' : print ' not-active' ?>"><a href="<?php echo site_url('customer/profile') ?>" class="w3-block"><?php echo sprintf(lang('my_option'), lang('profile')) ?></a></li>
    <li class="w3-border-light-gray <?php $sidebar_nav === 'changepassword' ? print ' w3-text-theme' : print ' not-active' ?>"><a href="<?php echo site_url('customer/changepassword') ?>" class="w3-block"><?php echo lang('password') . ' ' . lang('Change') ?></a></li>
    <li class="w3-border-light-gray <?php $sidebar_nav === 'addressbook' ? print ' w3-text-theme' : print ' not-active' ?>"><a href="<?php echo site_url('customer/addressbook') ?>" class="w3-block"><?php echo sprintf(lang('delivery_option'), lang('Address')) ?></a></li>
</ul>