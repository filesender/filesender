<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: सन्देश पठाउन असफल

{alternative:plain}

प्रिय महोदय वा महोदया,

तपाईंको एक वा बढी प्राप्तकर्ताहरूले तपाईंको सन्देश(हरु) प्राप्त गर्न असफल भए :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} on {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} on {datetime:bounce.date}
{endif}
{endeach}

तपाईंले यहाँ {cfg:site_url} थप विवरणहरू फेला पार्न सक्नुहुन्छ

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
   प्रिय महोदय वा महोदया,
</p>

<p>
    तपाईंको एक वा बढी प्राप्तकर्ताहरूले तपाईंको सन्देश(हरु) प्राप्त गर्न असफल भए :
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

<p>
    तपाईंले यहाँ <a href="{cfg:site_url}">{cfg:site_url}</a> थप विवरणहरू फेला पार्न सक्नुहुन्छ।
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>