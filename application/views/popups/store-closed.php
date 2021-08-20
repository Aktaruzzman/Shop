<div id="storeClosedPopup" class="w3-modal">
    <div class="w3-modal-content w3-white" id="storeClosed"></div>
</div>
<script id="storeClosedTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold">{{closed_popup_head}}</h4>
    </header>
    <div class="w3-container">
       <div class="w3-padding-16 w3-row">
            <div class="w3-col w3-block"><img src="<?php echo ASSET_PATH . 'img/store_close.gif' ?>" class="w3-image"></div>
            <div class="w3-col w3-block">
             {{closed_popup_text}} <a class="w3-border-bottom w3-text-bold" href="javascript:void(0)" onclick="openPopup('daysOpeningTimePopup')">{{view_opening_hours}}</a>
            </div>
        </div>
    </div>
</script>