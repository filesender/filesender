<?php
/*
 * Site announcement displayed on the home page (unauthenticated users).
 *
 * To show an announcement, create content files under config/language/:
 *   cp language/en_AU/announce.html.php config/language/en_AU/announce.html.php
 *   cp language/ja_JP/announce.html.php config/language/ja_JP/announce.html.php
 * Then replace the comment block in each file with your HTML content.
 * Files in config/language/ take precedence over language/ and are not
 * overwritten by upstream updates.
 *
 * To hide the announcement, remove or empty the config/language/ files.
 *
 * Language selection:
 *   FileSender's translation system picks the announce.html.php matching
 *   the user's current language, falling back to English (en_AU).
 *
 * CSS classes (defined in www/css/default.css):
 *   site-announcement            blue   — general notice
 *   site-announcement--warning   yellow — maintenance / caution
 *   site-announcement--danger    red    — urgent / outage
 */

// language/en_AU/announce.html.php exists but is empty by default,
// so Lang::translate() never returns '{announce}' in normal operation.
// The check guards against edge cases where no language file exists at all.
$_announce_text = (string)Lang::translate('announce');
if ($_announce_text !== '{announce}') {
    echo $_announce_text;
}
unset($_announce_text);
