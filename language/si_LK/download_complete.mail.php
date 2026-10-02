<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: බාගත කිරීම සම්පූර්ණයි

{alternative:plain}

හිතවත් මහත්මයාණෙනි හෝ මැතිණියනි,

ඔබගේ පහත {if:files>1}ගොනු{else}ගොනුව{endif} බාගැනීම අවසන් වී ඇත:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයාණෙනි හෝ මැතිණියනි,
</p>

<p>
    ඔබගේ පහත {if:files>1}ගොනු{else}ගොනුව{endif} බාගැනීම අවසන් වී ඇත:
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
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>