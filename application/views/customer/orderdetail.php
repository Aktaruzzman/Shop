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
                <div class="w3-col w3-threequarter w3-row-padding w3-responsive">
                    <table class="w3-table-all customer-order-detail w3-border-light-gray">
                        <caption class="w3-white w3-large w3-padding w3-border w3-border-light-gray w3-text-upper w3-text-bold w3-text-theme" style="border-bottom: 0!important;"><?php echo sprintf(lang('order_option'), lang('detail')) ?></caption>
                        <tr class="w3-border-0 ">
                            <td style="margin: 0 auto; padding:0" class="w3-white">
                                <div class="w3-clear w3-row-padding  w3-left-align w3-margin-top">
                                    <div class="w3-col w3-half form-group">
                                        <div class="w3-text-bold"><?php echo lang('customer') ?>,</div>
                                        <div class="w3-border w3-border-light-gray w3-round w3-padding-small">
                                            <div><?php echo lang('id') ?> : <?php echo str_pad($my['id'], 6, "0", STR_PAD_LEFT) ?></div>
                                            <div><?php echo lang('name') ?> : <?php echo $my['name'][$lang] ?></div>
                                            <div><?php echo lang('phone') ?> : <?php echo $my['phone'] ?></div>
                                            <div><?php echo lang('email') ?> : <?php echo $my['email'] ?></div>
                                        </div>
                                    </div>
                                    <div class="w3-col w3-half form-group">
                                        <div class="w3-text-bold"><?php echo lang('store') ?>,</div>
                                        <div class="w3-border w3-border-light-gray w3-round w3-padding-small">
                                            <?php if (config_item('store_type') === 'multiple') : ?>
                                                <?php $store_info = store_info($sale['hub_id']); ?>
                                            <?php endif; ?>
                                            <div><?php echo lang('name') ?> :<?php echo $store_info['name'] ?></div>
                                            <div><?php echo lang('address') ?> :<?php echo $store_info['house'] . ',' . $store_info['area'] ?></div>
                                            <div><?php echo lang('phone') ?> :<?php echo $store_info['phone'] ?></div>
                                            <div><?php echo lang('email') ?> :<?php echo $store_info['email'] ?></div>
                                        </div>
                                    </div>
                                    <div class="w3-col w3-block flash-message w3-text-bold flash w3-center"></div>
                                    <div class="w3-col w3-bolck w3-section">
                                        <div class="w3-border w3-border-light-gray w3-round w3-padding w3-row w3-text-bold w3-text-upper">
                                            <div class="w3-col m3"><?php echo lang('id') ?></span>&nbsp;:&nbsp;<span><?php echo $sale['invoice'] ?></div>
                                            <div class="w3-col m3"><?php echo lang('type') ?></span>&nbsp;:&nbsp;<span class=""><?php echo lang($sale['service']) ?></div>
                                            <div class="w3-col m3"><?php echo lang('status') ?></span>&nbsp;:&nbsp;<span class="order-status <?php echo $sale['status'] == "pending" ? 'flash' : '' ?> w3-text-bold <?php echo order_status_css($sale['status'], 'text') ?>"><?php echo lang($sale['status'])  ?></div>
                                            <div class="w3-col m3"><?php echo lang('payment') ?>&nbsp;:&nbsp;
                                                <?php if ($sale['is_paid']) : ?>
                                                    <span class="w3-text-bold <?php echo order_status_css('paid', 'text') ?>"><?php echo lang('paid')  ?></span>
                                                <?php else : ?>
                                                    <span class="w3-text-bold <?php echo order_status_css('unpaid', 'text') ?>"><?php echo lang('unpaid')  ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                        <tr class="w3-border-0">
                            <td style="margin: 0 auto; padding:0" class="w3-white">
                                <div class="w3-responsive w3-padding">
                                    <table class="w3-table-all w3-border-0">
                                        <caption class="w3-large w3-text-bold w3-theme-l3 w3-text-upper"><?php echo lang('item') ?> <?php echo lang('description') ?></caption>
                                        <tr class="w3-theme-light w3-text-bold">
                                            <th class="w3-padding-small" style="max-width: 50%;"><?php echo lang('name') ?></th>
                                            <th class="w3-right-align w3-padding-small"><?php echo lang('quantity') ?></th>
                                            <th class="w3-right-align w3-padding-small"><?php echo lang('rate') ?></th>
                                            <th class="w3-right-align w3-padding-small"><?php echo lang('total') ?></th>
                                        </tr>
                                        <?php foreach ($sale['details'] as $item) : ?>
                                            <tr class="w3-white">
                                                <td class="w3-padding-small w3-text-capitalize">
                                                    <div class=""><?php echo $item['name'][$lang] ?></div>
                                                    <?php if (!empty($item['sides'])) : ?>
                                                        <?php $sets = (reorder_sides($item['sides'])) ?>
                                                        <div class="w3-row-padding">
                                                            <?php foreach ($sets as $set) : ?>
                                                                <div class="w3-text-capitalize w3-text-bold">
                                                                    <?php if ($set['group_name']['en'] === 'Fixed Set') : ?>
                                                                        <div><?php echo lang('item') ?></div>
                                                                    <?php else : ?>
                                                                        <div><?php echo $set['group_name'][$lang]  ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <?php foreach ($set['sides'] as $side) : ?>
                                                                    <div class="w3-text-bold-500">
                                                                        <span class="w3-text-capitalize"><?php echo number($side['qty']) ?> <?php config_item('show_unit') == 'yes' ? print $item['unit_name'][$lang] : '' ?> <?php echo $side['name'][$lang] ?> <?php $side['total'] > 0 ? print '[+' . amount($side['total']) . ']' : '' ?></span>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif ?>
                                                </td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($item['qty']) ?> <?php config_item('show_unit') == 'yes' ? print $item['unit_name'][$lang] : '' ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo currency($item['sell_price']) ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo currency($item['item_total']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="w3-theme-light w3-text-bold">
                                            <td class="w3-right-align w3-padding-small" colspan="3"><b><?php echo lang('subtotal') ?></b></td>
                                            <td class="w3-right-align w3-padding-small"><b><?php echo currency($sale['subtotal']) ?></b></td>
                                        </tr>
                                        <?php if ($sale['discount']) : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('discount') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['discount']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['tax']) : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('vat') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['tax']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['delivery_charge']) : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('delivery') ?> <?php echo lang('fee') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['delivery_charge']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['service_charge']) : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('service') ?> <?php echo lang('charge') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['service_charge']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['payment_fee']) : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('payment') ?> <?php echo lang('fee') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['payment_fee']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['admin_fee'] && $sale['admin_fee_per'] === 'order_excluded') : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('admin') ?> <?php echo lang('fee') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['admin_fee']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if (abs($sale['rounding']) > 0) : ?>
                                            <tr class="w3-white">
                                                <td colspan="3" class="w3-right-align w3-padding-small"><?php echo lang('rounding') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['rounding']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr class="w3-theme-light w3-text-bold">
                                            <td colspan="3" class="w3-right-align w3-padding-small"><b><?php echo lang('total') ?></b></td>
                                            <td class="w3-right-align w3-padding-small"><b><?php echo currency($sale['grand_total']) ?></b></td>
                                        </tr>
                                        <?php if ($sale['Cash'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Cash') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Cash']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['Card'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Card') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Card']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['Bikash'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Bikash') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Bikash']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['Rocket'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Rocket') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Rocket']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['Nagad'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Nagad') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Nagad']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['Voucher'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Voucher') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Voucher']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['Due'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Due') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['Due']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['change'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('Change_Due') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['change']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($sale['tips'] > 0) : ?>
                                            <tr class="">
                                                <td class="w3-right-align w3-padding-small" colspan="3"><?php echo lang('tips') ?> <?php echo lang('payment') ?></td>
                                                <td class="w3-right-align w3-padding-small"><?php echo amount($sale['tips']) ?></td>
                                            </tr>
                                        <?php endif; ?>

                                        <tr class="w3-white  w3-border-0">
                                            <td colspan="4" style="padding:4px 0px;margin:0">
                                                <div class="w3-text-bold w3-text-lower"><?php echo $sale['service'] === 'delivery' ? lang('to_whom_and_where_to_delivery') : lang('who') . ' ' . lang('and') . ' ' . lang('where_to_collect_form') ?></div>
                                                <div class="w3-padding w3-row">
                                                    <div class="w3-col w3-half w3-left-align">
                                                        <div><?php echo lang('name') ?> : <?php echo $sale['cust_meta']['name'][$lang] ?></div>
                                                        <div><?php echo lang('phone') ?> : <?php echo $sale['cust_meta']['phone'] ?></div>
                                                    </div>
                                                    <div class="w3-col w3-half w3-left-align">
                                                        <div class="w3-text-capitalize">
                                                            <?php echo lang($sale['service']) ?> <?php echo lang('time') ?> : <?php echo date('d/m/Y h:i A', $sale['delivery_time'] / 1000)  ?>
                                                        </div>
                                                        <?php if ($sale['service'] === 'collection') :  ?>
                                                            <?php if (config_item('store_type') === 'multiple') : ?>
                                                                <?php $hub = collection_point($sale['hub_id']) ?>
                                                                <div class="w3-text-capitalize"><?php echo lang('collection') ?> <?php echo lang('point') ?> : <?php echo $hub->house[$lang] ?>, <?php echo $hub->area[$lang] ?></div>
                                                                <div><?php echo lang('phone') ?> : <?php echo $hub->phone ?>, <?php echo lang('email') ?> : <?php echo $hub->email ?></div>
                                                            <?php else : ?>
                                                                <?php $hub = collection_point() ?>
                                                                <div class="w3-text-capitalize"><?php echo $hub->name[$lang] ?>, <?php echo $hub->house[$lang] ?>, <?php echo $hub->area[$lang] ?></div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                        <?php if ($sale['service'] === 'delivery') : ?>
                                                            <div class="w3-text-capitalize">
                                                                <?php echo lang('address') ?> : <?php echo $sale['cust_meta']['house'][$lang]  ?>, <?php echo $sale['cust_meta']['area'][$lang]  ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
<?php if ($sale['status'] === 'pending') : ?>
    <script>
        $(function() {
            feeding(3000, 5000);
        });

        function feeding(min, max) {
            sleep(Math.floor(Math.random() * (max - min + 1)) + min).then(function() {
                $.get(_BASE_URL_ + 'data/order/<?php echo $sale['id'] ?>', function(order) {
                    if (order.status === "pending" && Number(order.delivery_time) > new Date().getTime()) {
                        $('.flash-message').addClass('w3-text-red').text('<?php echo lang('order_review_message') ?>');
                        feeding(min, max);
                    } else if (order.status === "confirmed") {
                        $('.order-status').removeClass('w3-text-orange').addClass('w3-text-green').text('<?php echo lang('confirmed') ?>');
                        $('.flash-message').addClass('w3-text-green').removeClass('w3-text-red').text('<?php echo lang('order_confirmed_message') ?>');

                    } else if (order.status === "rejected") {
                        $('.order-status').removeClass('w3-text-orange').addClass('w3-text-red').text('<?php echo lang('rejected') ?>');
                        $('.flash-message').addClass('w3-text-red').text('<?php echo lang('order_review_message') ?>');

                    } else if (order.status === "canceled") {
                        $('.order-status').removeClass('w3-text-orange').addClass('w3-text-red').text('<?php echo lang('canceled') ?>');
                        $('.flash-message').addClass('w3-text-red').text('<?php echo lang('order_review_message') ?>');
                    }
                });
            });
        }
    </script>
<?php endif; ?>