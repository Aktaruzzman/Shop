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
                <div class="w3-col w3-threequarter w3-margin-bottom w3-responsive">
                    <table class="w3-table-all w3-border-light-gray w3-white">
                        <caption class="w3-text-bold w3-padding w3-text-upper w3-white w3-border-light-gray w3-text-theme w3-border-left w3-border-right w3-border-top w3-table-caption w3-large"><?php echo sprintf(lang('my_option'), lang('order')) ?></caption>
                        <thead>
                            <tr class="w3-center">
                                <th class="w3-text-bold w3-text-upper w3-border-light-gray w3-left-align w3-hide-small"><?php echo lang('id') ?></th>
                                <th class="w3-text-bold w3-text-capitalize w3-border-light-gray w3-center"><?php echo lang('date') ?></th>
                                <th class="w3-text-bold w3-text-capitalize w3-border-light-gray w3-right-align" style="width: 20%"><?php echo lang('amount') ?></th>
                                <th class="w3-text-bold w3-text-capitalize w3-border-light-gray w3-center"><?php echo lang('status') ?></th>
                                <th class="w3-text-bold w3-text-capitalize w3-border-light-gray w3-center"><?php echo lang('detail') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderlist as $order) : ?>
                                <tr class="w3-center">
                                    <td class="w3-border-light-gray w3-left-align w3-hide-small"><?php echo $order->invoice ?></td>
                                    <td class="w3-border-light-gray w3-center"><?php echo date('d/m/Y h:i A', strtotime($order->created_at))  ?></td>
                                    <td class="w3-border-light-gray w3-right-align"><?php echo currency($order->grand_total) ?></td>
                                    <td class="<?php echo order_status_css($order->status, 'text') ?> w3-border-light-gray w3-center"><?php echo lang($order->status) ?></td>
                                    <td class="w3-border-light-gray w3-center"><a href="<?php echo site_url(array('customer', 'orderdetail', $order->id)) ?>"><i class="fa fa-eye w3-text-theme"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!empty($pagination)) : ?>
                        <div class="w3-block w3-padding-8 w3-center">
                            <?php echo $pagination ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>