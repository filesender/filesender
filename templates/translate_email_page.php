<div class="box">
<?php
    if(!array_key_exists('token', $_REQUEST))
        throw new TokenIsMissingException();
    
    $token = $_REQUEST['token'];
    if(!Utilities::isValidUID($token))
        throw new TokenHasBadFormatException($token);
    
    $lang = array_key_exists('lang', $_REQUEST) ? $_REQUEST['lang'] : null;
    
    $available = Lang::getAvailableLanguages();
    
    if(!array_key_exists($lang, $available))
        $lang = Lang::getCode();
    
    $url = '?s=translate_email&amp;token='.$token.'&amp;lang=';
    
    if(count($available) > 1) {
        echo '<div class="languages">';
        echo '<div class="buttons">';
        
        echo '<span class="spaced">'.Lang::tr('translate_to').'</span>';
        
        foreach($available as $id => $dfn) {
            if($id == $lang) {
                echo '<span class="spaced selected">'.Utilities::sanitizeOutput($dfn['name']).'</span>';
            } else {
                echo '<a href="'.$url.$id.'">'.Utilities::sanitizeOutput($dfn['name']).'</a>';
            }
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    $translatable = TranslatableEmail::fromToken($token);
    
    $translation = $translatable->translate($lang);
    
    /*
     * Do not call Template::sanitizeOutput on email contents after that because
     * TranslatableEmail::translate calls Translation::replace which itself calls
     * Utilities::sanitizeOutput, use Template::sanitize instead !
     */
    
    $subject = array_filter($translation->subject->out());
    
    ?>
    
    <hr />
    
    <dl>
        <dt data-property="subject">{tr:subject} :</dt>
        <dd data-property="subject"><?php echo Template::sanitizeOutput(array_pop($subject)) ?></dd>
        
        <dt data-property="message">{tr:message}</dt>
        <dd data-property="message"><?php echo Template::sanitize($translation->html) ?></dd>
    </dl>
    
    <script type="text/javascript" src="{path:js/translate_email_page.js}"></script>
</div>
