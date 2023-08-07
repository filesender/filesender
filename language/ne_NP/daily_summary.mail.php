<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: दैनिक सारांश स्थानान्तरण

{alternative:plain}

प्रिय महोदय वा महोदया,

कृपया आफ्नो स्थानान्तरण {transfer.id} (अपलोड गरिएको {date:transfer.created}) को लागि डाउनलोडहरूको सारांश तल फेला पार्नुहोस्। :

{if:events}
{each:events as event}
  - Recipient {event.who} downloaded {if:event.what == "archive"}archive{else}file {event.what_name}{endif} on {datetime:event.when}
{endeach}
{else}
No downloads
{endif}

तपाईंले {transfer.link} मा अतिरिक्त विवरणहरू फेला पार्न सक्नुहुन्छ

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    कृपया आफ्नो स्थानान्तरण {transfer.id} (अपलोड गरिएको {date:transfer.created}) को लागि डाउनलोडहरूको सारांश तल फेला पार्नुहोस्। :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Recipient {event.who} downloaded {if:event.what == "archive"}archive{else}file {event.what_name}{endif} on {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    No downloads
</p>
{endif}

<p>
    तपाईले थप विवरणहरू पाउन सक्नुहुन्छ <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>