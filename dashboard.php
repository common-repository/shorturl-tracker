<?php
require_once('class/Sut_Byw_Dashboard.php');
wp_create_nonce('wp_rest');
ob_start();
$date_end = $date_day = date('Y-m-d');
$date_start = date('Y-m-d', strtotime('-1 month'));
$start = new \ShortUrlTracker\Plugin\Sut_Byw_Dashboard();
$activated = $start->sut_byw_is_activated();
if ($activated['activate']) {
    $start->sut_byw_dashboard_js();
    if (isset($_POST) && isset($_POST['from']) && isset($_POST['to'])) {
        if ($_POST['from'] <= $_POST['to']) {
            $date_end = sanitize_text_field(htmlentities($_POST['to']));
            $date_start = sanitize_text_field(htmlentities($_POST['from']));
            $data = $start->sut_byw_call_status($date_start, $date_end);
            $start->sut_byw_dashboard_value_js((array)$data->day_by_day, $date_start, $date_end);
        } else {
            $date_end = date('Y-m-d');
            $date_start = date('Y-m-d', strtotime('-1 month'));
        }
    } else {
        $date_end = date('Y-m-d');
        $date_start = date('Y-m-d', strtotime('-1 month'));
        $data = $start->sut_byw_call_status($date_start, $date_end);
        $start->sut_byw_dashboard_value_js((array)$data->day_by_day, $date_start, $date_end);
    }
}
?>
<section class="ga-tracking-plugin">
    <?php if ($activated['activate']): ?>
        <div class="dashboard-content">
            <div class="top-dashboard">
                <div class="top-title">
                    <div class="back-link">
                        <a href="<?php echo ((isset($_SERVER['HTTP_REFERER'])) ? esc_url($_SERVER['HTTP_REFERER']) : esc_url($_SERVER['PHP_SELF'] . '?page=sut-home')); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            <p><?php echo esc_html(__('Back', 'shorturl-tracker')); ?></p>
                        </a>
                    </div>
                    <h1 class="header-plugin"><?php echo esc_html(__('Dashboard ShortUrl Tracker', 'shorturl-tracker')); ?></h1>
                    <h4 class="header-plugin"><?php echo esc_html(__('Performance of all the links that you created','shorturl-tracker')); ?></h4>
                    <?php if($activated['level'] == 'free'): ?><p class="medium-feature"><?php echo esc_html(__('Upgrade license to Medium Level to modify date', 'shorturl-tracker')); ?></p><?php endif; ?>
                </div>
                <div class="top-content">
                    <ul class="list-elements">
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Created Links', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                </div>
                                <h2><?php echo esc_html(((isset($data->created_link)) ? $data->created_link : '')); ?></h2>
                                <h4><?php echo esc_html(__('Total', 'shorturl-tracker')) ?></h4>
                            </div>
                        </li>
                        <li>
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Visitors', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mouse-pointer"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="M13 13l6 6"/></svg>
                                </div>
                                <h2><?php echo esc_html(((isset($data->total_click)) ? $data->total_click : '')); ?> / <?php echo esc_html(((isset($data->unique_click)) ? $data->unique_click : '')); ?></h2>
                                <h4><?php echo esc_html(__('Total / Single', 'shorturl-tracker')) ?></h4>
                            </div>
                        </li>
                        <li class="<?php echo esc_html((($activated['level'] != 'premium') ? 'upgrade-plugin' : '')); ?>">
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Orders', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                </div>
                                <?php if($activated['level'] == 'premium'): ?>
                                    <h2><?php echo esc_html(((isset($data->total_purchase)) ? $data->total_purchase : '')); ?> / <?php echo esc_html(((isset($data->total_amount)) ? $data->total_amount : '')); ?>€</h2>
                                    <h4><?php echo esc_html(__('Number / Amount', 'shorturl-tracker')) ?></h4>
                                <?php else: ?>
                                    <h2><?php echo esc_html(__('Premium Feature','shorturl-tracker'))?></h2>
                                    <h4><a href="<?php echo esc_url($_SERVER['PHP_SELF']) . '?page=sut-settings'; ?>"><?php echo __('Upgrade License','shorturl-tracker'); ?></a></h4>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="<?php echo esc_attr((($activated['level'] == 'free') ? 'upgrade-plugin' : '')); ?>">
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Device', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-monitor"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                </div>
                                <?php if($activated['level'] != 'free'): ?>
                                    <h2><?php echo ((isset($data->device->desktop)) ? $data->device->desktop : ''); ?> / <?php echo esc_html(((isset($data->device->mobile)) ? $data->device->mobile : '')); ?></h2>
                                    <h4><?php echo esc_html(__('Desktop / Mobile', 'shorturl-tracker')) ?></h4>
                                <?php else: ?>
                                    <h2><?php echo esc_html(__('Medium Feature','shorturl-tracker'))?></h2>
                                    <h4><a href="<?php echo esc_url($_SERVER['PHP_SELF']) . '?page=sut-settings'; ?>"><?php echo esc_html(__('Upgrade License','shorturl-tracker')); ?></a></h4>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="<?php echo esc_attr((($activated['level'] == 'free') ? 'upgrade-plugin' : '')); ?>">
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Top Link', 'shorturl-tracker')) ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                </div>
                                <?php if($activated['level'] != 'free'): ?>
                                    <h2><?php echo esc_html(((isset($data->top_link) && isset($data->top_link->totalclick)) ? $data->top_link->totalclick : '')); ?> Click<?php echo esc_html(((isset($data->top_link->totalclick) && $data->top_link->totalclick > 1) ? esc_html('s'):'')); ?></h2>
                                    <h4><a href="<?php echo ((isset($data->top_link->shortcode)) ? esc_url(get_admin_url() . 'admin.php?page=sut-historic&link_id=' . $data->top_link->shortcode . '&startdate=' . $date_start . '&enddate=' . $date_end) : '#'); ?>"><?php echo esc_html(((isset($data->top_link->shortcode)) ? $data->top_link->shortcode : 'error')); ?></a></h4>
                                <?php else: ?>
                                    <h2><?php echo esc_html(__('Medium Feature','shorturl-tracker'))?></h2>
                                    <h4><a href="<?php echo esc_url($_SERVER['PHP_SELF']) . '?page=sut-settings'; ?>"><?php echo esc_html(__('Upgrade License','shorturl-tracker')); ?></a></h4>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li class="<?php echo esc_attr((($activated['level'] == 'free') ? 'upgrade-plugin' : '')); ?>">
                            <div class="list-content">
                                <div class="icon">
                                    <h4><?php echo esc_html(__('Change date','shorturl-tracker')); ?></h4>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                                <form action="" method="POST" id="search-date">
                                    <div class="input-date-search <?php echo esc_attr(((($activated['level'] == 'free') ? 'medium' : '' ))); ?>">
                                        <label for="start"><?php echo esc_html(__('From', 'shorturl-tracker')); ?>:</label>
                                        <input type="date" id="start" name="from" value="<?php echo esc_html($date_start); ?>" min="01/01/2021" max="<?php echo esc_html($date_day); ?>"  <?php echo esc_attr((($activated['level'] == 'free') ? esc_html('readonly disabled') : '')); ?>>
                                    </div>
                                    <div class="input-date-search <?php echo (($activated['level'] == 'free') ? 'medium' : '' ); ?>">
                                        <label for="start"><?php echo esc_html(__('To', 'shorturl-tracker')); ?>:</label>
                                        <input type="date" id="end" name="to" value="<?php echo esc_html($date_end); ?>" min="01/01/2021" max="<?php echo esc_html($date_day); ?>"  <?php echo esc_attr((($activated['level'] == 'free') ? esc_html('readonly disabled') : '')); ?>>
                                    </div>
                                    <button type="submit" id="search-button" <?php echo (($activated['level'] == 'free') ? 'class="'.esc_attr('medium').'" disabled' : '' ); ?>><?php echo esc_html(__('Search', 'shorturl-tracker')); ?></button>
                                </form>
                            </div>
                        </li>
                    </ul>
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
        </div>
    <?php else: ?>
        <div class="alert">
            <h1 class="header-plugin"><?php echo esc_html(__('You must Activate your License to Activate the Plugin','shorturl-tracker')); ?></h1>
            <div class="activate-bouton">
                <a href="<?php echo esc_url($_SERVER['PHP_SELF']) . '?page=sut-settings'; ?>"><?php echo esc_html(__('Activate License','shorturl-tracker')); ?></a>
            </div>
        </div>
    <?php endif; ?>
</section>