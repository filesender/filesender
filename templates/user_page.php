<?php

function isChecked( $v ) {
    return $v ? 'checked="checked"' : '';
}

$user = Auth::user();

?>
<div class="fs-settings">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="fs-settings__header">
                    <h1>{tr:user_page}</h1>
                </div>
            </div>
        </div>

        <?php
        if(GUI::isUserAllowedToAccessPage('admin'))
        {
            echo "<div class='row'>
                    <div class='col-12 col-sm-12 col-md-12 col-lg-6'>
                        <div class='fs-settings__admin'>
                            <h2>".Lang::tr('admin_page')."</h2>
                            <p>".Lang::tr('profile_page_text_linking_to_admin_page')."</p>
                        </div>
                    </div>
                </div>";
        }

        $page = (array)Config::get('user_page');

        if( $page['id'] ) {
            $page['saml_user_identification_uid'] = $page['id'];
        }

        ?>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <div class="fs-settings__preferences">
                    <h2>{tr:user_preferences}</h2>

                    <?php
                    if (Config::get('lang_userpref_enabled')) {
                        $id = 'lang';

                        if($page[$id]) {
                            $value = Auth::user()->$id;
                            if(!$value) {
                                $value = Lang::getBaseCode();
                            }

                            if($value) {
                                $mode = $page[$id];

                                if($mode == 'write') {
                                    $opts = array();
                                    foreach(Lang::getAvailableLanguages() as $id => $language) {
                                        $selected = ($id == $value) ? 'selected="selected"' : '';
                                        $opts[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($language['name']).'</option>';
                                    }

                                    echo "<div class='fs-select'>";
                                    echo "<label for='user_lang'>{tr:language}</label>";
                                    echo '<select id="user_lang" name="user_lang">'.implode('', $opts).'</select>';
                                    echo "</div>";
                                } else {
                                    $languages = Lang::getAvailableLanguages();
                                    if(array_key_exists($value, $languages)) {
                                        $value = $languages[$value]['name'];
                                    }

                                    echo "<div class='fs-input-group fs-input-group--auto fs-input-group--center'>";
                                    echo "<label for='lang'>{tr:language}</label>";
                                    echo "<input id='lang' type='text' value='".$value."' disabled>";
                                    echo "</div>";
                                }
                            }
                        }
                    }
                    ?>

                    <div class='fs-select pt-3'>
                        <label for='user_theme'>{tr:theme}</label>
                        <select id="user_theme" name="user_theme">
                            <option value="device" selected>{tr:device}</option>
                            <option value="default">{tr:light}</option>
                            <option value="dark">{tr:dark}</option>
                        </select>
                        <small>{tr:theme_info}</small>
                    </div>

                    <div class="fs-switch fs-switch--small">
                        <input id="previous-settings" type="checkbox" name="save_transfer_preferences"  <?php echo isChecked($user->save_transfer_preferences); ?> />
                        <label for="previous-settings">
                            {tr:previous_settings}
                        </label>
                    </div>

                    <div class="fs-switch fs-switch--small">
                        <input id="save-recipients-emails" name="save_frequent_email_address" type="checkbox"  <?php echo isChecked($user->save_frequent_email_address); ?> />
                        <label for="save-recipients-emails">
                            {tr:save_recipients_emails}
                        </label>
                    </div>

                    <button type="submit" id="save-preferences" class="fs-button">
                        <i class="fa fa-save"></i>
                        <span>
                            {tr:save_preferences}
                        </span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-5 offset-xl-1">
                <div class="fs-settings__account-info">
                    <h2>{tr:account_information}</h2>

                    <?php

                    $value = Auth::user()->email_addresses;
                    $email = $value[0];

                    echo "<div class='fs-info'>";
                    echo "<strong>{tr:email_address}:</strong>";
                    echo "<span>".$email."</span>";
                    echo "</div>";

                    $value = Auth::user()->saml_user_identification_uid;

                    echo "<div class='fs-info'>";
                    echo "<strong>{tr:user_id}:</strong>";
                    echo "<span>".$value."</span>";
                    echo "</div>";

                    $value = Auth::user()->created;

                    echo "<div class='fs-info'>";
                    echo "<strong>{tr:user_created}:</strong>";
                    echo "<span>".Utilities::formatDate($value)."</span>";
                    echo "</div>";

                    $value = Auth::user()->quota;

                    echo "<div class='fs-info'>";
                    echo "<strong>{tr:current_quota_storage}:</strong>";
                    echo "<span>".$value."</span>";
                    echo "</div>";
                    ?>

                    <div class="fs-settings__account-actions">
                        <?php
                        if(Config::get('using_local_saml_dbauth'))
                        {

                            echo "<button type='button' id='change_password' class='fs-button'>";
                            echo "<i class='fa fa-key'></i>";
                            echo "<span>{tr:change_password}</span>";
                            echo "</button>";
                        }
                        ?>

                        <?php
                        if (Auth::isAuthenticated() && Auth::isSP())
                        {
                            $faicon = 'fa-sign-out';
                            $icon = '<i class="fa '.$faicon.'"></i> ';

                            $url = AuthSP::logoffURL();
                            if(Config::get('auth_sp_type') == "saml") {
                                $link = Utilities::sanitizeOutput($url);
                                $txt = Lang::tr('logoff');
                                echo <<<EOT
                                  <form action="$link" method="post" >
                                    <button type="submit" class="fs-button fs-button--danger">
                                        {$icon}
                                        <span>$txt</span>
                                    </button>
                                  </form>
                                EOT;
                            } else if($url) {
                                echo '<a class="fs-button fs-button--danger" href="'.Utilities::sanitizeOutput($url).'">'.$icon.Lang::tr('logoff').'</a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <div class="fs-settings__privacy">
                    <h2><?php echo Lang::tr('privacy_page'); ?></h2>

                    <p><?php echo Lang::tr('profile_page_text_linking_to_privacy_page'); ?></p>
                </div>
            </div>

        <div class="row">
            <div class="col-12">
                <div class="fs-settings__actions">
                    <h2>{tr:actions}</h2>

                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__saved-info">
                                <h3>{tr:saved_information}</h3>

                                <ul class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="clear_user_transfer_preferences" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>{tr:clear_transfer_settings}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:clear_stored_transfer_settings}
                                        </span>
                                    </li>
                                </ul>

                                <ul class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="clear_frequent_recipients" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>{tr:clear_recipients_emails}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:clear_stores_recipients_emails}
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div class="fs-settings__general">
                                <h3>{tr:general}</h3>

                                <button type="button" id="delete_my_account" class="fs-button fs-button--danger">
                                    <i class="fa fa-close"></i>
                                    <span>{tr:delete_my_account}</span>
                                </button>

                                {tr:user_profile_delete_about_description_text}
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__logs">
                                <h3>{tr:logs}</h3>

                                <ul class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="send_client_logs" class="fs-button">
                                            <i class="fa fa-arrow-right"></i>
                                            <span>{tr:send_client_logs}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:send_logs_to_server}
                                        </span>
                                    </li>
                                </ul>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="export_client_logs" class="fs-button">
                                            <i class="fa fa-download"></i>
                                            <span>{tr:export_logs}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:export_logs_to_file}
                                        </span>
                                    </li>
                                </div>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="clear_client_logs" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>{tr:clear_logs}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:clear_client_logs}
                                        </span>
                                    </li>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                <div class="fs-settings__about">
                    <h2>{tr:about_title}</h2>

                    <p>
                        {tr:agree_text}
                    </p>
                    <p>
                        {tr:about_more_info}
                    </p>
                </div>
            </div>
        </div>

        <?php
        if (Config::get('auth_remote_user_enabled')) {

        ?>
        <div class="row">
            <div class="col-12">
                <div class="fs-settings__remote-authentication">
                    <h2>{tr:user_remote_authentication}</h2>

                    <?php
                        $tt = 0;
                        $id = 'auth_secret';

                        if($page[$id]) {
                            $value = Auth::user()->$id;

                            $v = Auth::user()->auth_secret_created_formatted;
                            if( $v == '' ) {
                            } else {
                                $tt = Lang::tr('you_generated_this_auth_secret_at')->r('datetime', $v);
                            }
                            $info['key'] = 'auth_secret';
                            //                echo '<span data-info="remote_config">'.Auth::user()->remote_config.'</span>';
                        }

                    ?>
                    <p>
                        {tr:user_remote_authentication_body}
                    </p>

                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__api-secret">
                                <h3>{tr:api_secret}</h3>

                                <?php if($tt) { echo "<p>$tt</p>"; } ?>

                                <?php
                                if ($value) {
                                    echo <<<EOT
                                    <div class='fs-copy'>
                                        <span>$value</span>
                                        <button id='copy-api-secret' type='button' class='fs-button'>
                                            <i class='fa fa-copy'></i>
                                            {tr:copy}
                                        </button>
                                    </div>
                                    EOT;

                                }
                                ?>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="api_secret_create" class="fs-button">
                                            <i class="fa fa-plus"></i>
                                            <span>{tr:new_api_secret}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:generate_new_api_secret}
                                        </span>
                                    </li>
                                </div>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="api_secret_delete" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>{tr:clear_api_secret}</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            {tr:delete_current_api_secret}
                                        </span>
                                    </li>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__cli">
                                <h3><?php echo Lang::tr('python_cli_client_heading'); ?></h3>

                                {tr:python_cli_client_setup_information}

                                <div class="fs-copy">
                                    <span>python3 filesender.py -r person-to-send-to@emailserver.edu research-data-file.txt</span>

                                    <button id="copy-python-command" type="button" class="fs-button">
                                        <i class="fa fa-copy"></i>
                                        {tr:copy}
                                    </button>
                                </div>

                                <ul class="fs-list fs-list--inline">
                                    <li>
                                        <a href="https://raw.githubusercontent.com/filesender/filesender/master3/scripts/client/filesender.py">
                                            {tr:download_python_cli}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://sam/filesender/rest.php/user/@me/filesender-python-client-configuration-file" download="filesender.py.ini" >
                                            {tr:download_python_cli_configuration}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <?php
        }
        ?>
    </div>
</div>

<?php

if(
    array_key_exists('remote_auth_sync_request', $_REQUEST) &&
    Config::get('auth_remote_user_enabled') &&
    Auth::user()->auth_secret
) {
    $code = substr(Utilities::generateUID(), -6);

    $_SESSION['remote_auth_sync_request'] = array(
        'code' => $code,
        'expires' => time() + 60
    );

    echo '<span data-remote-auth-sync-request="'.$code.'">'.Utilities::sanitizeOutput($_REQUEST['remote_auth_sync_request']).'</span>';
}

?>


<script type="text/javascript" src="{path:js/user_page.js}"></script>
