<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Transfer daily summary

{alternative:plain}

Hei!

Alla näet koosteen tiedostojakosi latauksista {transfer.id} (jaettu {date:transfer.created}):

{if:events}
{each:events as event}
  - Vastaanottaja {event.who} latasi {if:event.what == "archive"}tiedostopaketin{else}tiedoston {event.what_name}{endif} ajassa {datetime:event.when}
{endeach}
{else}
Ei latauksia
{endif}

Lisätietoja osoitteessa {transfer.link}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Alla näet koosteen tiedostojakosi latauksista {transfer.id} (jaettu {date:transfer.created}):
</p>

{if:events}
<ul>
{each:events as event}
    <li>Vastaanottaja {event.who} latasi {if:event.what == "archive"}tiedostopaketin{else}tiedoston {event.what_name}{endif} ajassa {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Ei latauksia
</p>
{endif}

<p>
    Lisätietoja osoitteessa <a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>