<?php

use ShortUrlTracker\Plugin\Sut_Byw_Settings;

require_once('class/Sut_Byw_Settings.php');
$start = new Sut_Byw_Settings();
$licenceKey = $start->sut_byw_get_autorization_value('licence_key');
$cookie_day = $start->sut_byw_get_autorization_value('cookie_day');
$param = '';
$i = 0;
$activation = $start->sut_byw_is_activated();
if ($activation['activate']) {
    $account_info = $start->sut_byw_get_account_detail();
}
$url = esc_url($_SERVER['HTTP_HOST']);
$delete = __('Delete','shorturl-tracker');
if (isset( $_POST ) && count($_POST) != 0) {
    if (isset($_POST['delete_licence']) ) {
        if ($start->sut_byw_deactivate_licence(SUT_BYW_TOKEN)) {
            $start->sut_byw_delete_Licence('licence_key');
        }
    } else {
        foreach($_POST as $name => $content) {
            $start->sut_byw_insert_licence_key(sanitize_text_field(htmlentities(trim($name))), sanitize_text_field(htmlentities(trim($content))));
        }
    }
    $start->sut_byw_return_page();
}
?>
<section class="ga-tracking-plugin">
    <h1 class="header-plugin"><?php echo esc_html(__('Settings','shorturl-tracker')); ?></h1>
    <div class="parameter">
        <div class="settings-block">
            <div class="sub-title">
                <div class="icon-settings">
                    <svg id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 24"  xml:space="preserve">
                    <style type="text/css">
                        .st0{fill:none;stroke:#141211;stroke-linecap:round;stroke-linejoin:round;}
                    </style>
                        <path class="st0" d="M20,21v-2c0-2.2-1.8-4-4-4H8c-2.2,0-4,1.8-4,4v2"/>
                        <circle class="st0" cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <h4><?php echo esc_html(__('Account','shorturl-tracker')); ?></h4>
            </div>
            <form method="POST" class="licence-form">
                <label>
                    <div class="icon-settings">
                        <svg id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 24" xml:space="preserve">
                            <style type="text/css">
                                .st0{fill:none;stroke:#141211;stroke-linecap:round;stroke-linejoin:round;}
                            </style>
                            <path class="st0" d="M21,2l-2,2 M11.4,11.6c2.2,2.1,2.2,5.6,0.1,7.8s-5.6,2.2-7.8,0.1c0,0,0,0-0.1-0.1c-2.1-2.2-2-5.7,0.1-7.8
                                C5.9,9.6,9.3,9.6,11.4,11.6L11.4,11.6z M11.4,11.6l4.1-4.1 M15.5,7.5l3,3L22,7l-3-3 M15.5,7.5L19,4"/>
                            </svg>
                    </div>
                    <h4><?php echo esc_html(__('License Key','shorturl-tracker')); ?> <?php echo ($activation['activate'] ? '(Active)' : ''); ?></h4>
                </label>
                <div class="licence-block">
                    <input type="text" name="licence_key" value="<?php echo esc_attr($start->sut_byw_mask_token($licenceKey)) ?>" autocomplete="off" placeholder="<?php echo esc_html(__('License Key','shorturl-tracker')); ?>">
                    <?php if($activation['activate']): ?>
                        <div class="action-block">
                            <div class="deleted">
                                <input type="submit" value="<?php echo esc_attr($delete); ?>" name="delete_licence" class="delete-icon-button">
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="action-block">
                            <div class="activation">
                                <input type="submit" value="Activate">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if(!$activation['activate']): ?>
                    <h4 class="no-licence"><?php echo wp_kses(__('No license ? Click <a href="https://buildyourweb.fr/plugin-shorturl-tracker/" target="_blank">here</a>','shorturl-tracker'), $start->sut_byw_get_allowed_tags()); ?></h4>
                <?php endif; ?>
            </form>
            <?php if($activation['activate']): ?>
                <form method="POST" class="configuration-form">
                    <label>
                        <div class="icon-settings">
                            <svg id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 24" overflow="visible" xml:space="preserve">
                        <circle fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="12" r="10"/>
                                <rect x="9" y="9" display="none" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" width="6" height="6"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="12" cy="4.4" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="11.3" cy="10.2" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="12.3" cy="14.8" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="6.8" cy="7.7" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="15.4" cy="7.6" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="18.1" cy="12.9" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="13.7" cy="18.9" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="5.1" cy="13.5" r="0.4"/>
                                <circle stroke="#000000" stroke-miterlimit="10" cx="7.6" cy="17.2" r="0.4"/>
                        </svg>
                        </div>
                        <h4 class="<?php echo esc_attr(((isset($account_info) && isset($account_info->license_level) && $account_info->license_level != 'premium') ? 'premium' : '' )); ?>"><?php echo esc_html(__('Cookie Lifetime','shorturl-tracker')); ?> (Min: 1 | Max: 30 - <?php echo esc_html(__('in days', 'shorturl-tracker')); ?>)</h4>
                    </label>
                    <div class="cookie-part">
                        <input type="number" name="cookie_day" value="<?php echo esc_html(((isset($account_info) && isset($account_info->license_level) ? (($account_info->license_level == 'premium') ? $cookie_day : 7) : 7))); ?>" autocomplete="off" min="1" max="30" <?php echo ((isset($account_info) && isset($account_info->license_level) && $account_info->license_level != 'premium') ? 'class="premium" readonly' : '' ); ?>>
                        <input type="submit" value="<?php echo esc_html(((isset($account_info) && isset($account_info->license_level) ? __('Save','shorturl-tracker') : 'Error'))); ?>"  <?php echo ((isset($account_info) && isset($account_info->license_level) && $account_info->license_level != 'premium') ? 'class="premium" disabled' : '' ); ?>>
                    </div>
                </form>
                <div class="sub-title">
                    <div class="icon-settings">
                        <svg id="Calque_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 24" overflow="visible" xml:space="preserve">
                    <circle fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" cx="12" cy="12" r="10"/>
                            <line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x1="12" y1="16" x2="12" y2="12"/>
                            <line fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" x1="12" y1="8" x2="12" y2="8"/>
                    </svg>
                    </div>
                    <h4><?php echo esc_html(__('Informations','shorturl-tracker')); ?></h4>
                </div>
                <div class="section-info">
                    <div class="license-info">
                        <ul>
                            <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3c434a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg> <?php echo esc_html(__('License Level','shorturl-tracker')); ?> : <span class="level"><?php echo esc_html(((isset($account_info) && isset($account_info->license_level) ) ? ucfirst($account_info->license_level) : 'Error' )); ?></span><?php echo ((isset($account_info) && isset($account_info->license_level) && $account_info->license_level != 'premium') ? '<span class="upgrade-link"><a href="'. esc_url('https://buildyourweb.fr/plugin-shorturl-tracker/').'" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3c434a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-zap"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>'.esc_html(__('Upgrade', 'shorturl-tracker')).'</a></span>' : '');?></li>
                            <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3c434a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-calendar"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> <?php echo esc_html(__('Total Link Created','shorturl-tracker')); ?> : <span class="level"><?php echo esc_html(((isset($account_info) && isset($account_info->license_level) ) ? $account_info->license_call : 'Error' )); ?></span></li>
                            <?php if(isset($account_info) && isset($account_info->license_level) && $account_info->license_level != 'premium'): ?>
                                <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3c434a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg><?php echo esc_html(__('Remaining Links','shorturl-tracker')); ?> : <span class="level"><?php echo esc_html($account_info->monthly_call->remaining); ?>/<?php echo esc_html($account_info->monthly_call->remaining + $account_info->monthly_call->count); ?></span></li>
                            <?php elseif(isset($account_info) && isset($account_info->license_level) && $account_info->license_level == 'premium') : ?>
                                <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3c434a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg><?php echo esc_html(__('Remaining Links','shorturl-tracker')); ?> : <span class="level"><?php echo esc_html(__('Unlimited', 'shorturl-tracker')); ?></span></li>
                            <?php else: ?>
                                <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3c434a" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-pie-chart"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg><?php echo esc_html(__('Remaining Links','shorturl-tracker')); ?> : <span class="level"><?php echo esc_html(__('Error', 'shorturl-tracker')); ?></span></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>