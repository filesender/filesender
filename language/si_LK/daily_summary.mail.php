<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: දෛනික සාරාංශය මාරු කරන්න

{alternative:plain}

හිතවත් මහත්මයාණෙනි හෝ මැතිණියනි,

කරුණාකර ඔබගේ මාරු කිරීම සඳහා බාගැනීම් වල සාරාංශයක් සොයා ගන්න {transfer.id} (උඩුගත කළ {date:transfer.created}) :

{if:events}
{each:events as event}
  - Recipient {event.who} downloaded {if:event.what == "archive"}archive{else}file {event.what_name}{endif} on {datetime:event.when}
{endeach}
{else}
No downloads
{endif}

ඔබට {transfer.link} හි අමතර විස්තර සොයා ගත හැක

සුභ පතමින්,
{cfg:site_name}

{විකල්ප:html}

<p>
    හිතවත් මහත්මයාණෙනි හෝ මැතිණියනි,
</p>

<p>
    කරුණාකර ඔබගේ මාරු කිරීම සඳහා බාගැනීම් වල සාරාංශයක් සොයා ගන්න {transfer.id} (උඩුගත කළ {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Recipient {event.who} downloaded {if:event.what == "archive"}archive{else}file {event.what_name}{endif} on {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    බාගැනීම් නැත
</p>
{endif}

<p>
    ඔබට <a href="{transfer.link}">{transfer.link}</a> හි අමතර විස්තර සොයා ගත හැක
</p>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>