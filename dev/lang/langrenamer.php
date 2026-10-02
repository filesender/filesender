<?php

die('Already done !');

$renamed = array(
    '_DP_closeText' => 'dp_close_text',
    '_DP_currentText' => 'dp_current_text',
    '_DP_dayNames' => 'dp_day_names',
    '_DP_dayNamesMin' => 'dp_day_names_min',
    '_DP_dayNamesShort' => 'dp_day_names_short',
    '_DP_firstDay' => 'dp_first_day',
    '_DP_isRTL' => 'dp_is_rtl',
    '_DP_monthNames' => 'dp_month_names',
    '_DP_monthNamesShort' => 'dp_month_names_short',
    '_DP_nextText' => 'dp_next_text',
    '_DP_prevText' => 'dp_prev_text',
    '_DP_showMonthAfterYear' => 'dp_show_month_after_year',
    '_DP_weekHeader' => 'dp_week_header',
    '_DP_yearSuffix' => 'dp_year_suffix',
    '_DP_dateFormat' => 'dp_date_format',
    
    '_ABOUT' => 'about',
    '_ADD_ME_TO_RECIPIENTS' => 'add_me_to_recipients',
    '_ADVANCED_SETTINGS' => 'advanced_settings',
    '_CANCEL' => 'cancel',
    '_CLEAR_ALL' => 'clear_all',
    '_CLOSE' => 'close',
    '_CREATED' => 'created',
    '_DELETE' => 'delete',
    '_DETAILS' => 'details',
    '_DOWNLOAD' => 'download',
    '_DOWNLOADER_RECEIPT' => 'downloader_receipt',
    '_DOWNLOADS' => 'downloads',
    '_DRAG_AND_DROP' => 'drag_and_drop',
    '_EMAIL_SENT' => 'email_sent',
    '_EMAIL_SEPARATOR_MSG' => 'email_separator_msg',
    '_ENTER_TO_EMAIL' => 'enter_to_email',
    '_EXPIRY_DATE' => 'expiry_date',
    '_FILES' => 'files',
    '_FROM' => 'from',
    '_HELP' => 'help',
    '_INVALID_FILE' => 'invalid_file',
    '_LOGON' => 'logon',
    '_MESSAGE' => 'message',
    '_NO' => 'no',
    '_NUMBER_OF_FILES' => 'number_of_files',
    '_OK' => 'ok',
    '_OPTIONAL' => 'optional',
    '_PAUSE' => 'pause',
    '_RECIPIENTS' => 'recipients',
    '_SELECT_FILE' => 'select_file',
    '_SELECT_FILES' => 'select_files',
    '_SEND' => 'send',
    '_SEND_NEW_VOUCHER' => 'send_new_voucher.html.php',
    '_SEND_VOUCHER' => 'send_voucher',
    '_SHOWHIDE' => 'showhide',
    '_SITE_FOOTER' => 'site_footer.html.php',
    '_SIZE' => 'size',
    '_SUBJECT' => 'subject',
    '_TERA_WORKER_COUNT' => 'terasender_worker_count',
    '_TO' => 'to',
    '_YES' => 'yes',
    
    '_SITE_SPLASHHEAD' => 'site_splash.html.php@1',
    '_SITE_SPLASHTEXT' => 'site_splash.html.php@2',
    
    '_HELP_TEXT' => 'help_text.html.php',
    '_ABOUT_TEXT' => 'about_text.html.php',
    
    '_AUPTERMS' => 'aupterms.html.php',
);

$path = 'language/';

$strreg = '`^\$lang\[[\'"]([^\'"]+)[\'"]\]\s*=(.+)$`';

foreach(scandir($path) as $i) {
    if(substr($i, 0, 1) == '.') continue;
    if(!is_dir($path.$i)) continue;
    if($i == 'en_AU') continue;
    
    $lang_file = $path.$i.'/lang.php';
    if(!file_exists($lang_file)) continue;
    
    $lines = explode("\n", file_get_contents($lang_file));
    $out = array();
    $create = array();
    
    while(!is_null($line = array_shift($lines))) {
        if(trim($line) == '?>') continue;
        if(substr(trim($line), 0, 2) == '//') continue;
        
        if(preg_match($strreg, $line, $m)) {
            $id = $m[1];
            $line = $m[2];
            
            if(!array_key_exists($id, $renamed)) continue;
            
            while(count($lines) && !preg_match($strreg, $lines[0]))
                $line .= "\n".array_shift($lines);
            
            $line = trim($line);
            $line = preg_replace('`Config::get\(\'([^\']+)\'\)`ms', '{cfg:$1}', $line);
            $line = trim($line);
            $line = preg_replace('`htmlspecialchars\(([^\)]+)\)`ms', '$1', $line);
            $line = trim($line);
            $line = preg_replace('`[\'"]\s*\.\s*Utilities::formatBytes\(\s*\{cfg:([^\}]+)\}\s*\)\s*\.\s*[\'"]`ms', '{size:cfg:$1}', $line);
            $line = trim($line);
            $line = preg_replace('`([\'"])\s*\.\s*(\{cfg:[^\}]+\})(\s*;)?$`ms', '$2$1', $line);
            $line = trim($line);
            $line = preg_replace('`^(\{cfg:[^\}]+\})\s*\.\s*([\'"])`ms', '$2$1', $line);
            $line = trim($line);
            $line = preg_replace('`([\'"])\s*\.\s*(\{cfg:[^\}]+\})\s*\.\s*([\'"])`ms', '$2', $line);
            $line = trim($line);
            
            if(preg_match('`^(.+);\s*//.+$`ms', $line, $m))
                $line = $m[1];
            
            if(preg_match('`^(.+);\s*\?>$`ms', $line, $m))
                $line = $m[1];
            
            $line = trim($line);
            if(substr($line, -1) == ';') $line = substr($line, 0, -1);
            $line = trim($line);
            $line = preg_replace_callback('`^([\'"])(.*)[\'"]$`ms', function($m) {
                if($m[1] == '"') return str_replace('\'', '\\\'', $m[2]);
                return $m[2];
            }, $line);
            $line = trim($line);
            
            if(preg_match('`^(.+\.(?:html|text)\.php)(?:@([0-9]+))?$`', $renamed[$id], $m)) {
                $id = $m[1];
                if(!array_key_exists($id, $create))
                    $create[$id] = array();
                
                $p = (count($m) > 2) ? (int)$m[2] : 0;
                
                $create[$id]['o'.sprintf('%03d', $p)] = $line;
                continue;
                
            } else $id = $renamed[$id];
            
            if(in_array($id, array('dp_month_names', 'dp_month_names_short', 'dp_day_names', 'dp_day_names_short', 'dp_day_names_min'))) {
                if(substr($line, 0, 1) == '[' && substr($line, -1) == ']')
                    $line = preg_replace('`\s*(\\\\\'|")\s*`', '', substr($line, 1, -1));
            }
            
            $out[] = '$lang[\''.$id.'\'] = \''.$line.'\';';
            
        } else $out[] = $line;
    }
    
    if($fh = fopen($lang_file, 'w')) {
        fwrite($fh, implode("\n", $out));
        fclose($fh);
    } else throw new Exception('Could not write to '.$lang_file);
    
    foreach($create as $file => $strings) {
        ksort($strings);
        
        if($fh = fopen($path.$i.'/'.$file, 'w')) {
            fwrite($fh, implode("\n\n", $strings));
            fclose($fh);
        } else throw new Exception('Could not write to '.$path.$i.'/'.$file);
    }
}
