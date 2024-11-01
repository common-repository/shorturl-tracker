<?php
require_once('class/Sut_Byw_Campaign.php');
wp_create_nonce('wp_rest');
$alert = $campaign_list = $class = $historic = $historic_link = "";
$start = new \ShortUrlTracker\Plugin\Sut_Byw_Campaign();
$activated = $start->sut_byw_is_activated();
$date_jour = $date_day = date('Y-m-d');
$date_end = date('Y-m-d');
$wrong_parameter = false;
$date_start = date('Y-m-d', strtotime('-1 month'));
if ($activated['activate'] && $activated['level'] != 'free') {
    if (isset($_GET['campaign_id'])) {
        $start->sut_byw_campaign_js();
    }
    if (isset($_POST) && isset($_POST['from']) && isset($_POST['to'])) {
        if ($_POST['from'] <= $_POST['to']) {
            $date_end = sanitize_text_field(htmlentities($_POST['to']));
            $date_start = sanitize_text_field(htmlentities($_POST['from']));
            if (isset($_GET['campaign_id'])) {
                $campaign_list = $start->sut_byw_get_campaign_details(sanitize_text_field(htmlentities($_GET['campaign_id'])), $date_start, $date_end);
                if ($campaign_list->date) {
                    $start->sut_byw_campaign_value_js((array)$campaign_list->date, $date_start, $date_end);
                } else {
                    $start->sut_byw_campaign_value_js(array(), $date_start, $date_end);
                }
                $historic_link = $start->sut_byw_format_data($campaign_list, $date_start, $date_end);
            } else {
                $all_historic = $start->sut_byw_get_all_campaign($date_start, $date_end);
                $historic = $start->sut_byw_format_campaign_data($all_historic, $date_start, $date_end);
            }
        } else {
            $alert = "Start date can't be after end Date";
            $class = "error-alert";
            $date_end = date('Y-m-d');
            $date_start = date('Y-m-d', strtotime('-1 month'));
        }
    } elseif (isset($_GET['campaign_id']) && isset($_GET['startdate']) && isset($_GET['enddate'])) {
        $date_end = sanitize_text_field(htmlentities($_GET['enddate']));
        $date_start = sanitize_text_field(htmlentities($_GET['startdate']));
        if ($start->sut_byw_check_valid_date($date_end) && $start->sut_byw_check_valid_date($date_start)) {
            $campaign_list = $start->sut_byw_get_campaign_details(sanitize_text_field(htmlentities($_GET['campaign_id'])), $date_start, $date_end);
            $debug = $campaign_list;
            if (!isset($campaign_list->code)) {
                if (isset($campaign_list->date) && $campaign_list->date != null) {
                    $start->sut_byw_campaign_value_js((array)$campaign_list->date, $date_start, $date_end);
                } else {
                    $start->sut_byw_campaign_value_js(array(), $date_start, $date_end);
                }
                $historic_link = $start->sut_byw_format_data($campaign_list, $date_start, $date_end);
            }
        } else {
            $historic = '<p class="error-field">Wrong Parameters !</p>';
            $wrong_parameter = true;
        }
    } else {
        $date_end = date('Y-m-d');
        $date_start = date('Y-m-d', strtotime('-1 month'));
        $all_historic = $start->sut_byw_get_all_campaign($date_start, $date_end);
        $historic = $start->sut_byw_format_campaign_data($all_historic, $date_start, $date_end);
    }
}
?>

