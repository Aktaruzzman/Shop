<div class="content-area <?php echo config_item('web_theme_layout') === 'box' ? 'box-top-mutual' : 'top-mutual' ?>">
    <main class="w3-content w3-wide-container <?php config_item('web_theme_layout') === 'box' ? print 'w3-container w3-section' : '' ?>">
        <div class="w3-padding-16 w3-round w3-border w3-border-theme <?php echo config_item('web_theme_layout') === 'box' ? '' : 'w3-mobile' ?>" style="<?php echo config_item('web_theme_content_bg') ?>">
            <div class="w3-container w3-center w3-margin-bottom" id="heroBanner">
                <h2 class="w3-text-capitalize"><?php echo $page_title ?></h2>
                <h4><?php echo $page_subtitle ?></h4>
                <article class="w3-left-align">
                    <?php $store_info = store_info() ?>
                    <div class="w3-section w3-center w3-text-bold w3-large"> LAST UPDATED : <?php echo date('d/m/Y h:i A', filemtime(APPPATH . 'views/site/cookiepolicy.php')) ?></div>
                    <?php $domain = '<b>' . get_domain() . '</b>' ?>
                    <?php $address = '<b>' . $store_info['house'] . ' ' . $store_info['area'] . '</b>' ?>

                    <h3><strong>Why do we use cookies?</strong></h3>
                    <p>Our website uses cookies to distinguish you from other users of our website. This helps us to provide you with a good experience when you browse our website and also allows us to improve our site. By continuing to browse the site, you are agreeing to our use of cookies.</p>

                    <h3><strong>What is a cookie?</strong></h3>
                    <p>A cookie is a small file of letters and numbers that we store on your browser or the hard drive of your computer, if you agree to our use of them. Cookies contain information that is transferred to your computer's hard drive.</p>

                    <h3><strong>The different types of cookies we use:</strong></h3>
                    <h4><strong>Strictly necessary cookies.</strong></h4>
                    <p>These are cookies that are required for the operation of our website. They include, for example, cookies that enable you to log into secure areas of our website, use a shopping cart or make use of e-billing services.</p>

                    <h4><strong>Analytical/performance cookies.</strong></h4>
                    <p>They allow us to recognise and count the number of visitors and to see how visitors move around our website when they are using it. This helps us to improve the way our website works, for example, by ensuring that users are finding what they are looking for easily.</p>

                    <h4><strong>Functionality cookies.</strong></h4>
                    <p>These are used to recognise you when you return to our website. This enables us to personalise our content for you, greet you by name and remember your preferences (for example, your choice of language or region).</p>

                    <h4><strong>Targeting cookies.</strong></h4>
                    <p>These cookies record your visit to our website, the pages you have visited and the links you have followed. We will use this information to make our website and the advertising displayed on it more relevant to your interests. We may also share this information with third parties for this purpose.</p>

                    <h3><strong>Third parties</strong></h3>
                    <p>Please note that third parties (including, for example, advertising networks and providers of external services like web traffic analysis services) may also use cookies, over which we have no control. These cookies are likely to be analytical/performance cookies or targeting cookies.</p>

                    <h3><strong>Disabling cookies.</strong></h3>
                    <p>You block cookies by activating the setting on your browser that allows you to refuse the setting of all or some cookies. However, if you use your browser settings to block all cookies (including essential cookies) you may not be able to access all or parts of our site.</p>
                </article>
            </div>
        </div>
    </main>
</div>