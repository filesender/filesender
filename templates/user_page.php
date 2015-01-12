<div class="box">
    <h1>{tr:user_page}</h1>
    
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
        'additionnal' => array(
            'created' => function($info) use($readonly) {
                $info['value'] = Utilities::formatDate($info['value']);
                $readonly($info);
            },
        ),
    );
    
    $page = (array)Config::get('user_page');
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
        
        foreach($displayed as $info) {
            echo '<div class="info" data-info="'.$info['id'].'">';
            echo '  <h3>'.Lang::tr('user_'.$info['id']).'</h3>'."\n";
            $info['generator']($info);
            echo '</div>';
        }
    }
        
    ?>
</div>

<script type="text/javascript" src="{path:js/user_page.js}"></script>
