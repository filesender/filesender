<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: බාගතකිරීමේ රිසිට්පත 

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

{if:files>1}Several files{else}A file{endif} you uploaded {if:files>1}have{else}has{endif} been downloaded from {cfg:site_name} by {recipient.email} :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

ඔබට {files.first().transfer.link} හිදී මාරුවීම් පිටුවෙන් ඔබේ ගොනු වෙත ප්‍රවේශ වීමට සහ සවිස්තරාත්මක බාගැනීම් සංඛ්‍යාලේඛන බැලීමට හැකිය.

සුභ පතමින්,
{cfg:site_name}

{විකල්ප:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    {if:files>1}ගොනු කිහිපයක්{else}ගොනුවක්{endif} ඔබ උඩුගත කර ඇති {if:files>1}තිබේ{else}{endif} {recipient.email} විසින් {cfg:site_name} වෙතින් බාගත කර ඇත.
</p>

 {if:files>1}Several files{else}A file{endif} you uploaded {if:files>1}have{else}has{endif} been downloaded from {cfg:site_name} by {recipient.email}.
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>
<p>
    ඔබට <a href="{files.first().transfer.link}">{files.first().transfer.link}</a> හිදී ඔබේ ගොනු වෙත ප්‍රවේශ වීමට සහ විස්තර බාගත කිරීමේ සංඛ්‍යාලේඛන බැලීමට හැකිය.
</p>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>