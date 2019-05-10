<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Terugkoppelingsformulier
{if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gastgebruiker{endif}#{target_id} {target.email}

{alternative:plain}

Geachte heer, mevrouw,

Wij ontvingen terugkoppeling per email van {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gastgebruiker{endif}#{target_id} {target.email}, u treft deze bijgesloten aan.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    Wij ontvingen terugkoppeling per email van {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gastgebruiker{endif}#{target_id} {target.email}, u treft deze bijgesloten aan.
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

