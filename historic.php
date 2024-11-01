<?php
require_once('class/Sut_Byw_Historic.php');
wp_create_nonce('wp_rest');
$alert = $link_historic = $class = $historic = "";
$start = new \ShortUrlTracker\Plugin\Sut_Byw_Historic();
$activated = $start->sut_byw_is_activated();
$wrong_parameter = false;
$date_end = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 month'));
$date_jour = date('Y-m-d');
if ($activated['activate']) {
    $start->sut_byw_chart_js();
    if (isset($_POST) && isset($_POST['from']) && isset($_POST['to'])) {
        if ($_POST['from'] <= $_POST['to']) {
            $date_end = sanitize_text_field(htmlentities($_POST['to']));
            $date_start = sanitize_text_field(htmlentities($_POST['from']));
            if (isset($_GET['link_id'])) {
                $link_historic = $start->sut_byw_get_link_historic(sanitize_text_field(htmlentities($_GET['link_id'])), $date_start, $date_end);
                $start->sut_byw_graph_value_js((array)$link_historic->date, $date_start, $date_end);
            } else {
                $historic = $start->sut_byw_call_historic($date_start, $date_end);
            }
        } else {
            $alert = "Start date can't be after end Date";
            $class = "error-alert";
            $date_end = date('Y-m-d');
            $date_start = date('Y-m-d', strtotime('-1 month'));
        }
    } elseif (isset($_GET['link_id']) && isset($_GET['startdate']) && isset($_GET['enddate'])) {
        $date_end = sanitize_text_field(htmlentities($_GET['enddate']));
        $date_start = sanitize_text_field(htmlentities($_GET['startdate']));
        if ( $start->sut_byw_check_valid_date($date_end) && $start->sut_byw_check_valid_date($date_start) ) {
            $link_historic = $start->sut_byw_get_link_historic(sanitize_text_field(htmlentities($_GET['link_id'])), $date_start, $date_end);
            if (!isset($link_historic->code)) {
                $start->sut_byw_graph_value_js((array)$link_historic->date, $date_start, $date_end);
            }
        } else {
            $historic = '<p class="error-field">Wrong Parameters !</p>';
            $wrong_parameter = true;
        }
    } else {
        $date_end = date('Y-m-d');
        $date_start = date('Y-m-d', strtotime('-1 month'));
        $historic = $start->sut_byw_call_historic($date_start, $date_end);
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
    <?php if ($activated['activate']): ?>
    <?php if (isset($_GET['link_id']) && !isset($link_historic->code) && !$wrong_parameter): ?>
        <div class="social-share-link">
            <?php echo wp_kses($start->sut_byw_build_social_share_block($_GET['link_id']), $start->sut_byw_get_allowed_tags()); ?>
        </div>
        <div class="page-historic">
            <div class="historic-content">
                <div class="top-title">
                    <h1 class="header-plugin" id="header-plugin-first"><?php echo esc_html(__('Information for link:','shorturl-tracker'));?> <span class="link-id" id="<?php echo $_GET['link_id']; ?>" onclick="copyLink('<?php echo sanitize_text_field($_GET['link_id']); ?>')"><?php echo esc_html(sanitize_text_field($_GET['link_id'])); ?></span><span id="copy-button"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link copy-link" id="copy-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg><span id="copied-txt"><?php echo esc_html(__('Copy', 'shorturl-tracker')); ?></span></span></h1>
                    <h4 class="header-plugin"><?php echo esc_html(((isset($link_historic->destination)) ? 'URL: ' . htmlspecialchars_decode($link_historic->destination) : '')); ?></h4>
                    <h4 class="header-plugin"><?php echo esc_html(((isset($link_historic->link_name)) ? __('Name : ','shorturl-tracker') . htmlspecialchars_decode($link_historic->link_name) : '')); ?></h4>
                    <?php if($activated['level'] == 'free'): ?><p class="medium-feature"><?php echo esc_html(__('Upgrade license to Medium Level to modify date', 'shorturl-tracker')); ?></p><?php endif; ?>
                </div>
                <div class="top-content">
                    <ul class="list-elements">
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Created Date', 'shorturl-tracker')); ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            </div>
                            <h2><?php echo esc_html($this->sut_byw_format_date($link_historic->created_date)); ?></h2>
                            <h4><?php echo ((isset($link_historic->campaign_id) && $link_historic->campaign_id != null) ? '<a href="'.esc_url(get_admin_url() . 'admin.php?page=sut-get-campaign&campaign_id=' . $link_historic->campaign_id .'&startdate=' . $date_start . '&enddate=' . $date_end) . '">' . esc_html(__('Go to Campaign', 'shorturl-tracker')) . '</a>' : esc_html(__('Date', 'shorturl-tracker'))) ?></h4>
                </div>
                </li>
                <li>
                    <div class="list-content">
                        <div class="icon">
                            <h4><?php echo esc_html(__('Visitors', 'shorturl-tracker')); ?></h4>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mouse-pointer"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="M13 13l6 6"/></svg>
                        </div>
                        <h2><?php echo esc_html(((isset($link_historic->totalClick)) ? $link_historic->totalClick : 'error')); ?> / <?php echo esc_html(((isset($link_historic->uniqueClick)) ? $link_historic->uniqueClick : 'error')); ?></h2>
                        <h4><?php echo esc_html(__('Total / Single', 'shorturl-tracker')); ?></h4>
                    </div>
                </li>
                <li <?php echo (($activated['level'] != 'premium') ? 'class="upgrade-plugin"' : ''); ?>>
                    <div class="list-content">
                        <div class="icon">
                            <h4><?php echo esc_html(__('Orders', 'shorturl-tracker')) ?></h4>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        </div>
                        <?php if($activated['level'] == 'premium'): ?>
                            <h2><?php echo esc_html(((isset($link_historic->total_purchase)) ? $link_historic->total_purchase : '0')); ?> / <?php echo esc_html(((isset($link_historic->total_amount)) ? $link_historic->total_amount : '0')); ?>€</h2>
                            <h4><?php echo esc_html(__('Orders / Amount', 'shorturl-tracker')); ?></h4>
                        <?php else: ?>
                            <h2><?php echo esc_html(__('Premium Feature','shorturl-tracker')); ?></h2>
                            <h4><a href="<?php echo esc_url($_SERVER['PHP_SELF'] . '?page=sut-settings'); ?>"><?php echo esc_html(__('Upgrade License','shorturl-tracker')); ?></a></h4>
                        <?php endif; ?>
                    </div>
                </li>
                <li <?php echo (($activated['level'] == 'free') ? 'class="upgrade-plugin"' : ''); ?>>
                    <div class="list-content">
                        <div class="icon">
                            <h4><?php echo esc_html(__('Device', 'shorturl-tracker')); ?></h4>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        </div>
                        <?php if($activated['level'] != 'free'): ?>
                            <h2><?php echo esc_html($link_historic->device->desktop); ?> / <?php echo esc_html($link_historic->device->mobile); ?></h2>
                            <h4><?php echo esc_html(__('Desktop / Mobile', 'shorturl-tracker')); ?></h4>
                        <?php else: ?>
                            <h2><?php echo esc_html(__('Medium Feature','shorturl-tracker')); ?></h2>
                            <h4><a href="<?php echo esc_url($_SERVER['PHP_SELF'] . '?page=sut-settings'); ?>"><?php echo esc_html(__('Upgrade License','shorturl-tracker')); ?></a></h4>
                        <?php endif; ?>
                    </div>
                </li>
                <li>
                    <div class="list-content">
                        <div class="icon">
                            <h4><?php echo esc_html(__('QRCode', 'shorturl-tracker')); ?></h4>
                            <h4 class="scan_count"><?php echo esc_html(__('SCAN', 'shorturl-tracker')); ?> : <?php echo esc_html($link_historic->qrcode->scan_count); ?></h4>
                        </div>
                        <img src="<?php echo esc_html($link_historic->qrcode->formats->small); ?>" class="historic-link-qrcode" alt="qrcode">
                    </div>
                </li>
                <li <?php echo (($activated['level'] == 'free') ? 'class="upgrade-plugin"' : ''); ?>>
                    <div class="list-content">
                        <div class="icon">
                            <h4><?php echo esc_html(__('Change date','shorturl-tracker')); ?></h4>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <form action="" method="POST" id="search-date">
                            <div class="input-date-search <?php echo (($activated['level'] == 'free') ? 'medium' : '' ); ?>">
                                <label for="start"><?php echo esc_html(__('From', 'shorturl-tracker')); ?>:</label>
                                <input type="date" id="start" name="from" value="<?php echo esc_html($date_start); ?>" min="01/01/2021" max="<?php echo esc_html($date_jour); ?>"  <?php echo (($activated['level'] == 'free') ? 'readonly disabled' : ''); ?>>
                            </div>
                            <div class="input-date-search <?php echo (($activated['level'] == 'free') ? 'medium' : '' ); ?>">
                                <label for="start"><?php echo esc_html(__('To', 'shorturl-tracker')); ?>:</label>
                                <input type="date" id="end" name="to" value="<?php echo esc_html($date_end); ?>" min="01/01/2021" max="<?php echo esc_html($date_jour); ?>"  <?php echo (($activated['level'] == 'free') ? 'readonly disabled' : ''); ?>>
                            </div>
                            <button type="submit" id="search-button" <?php echo (($activated['level'] == 'free') ? 'class="'.esc_attr('medium').'" disabled' : '' ); ?>><?php echo esc_html(__('Search', 'shorturl-tracker')); ?></button>
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

        </div>
    <?php else: ?>
    <div class="page-historic">
        <div class="historic-content"><div class="top-title">
                <h1 class="header-plugin"><?php echo esc_html(__('Links Historic', 'shorturl-tracker')); ?></h1>
                <?php if($activated['level'] == 'free'): ?><p class="medium-feature"><?php echo esc_html(__('Upgrade license to Medium Level to modify date', 'shorturl-tracker')); ?></p><?php endif; ?>
            </div>
            <div class="<?php echo esc_attr($class); ?>"><?php echo esc_html((($alert) ? : '')); ?></div>
            <div class="search-bar">
                <form action="" method="POST" id="form-search">
                    <div class="input-date">
                        <label for="start"><?php echo esc_html(__('From', 'shorturl-tracker')); ?>:</label>
                        <input type="date" id="start" name="from" value="<?php echo esc_html($date_start); ?>" min="01/01/2021" max="<?php echo esc_html($date_jour); ?>"  <?php echo (($activated['level'] == 'free') ? 'readonly disabled' : ''); ?>>
                    </div>
                    <div class="input-date">
                        <label for="start"><?php echo esc_html(__('To', 'shorturl-tracker')); ?>:</label>
                        <input type="date" id="end" name="to" value="<?php echo esc_html($date_end); ?>" min="01/01/2021" max="<?php echo esc_html($date_jour); ?>" <?php echo (($activated['level'] == 'free') ? 'readonly disabled' : ''); ?>>
                    </div>
                    <button type="submit" id="search-historic" <?php echo (($activated['level'] == 'free') ? 'class="medium" disabled' : '' ); ?>><img src="<?php echo esc_url(plugin_dir_url( __FILE__ ) . 'img/search-icon.png'); ?>" alt="search-button" id="search-button"></button>
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
        <?php else: ?>
            <div class="alert">
                <h1 class="header-plugin"><?php echo esc_html(__('You must Activate your License to Activate the Plugin','shorturl-tracker')); ?></h1>
                <div class="activate-bouton">
                    <a href="<?php echo esc_url($_SERVER['PHP_SELF'] . '?page=sut-settings'); ?>"><?php echo esc_html(__('Activate License','shorturl-tracker')); ?></a>
                </div>
            </div>
        <?php endif; ?>
</section>