<section class="ga-tracking-plugin">
    <div class="back-link">
        <a href="<?php echo ((isset($_SERVER['HTTP_REFERER'])) ? esc_url($_SERVER['HTTP_REFERER']) : esc_url($_SERVER['PHP_SELF'] . '?page=sut-home')); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <p><?php echo esc_html(__('Back', 'shorturl-tracker')); ?></p>
        </a>
    </div>
    <?php if ($activated['activate'] && $activated['level'] != 'free'): ?>
    <?php if (isset($_GET['campaign_id']) && !isset($campaign_list->code) && !$wrong_parameter): ?>
        <div class="page-historic">
            <div class="historic-content">
                <div class="top-title">
                    <h1 class="header-plugin"><?php echo esc_html(__('Campaign Name :','shorturl-tracker'));?> <span class="link-id"><?php echo ((isset($campaign_list->campaign_name)) ? htmlspecialchars_decode(esc_html($campaign_list->campaign_name)) : ''); ?></span></h1>
                    <h4 class="header-plugin"><?php echo ((isset($campaign_list->campaign_description)) ? esc_html(__('Description : ','shorturl-tracker')) . htmlspecialchars_decode(esc_html($campaign_list->campaign_description)) : ''); ?></h4>
                </div>
                <div class="top-content">
                    <ul class="list-elements">
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Created Links', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </div>
                                <h2><?php echo esc_html($campaign_list->total_links); ?></h2>
                                <h4><a href="<?php echo esc_url(get_admin_url() . 'admin.php?page=sut-create-link&campaign_id=' . $campaign_list->campaign_id); ?>"><?php echo esc_html(__( 'Add Link', 'shorturl-tracker' )); ?></a></h4>
                            </div>
                        </li>
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Visitors', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mouse-pointer"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="M13 13l6 6"/></svg>
                                </div>
                                <h2><?php echo esc_html($campaign_list->totalClick); ?> / <?php echo esc_html($campaign_list->uniqueClick); ?></h2>
                                <h4><?php echo esc_html(__('Total / Single', 'shorturl-tracker')) ?></h4>
                            </div>
                        </li>
                        <li <?php echo esc_html((($activated['level'] != 'premium') ? 'upgrade-plugin' : '')); ?>>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Orders', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                </div>
                                <?php if($activated['level'] == 'premium'): ?>
                                    <h2><?php echo ((isset($campaign_list->total_purchase)) ? esc_html($campaign_list->total_purchase) : '0'); ?> / <?php echo ((isset($campaign_list->total_amount)) ? esc_html($campaign_list->total_amount) : '0'); ?>€</h2>
                                    <h4><?php echo esc_html(__('Orders / Amount', 'shorturl-tracker')) ?></h4>
                                <?php else: ?>
                                    <h2><?php echo esc_html(__('Premium Feature','shorturl-tracker')) ?></h2>
                                    <h4><a href="<?php echo esc_url($_SERVER['PHP_SELF'] . '?page=sut-settings'); ?>"><?php echo __('Upgrade License','shorturl-tracker'); ?></a></h4>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Device', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                </div>
                                <h2><?php echo esc_html(((isset($campaign_list->device->desktop)) ? $campaign_list->device->desktop : '0')); ?> / <?php echo esc_html(((isset($campaign_list->device->mobile)) ? $campaign_list->device->mobile : '0')); ?></h2>
                                <h4><?php echo esc_html(__('Desktop / Mobile', 'shorturl-tracker')) ?></h4>
                            </div>
                        </li>
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Top Link', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                                <h2><?php echo esc_html($campaign_list->top_link->totalclick) ?> Click<?php echo esc_html((($campaign_list->top_link->totalclick > 1) ? 's' : '')); ?></h2>
                                <h4><a href="<?php echo esc_url(get_admin_url() . 'admin.php?page=sut-historic&link_id=' . $campaign_list->top_link->shortcode . '&startdate=' . $date_start . '&enddate=' . $date_end); ?>"><?php echo esc_html($campaign_list->top_link->shortcode); ?></a></h4>
                            </div>
                        </li>
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Change date','shorturl-tracker')); ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                                <form action="" method="POST" id="search-date">
                                    <div class="input-date-search">
                                        <label for="start"><?php echo esc_html(__('From', 'shorturl-tracker')); ?>:</label>
                                        <input type="date" id="start" name="from" value="<?php echo esc_html($date_start); ?>" min="01/01/2021" max="<?php echo esc_html($date_day); ?>">
                                    </div>
                                    <div class="input-date-search">
                                        <label for="start"><?php echo esc_html(__('To', 'shorturl-tracker')); ?>:</label>
                                        <input type="date" id="end" name="to" value="<?php echo esc_html($date_end); ?>" min="01/01/2021" max="<?php echo esc_html($date_day); ?>">
                                    </div>
                                    <button type="submit" id="search-button"><?php echo esc_html(__('Search', 'shorturl-tracker')); ?></button>
                                </form>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="mid-dashboard" id="mid">
            <div class="mid-left-dashboard" id="mid-left">
                <canvas id="dashboardChart" width="400" height="400"></canvas>
            </div>
            <div class="mid-right-dashboard" id="mid-right">

            </div>
        </div>
        <div class="bottom-dashboard" id="bottom">
            <div class="historic-data">
                <?php echo wp_kses($historic_link, $start->sut_byw_get_allowed_tags()); ?>
            </div>
        </div>
    <?php else: ?>
    <div class="page-historic">
        <div class="historic-content">
            <div class="top-title">
                <h1 class="header-plugin"><?php echo esc_html(__('All Campaigns', 'shorturl-tracker')); ?></h1>
            </div>
            <div class="<?php echo esc_attr($class); ?>"><?php echo esc_html ((($alert) ? : '')); ?></div>
            <div class="search-bar">
                <form action="" method="POST" id="form-search">
                    <div class="input-date">
                        <label for="start"><?php echo esc_html(__('From', 'shorturl-tracker')); ?>:</label>
                        <input type="date" id="start" name="from" value="<?php echo esc_attr($date_start); ?>" min="01/01/2021" max="<?php echo esc_attr($date_jour); ?>">
                    </div>
                    <div class="input-date">
                        <label for="start"><?php echo esc_html(__('To', 'shorturl-tracker')); ?>:</label>
                        <input type="date" id="end" name="to" value="<?php echo esc_attr($date_end); ?>" min="01/01/2021" max="<?php echo esc_attr($date_jour); ?>">
                    </div>
                    <button type="submit" id="search-historic"><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ) . 'img/search-icon.png'); ?>" alt="search-button" id="search-button"></button>
                </form>
            </div>
            <div class="historic-data">
                <?php echo wp_kses($historic, $start->sut_byw_get_allowed_tags()); ?>
            </div>
            <div class="popup-result" id="popup-result">
                <div class="titre-element" id="titre-element">

                </div>
                <div id="graph">

                </div>
                <div class="close-cross" id="close-cross">
                    <img src="<?php echo esc_url(plugin_dir_url( __FILE__ ) . 'img/close-cross.png'); ?>" alt="cross-button" id="close-button">
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php elseif ($activated['activate'] && $activated['level'] == 'free'): ?>
            <div class="alert">
                <h1 class="header-plugin"><?php echo esc_html(__('All Campaigns','shorturl-tracker')); ?></h1>
                <p class="medium-feature"><?php echo esc_html(__('Upgrade license to Medium Level to manage Campaign', 'shorturl-tracker')); ?></p>
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