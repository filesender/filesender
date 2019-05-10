<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: dagstaat van uw transfers

{alternative:plain}

Geachte heer, mevrouw,

Onderstaand gelieve aan te treffen een dagstaat van de downloads van uw transfer {transfer.id} (ge-upload op {date:transfer.created}) :

{if:events} {each:events as event}
 - Ontvanger {event.who} heeft gedownload {if:event.what == "archive"}archief{else}bestand{event.what_name}{endif} op {datetime:event.when}
{endeach}
{else}
Geen downloads
{endif}

U kunt nadere details bekijken op {transfer.link}
Hoogachtend,{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    Onderstaand gelieve aan te treffen een dagstaat van de downloads van uw transfer {transfer.id} (ge-upload op {date:transfer.created}) :
</p>

{if:events}
<ul>
{each:events as event}
    <li>Ontvanger {event.who} heeft gedownload {if:event.what == "archive"}archief{else}bestand {event.what_name}{endif} op {datetime:event.when}</li>
{endeach}
</ul>
{else}
<p>
    Geen dowloads
</p>
{endif}

<p>
    U kunt nadere details bekijken op <a href="{transfer.link}">{transfer.link}</a>
</p>

</td></tr>
 <tr style="border-style:none">
    <td align="center">
       <p style="font-size:12px; text-decoration:none">
       Meer informatie over de SURFfilesender dienst is beschikbaar op
       <a rel="nofollow" href="https://www.surffilesender.nl/" target="_blank">www.surffilesender.nl</a>
       </p>
       <p style="font-size:10px; text-decoration:none"> SURFfilesender is powered by <a rel="nofollow" href="https://www.surf.nl/" target="_blank">SURF</a>.
       </p>
    </td>
</tr>
</table>
