<?php
wp_create_nonce('wp_rest');
use ShortUrlTracker\Plugin\Sut_Tracker;
$start = new Sut_Tracker();
$activated = $start->sut_byw_is_activated();
if ($activated['activate']) {
    $html_options = "";
    $date_end = date('Y-m-d');
    $date_start = date('Y-m-d', strtotime('-1 month'));
    if (($activated['level'] != 'free')) {
        if (isset($_GET['campaign_id'])) {
            $campaign_id = sanitize_text_field(htmlentities($_GET['campaign_id']));
        }
        if (isset($_GET['enddate']) && isset($_GET['startdate'])) {
            $date_end = sanitize_text_field(htmlentities($_GET['enddate']));
            $date_start = sanitize_text_field(htmlentities($_GET['startdate']));
        }
        $campaign_details = $start->sut_byw_get_all_campaign($date_start, $date_end);
        if (isset ($campaign_details->campaign_list)) {
            $html_options .= '<select id="campaign-choice">';
            foreach ($campaign_details->campaign_list as $campaign) {
                $html_options .= '<option value="' . esc_attr($campaign->campaign_id) . '" ' . ((isset($_GET['campaign_id']) && $campaign->campaign_id == $campaign_id) ? 'selected' : '') . '>' . html_entity_decode($campaign->campaign_name) . '</option>';
            }
            $html_options .= '</select>';
        }
    }
}
?>
<section class="ga-tracking-plugin">
    <?php if ($activated['activate']): ?>
        <h1 class="header-plugin"><?php echo esc_html(__('Create Short Link','shorturl-tracker')); ?></h1>
        <h4 class="sub-title"><?php echo esc_html(__('What are you looking for (Pages, Articles, Products)', 'shorturl-tracker'))?> ?</h4>
        <div class="create-link">
            <div class="msg-search">
                <div class="search-content">
                    <fieldset id="first-step">
                        <div class="type-search-element" id="type-product">
                            <div class="tse-content">
                                <label id="search-label"><?php echo wp_kses(__('Products, Pages,<br> Posts', 'shorturl-tracker'), $start->sut_byw_get_allowed_tags()); ?></label>
                                <input type="radio" name="search" id="search-product" value="search-product">
                            </div>
                        </div>
                        <div class="type-search-element" id="type-category">
                            <div class="tse-content">
                                <label id="search-label"><?php echo wp_kses(__('Categories,<br>Product Categories', 'shorturl-tracker'), $start->sut_byw_get_allowed_tags()); ?></label>
                                <input type="radio" name="search" id="search-category" value="search-product">
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="search-element" id="search-element">
                    <img src="<?php echo esc_url(SUT_BYW_PURL . 'img/search-icon-black.png'); ?>" alt="search-button" id="search-button">
                    <input type="text" name="search_box" id="search_box" class="form-control" placeholder="<?php echo esc_attr(__('Search...', 'shorturl-tracker')); ?>" autocomplete="off">
                    <img src="<?php echo esc_url(SUT_BYW_PURL . 'img/close-cross.png'); ?>" alt="search-cleaner" id="search-cleaner" class="search-cleaner active">
                    <div class="loader" id="loader-search">
                        <svg id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" xml:space="preserve">
                <path fill="#4d6179" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">
                    <animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite"></animateTransform>
                </path>
                </svg>
                    </div>
                </div>
                <div id='results'>

                </div>
            </div>
            <div class="param-form-content" id="param-form-content">
                <div id="param-ga">
                    <label id="add-ga-param-request"><?php echo esc_html(__('Add Google Analytics Tracking','shorturl-tracker')); ?></label>
                    <input type="checkbox" name="add-ga-param" id="add-ga-param">
                </div>
                <div class="param-ga-list" id="param-ga-list">
                    <div id="param-ga" class="param-element-ga">
                        <input type="text" name="key" placeholder="utm_source" value="utm_source" readonly class="ga-label">
                        <input type="text" name="value" placeholder="Campaign Source" autocomplete="off">
                    </div>
                    <div id="param-ga" class="param-element-ga">
                        <input type="text" name="key" placeholder="utm_medium" value="utm_medium" readonly class="ga-label">
                        <input type="text" name="value" placeholder="Campaign Medium" autocomplete="off">
                    </div>
                    <div id="param-ga" class="param-element-ga">
                        <input type="text" name="key" placeholder="utm_campaign" value="utm_campaign" readonly class="ga-label">
                        <input type="text" name="value" placeholder="Campaign Name" autocomplete="off">
                    </div>
                    <div id="param-ga" class="param-element-ga">
                        <input type="text" name="key" placeholder="utm_term" value="utm_term" readonly class="ga-label">
                        <input type="text" name="value" placeholder="Campaign Term" autocomplete="off">
                    </div>
                    <div id="param-ga" class="param-element-ga">
                        <input type="text" name="key" placeholder="utm_content" value="utm_content" readonly class="ga-label">
                        <input type="text" name="value" placeholder="Campaign Content" autocomplete="off">
                    </div>
                    <div id="param-ga" class="param-element-ga">
                        <input type="text" name="key" placeholder="utm_id" value="utm_id" readonly class="ga-label">
                        <input type="text" name="value" placeholder="Campaign ID" autocomplete="off">
                    </div>
                </div>
                <div class="param-product <?php echo esc_attr((($activated['level'] == 'free') ? 'param-medium' : '')); ?>" id="param-product">
                    <label id="add-param-product"><?php echo esc_html(__('Add specific product variation', 'shorturl-tracker')); ?></label>
                    <input type="checkbox" name="add-varia-param-request" id="add-varia-param-request" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                </div>
                <div id="param-product-list" class="param-product-list">

                </div>
                <div id="param-user" class="param-user <?php echo esc_attr((($activated['level'] == 'free') ? 'param-medium' : '')); ?>">
                    <label id="add-param-request"><?php echo esc_html(__('Add Custom Param','shorturl-tracker')); ?></label>
                    <input type="checkbox" name="add-param" id="add-param" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                </div>
                <div class="param-list  <?php echo esc_attr((($activated['level'] == 'free') ? 'param-medium' : '')); ?>" id="param-list">
                    <div id="param-0" class="param-element">
                        <input type="text" name="key" id="key" placeholder="key" class="custom-param" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                        <input type="text" name="value" id="value" placeholder="value" class="custom-param-value" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                        <button onclick="addParam()" class="add-custom-param"><?php echo esc_html(__('Add', 'shorturl-tracker')); ?> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
                    </div>
                </div>
                <div id="param-name"  class="param-name <?php echo esc_attr((($activated['level'] == 'free') ? 'param-medium' : '')); ?>">
                    <label id="add-name-param-request"><?php echo esc_html(__('Add Link Name','shorturl-tracker')); ?></label>
                    <input type="checkbox" name="add-name-param" id="add-name-param" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                </div>
                <div class="param-name-request <?php echo esc_attr((($activated['level'] == 'free') ? 'param-premium' : '')); ?>" id="param-name-request">
                    <div id="param-name" class="param-name-elment">
                        <input type="text" name="url-name" placeholder="Name" id="url-name" autocomplete="off" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                    </div>
                </div>
                <div class="param-favorite <?php echo esc_attr((($activated['level'] == 'free') ? 'param-medium' : '')); ?>" id="param-campaign">
                    <label id="add-favorite-param-request"><?php echo esc_html(__('Make favorite','shorturl-tracker')); ?></label>
                    <input type="checkbox" name="add-favorite-param" id="add-favorite-param" <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                </div>
                <div class="param-campaign <?php echo esc_attr((($activated['level'] == 'free') ? 'param-medium' : '')); ?>" id="param-campaign">
                    <label id="add-campaign-param-request"><?php echo esc_html(__('Attach to Campaign','shorturl-tracker')); ?></label>
                    <input type="checkbox" name="add-campaign-param" id="add-campaign-param" <?php echo esc_attr(((isset($_GET['campaign_id'])) ? 'checked' : '')); ?> <?php echo esc_attr((($activated['level'] == 'free') ? 'readonly disabled' : '')); ?>>
                </div>
                <div class="param-campaign-request" id="param-campaign-request">
                    <?php echo ((isset($html_options)) ?  wp_kses($html_options, $start->sut_byw_get_allowed_tags()) : ''); ?>
                </div>
            </div>
            <div class="result-content" id="result-content">
                <div class="final-url" id="final-url">
                    <div class="message">
                        <div class="button-action">
                            <button type="submit" onclick="generateUrl()" id="generate-url"><span id="btn-txt"><?php echo esc_html(__('Generate Shortlink', 'shorturl-tracker')); ?></span>
                                <div class="loader submit" id="loader-generate">
                                    <svg id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" xml:space="preserve">
                            <path fill="#fff" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">
                                <animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite"></animateTransform>
                            </path>
                            </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                    <div class="link-url" id="link-url">
                        <h2 class="title-link"><?php echo esc_html(__('Short URL generated !','shorturl-tracker')); ?></h2>
                        <h2 id="url-response"></h2>
                        <h2 id="url-shorted"></h2>
                        <div id="button-copy"></div>
                        <div class="qrcode-element" id="qrcode-element"></div>
                        <div class="social-share-link" id="social-share-link">
                            <h2 class="title-link"><?php echo esc_html(__('Tell your Friends! ','shorturl-tracker')); ?></h2>
                            <div class="social-list" id="social-list"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert">
            <h1 class="header-plugin"><?php echo esc_html(__('You must Activate your License to Activate the Plugin','shorturl-tracker')); ?></h1>
            <div class="activate-bouton">
                <a href="<?php echo esc_url($_SERVER['PHP_SELF'] . '?page=sut-settings'); ?>"><?php echo esc_html(__('Activate License','shorturl-tracker')); ?></a>
            </div>
        </div>
    <?php endif; ?>
</section>