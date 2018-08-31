<div class="box">

    <h2>Actions</h2>

        {tr:user_profile_delete_about_description_text}
    
    <div class="delete_my_account">
        <a href="#">
            <span class="fa fa-lg fa-times"></span>
            {tr:delete_my_account}
        </a>
    </div>
    
    <?php
    
    $readonly = function($info) {
        echo '<div class="readonly">'.Utilities::sanitizeOutput($info['value']).'</div>';
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
                
                $readonly($info);
                echo '<span data-info="remote_config">'.Auth::user()->remote_config.'</span>';
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
                if($value) $displayed[] = array(
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
</div>

<script type="text/javascript" src="{path:js/user_page.js}"></script>
