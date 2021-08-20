<div id="preorderPermissionPopup" class="w3-modal w3-text-bold">
    <div class="w3-modal-content w3-white" id="preorderPermission"></div>
</div>
<script id="preorderPermissionTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <a href="javascript:void(0)" onclick="closePopup()" class="w3-display-topright w3-hover-none w3-padding-8"><i class="fa fa-times w3-xlarge w3-padding-small"></i></a>
        <h4 class="w3-text-capitalize w3-text-bold">{{preorder_popup_head}}</h4>
    </header>
    <div class="w3-container">
       <div class="w3-padding-16">
            <div class="w3-text-bold-500">
                <span>{{preorder_popup_text}}</span>
                <a class="w3-border-bottom" href="javascript:void(0)" onclick="openPopup('daysOpeningTimePopup')">{{view_opening_hours}}</a>
            </div>
            <div class="w3-section-small w3-border w3-border-light-gray w3-text-theme w3-round w3-cursor-pointer submit-preorder-permission">
                <div class="w3-padding w3-center w3-text-upper"><i></i>{{click_here_for_preorder}}</div>
            </div>
        </div>
    </div>
</script>