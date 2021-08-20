<div class="contact-map">
    <?php if (!empty($store_info['map'])) : ?>
        <?php echo $store_info['map'] ?>
    <?php else : ?>
        <iframe class="w3-round w3-border w3-bodyborder-theme" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3619.1094449789234!2d91.88864561495566!3d24.89424818403802!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x375054b578c55f09%3A0xd450f0b7ec5a5eff!2sSaffron%20Restaurant%20%26%20Party%20Center!5e0!3m2!1sen!2sbd!4v1588435105239!5m2!1sen!2sbd&zoom=9" width="100%" height="<?php echo $map_height ?>" frameborder="3" style="border:3px" allowfullscreen="" aria-hidden="false" tabindex="0" zoom="16"></iframe>
    <?php endif; ?>
</div>