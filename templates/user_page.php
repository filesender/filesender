
<div class="fs-settings">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="fs-settings__header">
                    <h1>Account settings</h1>
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
                    <h2>Preferences</h2>

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
                                    echo "<label for='user_lang'>Language</label>";
                                    echo '<select id="user_lang" name="user_lang">'.implode('', $opts).'</select>';
                                    echo "</div>";
                                } else {
                                    $languages = Lang::getAvailableLanguages();
                                    if(array_key_exists($value, $languages)) {
                                        $value = $languages[$value]['name'];
                                    }

                                    echo "<div class='fs-input-group fs-input-group--auto fs-input-group--center'>";
                                    echo "<label for='lang'>Language</label>";
                                    echo "<input id='lang' type='text' value='".$value."' disabled>";
                                    echo "</div>";
                                }
                            }
                        }
                    }
                    ?>

                    <div class="fs-switch fs-switch--small">
                        <input id="previous-settings" type="checkbox" />
                        <label for="previous-settings">
                            Use transfer settings from previous upload as default settings for next transfer.
                        </label>
                    </div>

                    <div class="fs-switch fs-switch--small">
                        <input id="save-recipients-emails" type="checkbox" />
                        <label for="save-recipients-emails">
                            Save email recipients from past use (used to automatically complete email fields).
                        </label>
                    </div>

                    <button type="submit" id="save-preferences" class="fs-button">
                        <i class="fa fa-save"></i>
                        <span>Save preferences</span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-sm-12 col-md-12 col-lg-6 col-xl-5 offset-xl-1">
                <div class="fs-settings__account-info">
                    <h2>Account information</h2>

                    <?php

                    $id = 'email_addresses';
                    $value = Auth::user()->$id;
                    $email = $value[0];

                    echo "<div class='fs-info'>";
                    echo "<strong>Email address:</strong>";
                    echo "<span>".$email."</span>";
                    echo "</div>";

                    $id = 'saml_user_identification_uid';
                    $value = Auth::user()->$id;

                    echo "<div class='fs-info'>";
                    echo "<strong>Identifiant:</strong>";
                    echo "<span>".$value."</span>";
                    echo "</div>";

                    $id = 'created';
                    $value = Auth::user()->$id;

                    echo "<div class='fs-info'>";
                    echo "<strong>First login:</strong>";
                    echo "<span>".Utilities::formatDate($value)."</span>";
                    echo "</div>";

                    $id = 'quota';
                    $value = Auth::user()->$id;

                    echo "<div class='fs-info'>";
                    echo "<strong>Current quota storage:</strong>";
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
                                        ${icon}
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
                                <h3>Saved information</h3>

                                <ul class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="clear_user_transfer_preferences" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>Clear transfer settings</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Clear stored transfer settings from last upload.
                                        </span>
                                    </li>
                                </ul>

                                <ul class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="clear_frequent_recipients" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>Clear email recipients</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Clear stored email recipients from past use (used to automatically complete email fields).
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div class="fs-settings__general">
                                <h3>General</h3>

                                <button type="button" id="delete_my_account" class="fs-button fs-button--danger">
                                    <i class="fa fa-close"></i>
                                    <span>{tr:delete_my_account}</span>
                                </button>

                                {tr:user_profile_delete_about_description_text}
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__logs">
                                <h3>Logs</h3>

                                <ul class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="send_client_logs" class="fs-button">
                                            <i class="fa fa-arrow-right"></i>
                                            <span>Send logs</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Send the logs from your web browser to the server.
                                        </span>
                                    </li>
                                </ul>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="export_client_logs" class="fs-button">
                                            <i class="fa fa-download"></i>
                                            <span>Export logs</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Export client logs to a file.
                                        </span>
                                    </li>
                                </div>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="clear_client_logs" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>Clear logs</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Clear stored transfer settings from last upload.
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
                    <h2>About FileSender</h2>

                    <p>
                        By using FileSender, you automatically agree with the <a href="?s=terms">general terms and conditions</a> of FileSender.
                    </p>
                    <p>
                        For more information about FileSender, go to the <a href="?s=about">About FileSender page</a>.
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
                    <h2>Remote authentication</h2>

        <?php
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
                        Use this information to authenticate with the system if you are developing REST clients.
                    </p>

                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__api-secret">
                                <h3>API secret</h3>

                                <?php echo "<p>$tt</p>"; ?>

                                <?php
                                if ($value) {
                                    echo <<<EOT
                                    <div class='fs-copy'>
                                        <span>$value</span>
                                        <button id='copy-api-secret' type='button' class='fs-button'>
                                            <i class='fa fa-copy'></i>
                                            Copy
                                        </button>
                                    </div>
                                    EOT;

                                }
                                ?>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="api_secret_create" class="fs-button">
                                            <i class="fa fa-plus"></i>
                                            <span>New API secret</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Generate a new API secret.
                                        </span>
                                    </li>
                                </div>

                                <div class="fs-list fs-list--inline fs-list--mobile-reverse">
                                    <li>
                                        <button type="button" id="api_secret_delete" class="fs-button fs-button--danger">
                                            <i class="fa fa-close"></i>
                                            <span>Clear API secret</span>
                                        </button>
                                    </li>
                                    <li>
                                        <span>
                                            Delete the current API secret.
                                        </span>
                                    </li>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                            <div class="fs-settings__cli">
                                <h3><?php echo Lang::tr('python_cli_client_heading'); ?></h3>

                                <p>
                                    To use the Python CLI Client configuration, create a directory ~/.filesender and copy the configuration file filesender.py.ini to the directory ~/.filesender. The configuration file is optional but is recommended as it means you do not always have to specify all parameters on the command line. The python CLI Client can be downloaded to any location and requires Python version 3 to execute.
                                </p>
                                <p>
                                    With the configuration file in place you can upload a file using:
                                </p>

                                <div class="fs-copy">
                                    <span>python3 filesender.py -r person-to-send-to@emailserver.edu research-data-file.txt</span>

                                    <button id="copy-python-command" type="button" class="fs-button">
                                        <i class="fa fa-copy"></i>
                                        Copy
                                    </button>
                                </div>

                                <ul class="fs-list fs-list--inline">
                                    <li>
                                        <a href="">
                                            Download Python CLI Client
                                        </a>
                                    </li>
                                    <li>
                                        <a href="">
                                            Download Python CLI Client configuration
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
