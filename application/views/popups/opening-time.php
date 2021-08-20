<div id="daysOpeningTimePopup" class="w3-modal">
    <div class="w3-modal-content w3-white">
        <header class="w3-container w3-theme-d1 w3-padding">
            <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
            <h4 class="w3-text-capitalize w3-text-bold"><?php echo lang('opening_hours') ?></h4>
        </header>
        <div class="w3-container">
            <ul class="w3-ul w3-padding-16">
                <?php $openings = get_rows('openings') ?>
                <?php foreach ($openings as $key => $obj) : ?>
                    <li class="w3-clear w3-padding <?php $obj->day === date('D') ? print 'w3-text-theme w3-text-bold' : '' ?>">
                        <div class="w3-col s3 w3-left-align"><?php echo lang($obj->day) ?></div>
                        <?php if ($obj->opened) : ?>
                            <div class="w3-col s9 w3-right-align"><?php echo to_bengali(date('h:i A', strtotime($obj->start)))  ?> - <?php echo to_bengali(date('h:i A', strtotime($obj->end)))  ?></div>
                        <?php else : ?>
                            <div class="w3-col s9 w3-right-align"><?php echo lang('closed') ?></div>
                        <?php endif; ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
    </div>
</div>