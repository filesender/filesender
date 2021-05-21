<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: Günlük aktarım özeti

{alternative:plain}

Merhaba,

Aktarmanız {transfer.id} (uploaded {date:transfer.created}) için indirmelerin bir özeti aşağıdadır:

{if:events}
{each:events as event}
  - Alıcı{event.who} arşiv {if:event.what == "archive"}dosyasını{else}indirdi {event.what_name}{endif} {datetime:event.when} tarihinde
{endeach}
{else}
İndirme yok
{endif}

İlave detayları {transfer.link} adresinde bulabilirsinizS

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    Aktarmanız {transfer.id} (uploaded {date:transfer.created}) için indirmelerin bir özeti aşağıdadır:
</p>

{if:events}
<ul>
{each:events as event}
    <li>Alıcı{event.who} arşiv {if:event.what == "archive"}dosyasını{else}indirdi {event.what_name}{endif} {datetime:event.when} tarihinde</li>
{endeach}
</ul>
{else}
<p>
    İndirme yok
</p>
{endif}

<p>
    İlave detayları şu adreste bulabilirsiniz: S<a href="{transfer.link}">{transfer.link}</a>
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>
