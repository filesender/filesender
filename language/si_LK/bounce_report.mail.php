<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: පණිවිඩ බෙදා හැරීම අසාර්ථකයි

{alternative:plain}

හිතවත් මහත්මයාණෙනි හෝ මැතිණියනි,

ඔබගේ ලබන්නන්ගෙන් එකක් හෝ වැඩි ගණනක් ඔබගේ පණිවිඩ(ය) ලැබීමට අපොහොසත් විය:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} on {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} on {datetime:bounce.date}
{endif}
{endeach}

ඔබට {cfg:site_url} හි අමතර විස්තර සොයා ගත හැක

සුභ පතමින්,
{cfg:site_name}

{විකල්ප:html}

<p>
    හිතවත් සර් හෝ මැතිණියනි,
</p>

<p>
    ඔබගේ ලබන්නන්ගෙන් එකක් හෝ වැඩි ගණනක් ඔබගේ පණිවිඩය(ය) ලැබීමට අපොහොසත් විය:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> recipient {bounce.target.email} on {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Guest {bounce.target.email} on {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

    </li>
{endeach}
</ul>

<p>
    ඔබට <a href="{cfg:site_url}">{cfg:site_url}</a> හි අමතර විස්තර සොයා ගත හැක
</p>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>