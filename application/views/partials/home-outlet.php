 <div class="w3-row-padding">
     <h3 class="w3-col w3-block w3-center w3-text-upper"><?php echo lang('sales_service_centers') ?></h3>
     <h4 class="w3-col w3-block w3-center"><?php echo lang('choose_outlet_to_order') ?></h4>
     <?php foreach ($stores as $store) : ?>
         <div class="w3-col w3-quarter w3-section-small w3-cursor-pointer" onclick="window.location.href='<?php echo site_url(order_slug($store['id'])) ?>'">
             <div class="w3-block w3-border w3-padding w3-bodyborder-theme w3-round <?php $store['id'] == $this->session->userdata('store_branch') ? print 'w3-text-bold w3-text-deep-orange' : 'w3-text-bold-500' ?>">
                 <div class="w3-text-bold"><?php echo $store['name'][$lang] ?></div>
                 <div class="w3-small"><?php echo $store['house'][$lang] ?></div>
                 <div class="w3-small"><?php echo $store['area'][$lang] ?></div>
                 <div class="w3-small"><?php echo $store['phone'] ?></div>
                 <div class="w3-small"><?php echo $store['email'] ?></div>
             </div>
         </div>
     <?php endforeach; ?>
 </div>