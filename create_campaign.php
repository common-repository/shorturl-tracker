<?php
wp_create_nonce('wp_rest');
require_once('class/Sut_Byw_Campaign.php');
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 month'));
$campaign_data = "";
$start = new \ShortUrlTracker\Plugin\Sut_Byw_Campaign();
$start->sut_byw_create_campaign_js();
$activated = $start->sut_byw_is_activated();
if ($activated['activate']) {
    if (isset($_POST) && isset($_POST['name']) && isset($_POST['description'])) {
        $name = sanitize_text_field(htmlentities($_POST['name']));
        $description = sanitize_text_field(htmlentities($_POST['description']));
        $favorite = sanitize_text_field(htmlentities($_POST['favorite']));
        if ($favorite == "on") {
            $favorite = true;
        } else {
            $favorite = false;
        }
        $data = $start->sut_byw_create_campaign($name, $description, $favorite);
    }
    if (isset($_GET['campaign_id'])) {
        $campaign_data = $start->sut_byw_get_campaign_details(sanitize_text_field(htmlentities($_GET['campaign_id'])), $date_start, $date_end);
    }
}
?>
<section class="ga-tracking-plugin">
    <div class="back-link">
        <a href="<?php echo esc_url(((isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['PHP_SELF'] . '?page=sut-home')); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <p><?php echo esc_html(__('Back', 'shorturl-tracker')); ?></p>
        </a>
    </div>
    <?php if ($activated['activate'] && $activated['level'] != 'free'): ?>
        <?php if (isset($_GET['campaign_id'])): ?>
            <div class="content">
                <h1 class="header-plugin success"><?php echo esc_html(__('Campaign','shorturl-tracker')); ?> <span class="campaign-name"><?php echo esc_html($campaign_data->campaign_name); ?></span> <?php echo esc_html(__('created !','shorturl-tracker')); ?></h1>
                <div class="msg-search">
                    <h4 class="sub-title success"><?php echo esc_html(__('Description', 'shorturl-tracker')); ?> : <?php echo esc_html($campaign_data->campaign_description); ?></h4>
                </div>
                <div class="campaign-detail">
                    <div class="data-link">
                        <div class="left-part">
                            <h4><?php echo esc_html(__( 'Created on', 'shorturl-tracker' )) . ' <span class="in-focus">' . esc_html($this->sut_byw_format_date($campaign_data->created_date)) . '</span>'; ?></h4>
                            <h2><span class="in-focus"><?php echo esc_html($campaign_data->campaign_name); ?></span></h2>
                            <h3><?php echo esc_html($campaign_data->campaign_description); ?></h3>
                        </div>
                        <div class="right-part">
                            <a href="<?php echo esc_url(get_admin_url() . 'admin.php?page=sut-create-link&campaign_id='.$campaign_data->campaign_id); ?>"><?php echo esc_html(__( 'Add Link', 'shorturl-tracker' )); ?></a>
                        </div>
                        <div class="favorite-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="<?php echo esc_html((!$campaign_data->isFavorite ? 'none' : 'currentColor')); ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-heart"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <h1 class="header-plugin"><?php echo esc_html(__('Create a campaign','shorturl-tracker')); ?></h1>
            <div class="msg-search">
                <h4 class="sub-title"><?php echo esc_html(__('Start a campaign and add links', 'shorturl-tracker')); ?></h4>
            </div>
            <div class="generate-form" id="generate-form">
                <form method="POST" id="create-campaign-form" name="Form">
                    <input type="text" name="name" id="name" placeholder="Campaign Name" required />
                    <input type="text" name="description" id="description" placeholder="Campaign Description" required />
                    <div class="favorite-box">
                        <label><?php echo esc_html(__('Make favorite','shorturl-tracker')); ?></label>
                        <input type="checkbox" name="favorite">
                    </div>
                    <button type="submit" id="create-campaign-button"><?php echo esc_html(__('Create Campaign','shorturl-tracker')); ?>
                        <div class="loader submit" id="loader-generate">
                            <svg id="L9" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100" xml:space="preserve">
                    <path fill="#fff" d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50">
                        <animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite"></animateTransform>
                    </path>
                    </svg>
                        </div>
                    </button>
                </form>
            </div>
        <?php endif; ?>
    <?php elseif ($activated['activate'] && $activated['level'] == 'free'): ?>
        <div class="alert">
            <h1 class="header-plugin"><?php echo esc_html(__('Create Campaign','shorturl-tracker')); ?></h1>
            <p class="medium-feature"><?php echo esc_html(__('Upgrade license to Medium Level to Create Campaign', 'shorturl-tracker')); ?></p>
            <div class="activate-bouton">
                <a href="<?php echo esc_url($_SERVER['PHP_SELF'] . '?page=sut-settings'); ?>"><?php echo esc_html(__('Upgrade License','shorturl-tracker')); ?></a>
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