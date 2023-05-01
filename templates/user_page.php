<div class="box">

    
    
    <?php


    if(GUI::isUserAllowedToAccessPage('admin'))
    {
        echo "<h2>".Lang::tr('admin_page')."</h2>\n";
        echo " <p>".Lang::tr('profile_page_text_linking_to_admin_page')."</p>\n";
    }

    if(Config::get('using_local_saml_dbauth'))
    {
        echo "<h2>".Lang::tr('password')."</h2>\n";
        echo <<<EOF
                   <div class="change_password">
                      <a href="#">
                         {tr:change_password}
                      </a>
                   </div>
EOF;
    }
    
    $readonly = function($info) {
        $extraclass = 'readonly';
        if( !$info['value'] ) {
            $info['value'] = '';
            $extraclass = 'unknown';
            if( $info['key'] == 'auth_secret' ) {
                $info['value'] = Lang::tr('api_secret_use_the_button_below_to_create');
            }
        }
        
        echo "<div class='$extraclass'>".Utilities::sanitizeOutput($info['value']).'</div>';
    };
    
    $infos = array(
        'preferences' => array(
            'lang' => function($info) use($readonly) {
                if(!Config::get('lang_userpref_enabled')) return;
                if($info['mode'] == 'write') {
                    $opts = array();
                    foreach(Lang::getAvailableLanguages() as $id => $language) {
                        $selected = ($id == $info['value']) ? 'selected="selected"' : '';
                        $opts[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($language['name']).'</option>';
                    }
                    
                    echo '<select name="user_lang">'.implode('', $opts).'</select>';
                } else {
                    $languages = Lang::getAvailableLanguages();
                    if(array_key_exists($info['value'], $languages))
                        $info['value'] = $languages[$info['value']]['name'];
                    
                    $readonly($info);
                }
            },
        ),
        'remote_authentication' => array(
            'auth_secret' => function($info) use($readonly) {
                if(!Config::get('auth_remote_user_enabled')) return;

                $v = Auth::user()->auth_secret_created_formatted;
                if( $v == '' ) {
                } else {
                    $tt = Lang::tr('you_generated_this_auth_secret_at')->r('datetime', $v);
                    echo "<p class='datetime'>$tt</p>";
                }
                $info['key'] = 'auth_secret';
                $readonly($info);
//                echo '<span data-info="remote_config">'.Auth::user()->remote_config.'</span>';

                echo <<<EOT
                <div>
                   <div class="api_secret_delete">
                      <a href="#">
                         <span class="fa fa-lg fa-times"></span>
                         {tr:api_secret_delete}
                      </a>
                   </div>
                   <div class="api_secret_create">
                      <a href="#">
                         <span class="fa fa-lg fa-plus"></span>
                         {tr:api_secret_recreate}
                      </a>
                   </div>
                </div>
EOT;
                
                echo <<<EOT
                   <h2>{tr:python_cli_client_heading}</h2>
                   {tr:python_cli_client_setup_information}
EOT;
            },
        ),
        'additional' => array(
            'saml_user_identification_uid' => function($info) use($readonly) {
                $readonly($info);
            },
            'created' => function($info) use($readonly) {
                $info['value'] = Utilities::formatDate($info['value']);
                $readonly($info);
            },
        ),
    );
    
    $page = (array)Config::get('user_page');
    if( $page['id'] ) {
        $page['saml_user_identification_uid'] = $page['id'];
    }
    foreach($infos as $category => $set) {

        $displayed = array();
        foreach($set as $id => $generator) {
            if(array_key_exists($id, $page)) {
                if(!$page[$id]) continue;

                $value = Auth::user()->$id;
                if( $id == 'lang' ) {
                    if( !$value ) {
                        $value = Lang::getBaseCode();
                    }
                }
                
                if($value || $id=='auth_secret') $displayed[] = array(
                    'id' => $id,
                    'mode' => $page[$id],
                    'generator' => $generator,
                    'value' => $value
                );
            }
        }
        
        if(!count($displayed)) continue;
        
        echo '<h2>'.Lang::tr('user_'.$category).'</h2>'."\n";
        echo '<p>'.Lang::tr('user_'.$category.'_body').'</p>'."\n";
        foreach($displayed as $info) {
            $tag = $info['id'];
            if( $tag == 'saml_user_identification_uid' ) {
                $tag = 'id';
            }
            echo '<div class="info" data-info="'.$tag.'">';
            echo '  <h3>'.Lang::tr('user_'.$tag).'</h3>'."\n";
            $info['generator']($info);
            echo '</div>';

            // We want the privacy to appear near the top, and we want
            // the language selector to be very high to assist folks
            // who do not speak the default language.
            if( $info['id'] == 'lang' ) {
                echo "<h2>".Lang::tr('privacy_page')."</h2>\n";
                echo " <p>".Lang::tr('profile_page_text_linking_to_privacy_page')."</p>\n";
            }
        }
    }
    
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
    

    <h2>{tr:actions}</h2>

        {tr:user_profile_send_client_logs_description_text}
    
    <div class="send_client_logs">
        <a href="#">
            <span class="fa fa-lg fa-send"></span>
            {tr:send_client_logs}
        </a>
    </div>

    <div class="export_client_logs">
        <a href="#">
            <span class="fa fa-lg fa-save"></span>
            {tr:export_client_logs}
        </a>
    </div>
    
    <div class="clear_client_logs">
        <a href="#">
            <span class="fa fa-lg fa-times"></span>
            {tr:clear_client_logs}
        </a>
    </div>
    <br/>

    <div class="clear_frequent_recipients">
        <a href="#">
            <span class="fa fa-lg fa-times"></span>
            {tr:clear_frequent_recipients}
        </a>
    </div>
    <div class="clear_user_transfer_preferences">
        <a href="#">
            <span class="fa fa-lg fa-times"></span>
            {tr:clear_user_transfer_preferences}
        </a>
    </div>
    <br/>
    
        {tr:user_profile_delete_about_description_text}
    
    <div class="delete_my_account">
        <a href="#">
            <span class="fa fa-lg fa-times"></span>
            {tr:delete_my_account}
        </a>
    </div>


    
</div>

<script type="text/javascript" src="{path:js/user_page.js}"></script>
