<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: İleti teslim hatası

{alternative:plain}

Merhaba,

İletileriniz, alıcılarınızdan birine veya daha fazlasına ulaştırılamadı:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Aktarma numarası{bounce.target.transfer.id} alıcı {bounce.target.email} tarihinde {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Konuk {bounce.target.email} tarihinde {datetime:bounce.date}
{endif}
{endeach}

Ek detayları {cfg:site_url} adresinden bulabilirsiniz

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    İletileriniz, alıcılarınızdan birine veya daha fazlasına ulaştırılamadı:
</p>

<ul>
{each:bounces as bounce}
    <li>
{if:bounce.target_type=="Recipient"}
<a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> recipient {bounce.target.email} on {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Guest {bounce.target.email} on {datetime:bounce.date}
    {endif}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Detayları şu adreste bulabilirsiniz: <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Saygılarımızla,<br />
    {cfg:site_name}
</p>