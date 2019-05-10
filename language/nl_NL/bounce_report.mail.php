<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: fout bij afleveren berichten

{alternative:plain}

Geachte heer, mevrouw,

Het is niet gelukt uw bericht(en) af te leveren bij een of meer van uw ontvangers :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} ontvanger
{bounce.target.email} op {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
 - Gastgebruiker {bounce.target.email} op {datetime:bounce.date}
{endif}
{endeach}

Verdere bijzonderheden kunt u vinden op {cfg:site_url}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    Het is niet gelukt uw bericht(en) af te leveren bij een of meer van uw ontvangers :
</p>

<ul>
{each:bounces as bounce}
    <li> {if:bounce.target_type=="Recipient"}
               <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> ontvanger
{bounce.target.email} op {datetime:bounce.date}
     {endif}{if:bounce.target_type=="Guest"}
         Gastgebruiker {bounce.target.email} op {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
   Verdere bijzonderheden kunt u vinden op <a href="{cfg:site_url}">{cfg:site_url}</a>
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
