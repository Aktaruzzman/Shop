<ul class="w3-ul" id="opening-hours">
    <?php $openings = get_rows('openings') ?>
    <?php foreach ($openings as $key => $obj) : ?>
        <li class="w3-bodyborder-theme w3-clear <?php $obj->day === date('D') ? print 'w3-theme w3-round' : '' ?>">
            <div class="w3-col s3 w3-left-align"><?php echo lang($obj->day) ?></div>
            <?php if ($obj->opened) : ?>
                <div class="w3-col s9 w3-right-align"><?php echo to_bengali(date('h:i A', strtotime($obj->start)))  ?> - <?php echo to_bengali(date('h:i A', strtotime($obj->end)))  ?></div>
            <?php else : ?>
                <div class="w3-col s9 w3-right-align"><?php echo lang('closed') ?></div>
            <?php endif; ?>
        </li>
    <?php endforeach ?>
</ul>