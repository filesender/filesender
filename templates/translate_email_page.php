<div class="box">
<?php
    if(!Config::get('lang_selector_enabled')) {
        $opts = array();
        $code = Lang::getCode();
        foreach(Lang::getAvailableLanguages() as $id => $dfn) {
            $selected = ($id == $code) ? 'selected="selected"' : '';
            $opts[] = '<option value="'.$id.'" '.$selected.'>'.Utilities::sanitizeOutput($dfn['name']).'</option>';
        }
        
        echo '<div class="buttons"><select id="language_selector">'.implode('', $opts).'</select></div>';
    }
    
    if(!array_key_exists('token', $_REQUEST))
        throw new TokenIsMissingException();
    
    $token = $_REQUEST['token'];
    if(!Utilities::isValidUID($token))
        throw new TokenHasBadFormatException($token);
    
    $translatable = TranslatableEmail::fromToken($token);
    
    $translation = $translatable->translate();
    
    /*
     * Do not call Template::sanitizeOutput on email contents after that because
     * TranslatableEmail::translate calls Translation::replace which itself calls
     * Utilities::sanitizeOutput, use Template::sanitize instead !
     */
    
    $subject = array_filter($translation->subject->out());
    
    ?>
    
    <dl>
        <dt data-property="subject">{tr:subject} :</dt>
        <dd data-property="subject"><?php echo Template::sanitize(array_pop($subject)) ?></dd>
        
        <dt data-property="message">{tr:message}</dt>
        <dd data-property="message"><?php echo Template::sanitize($translation->html) ?></dd>
    </dl>
</div>
