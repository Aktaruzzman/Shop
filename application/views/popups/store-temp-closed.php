<div id="storeTempClosedPopup" class="w3-modal w3-text-bold">
    <div class="w3-modal-content w3-white" id="storeTempClosed"></div>
</div>
<script id="storeTempClosedTemplate" type="text/x-handlebars-template">
    <header class="w3-container w3-theme-d1 w3-padding">
        <span onclick="closePopup()" class="w3-display-topright w3-hover-none"><i class="fa fa-times w3-padding-small"></i></span>
        <h3 class="w3-text-upper">{{temp_closed_popup_head}}</h3>
    </header>
    <div class="w3-container w3-text-red">
       <div class="w3-padding-16">
            <div class="w3-text-bold-500">{{temp_closed_popup_text}}</div>
        </div>
    </div>
</script>