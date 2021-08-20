<ul class="w3-ul w3-white w3-border w3-border-theme w3-round category-block w3-text-capitalize w3-padding-small w3-padding-8" id="menuCategoryList" style="min-height:30vh; max-height:70vh;">
    <div class="w3-padding-48 w3-center"><span class="spinning"><img src="<?php echo ASSET_PATH . "img/ajax-loader.gif" ?>" class="w3-image w3-circle w3-green"></span></div>
</ul>
<script id="menuCategoryListTemplate" type="text/x-handlebars-template">
    {{#each categories}}
        {{#if items}}
            <li onclick="scrollToPosition('#cat-{{id}}',this)" class="w3-block w3-hover-text-theme category {{#unless @last}}w3-border-bottom w3-bodyborder-theme {{else}}w3-border-0{{/unless}}"><a class="w3-block" href="javascript:void(0)">{{langof name lang}}</a></li>
        {{/if}}
    {{/each}}
</script>