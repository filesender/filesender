<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Message delivery failure

{alternative:plain}

Dear Sir or Madam,

One or more of your recipients failed to receive your message(s) :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} recipient {bounce.target.email} on {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Guest {bounce.target.email} on {datetime:bounce.date}
{endif}
{endeach}

You may find additional details at {cfg:site_url}

Best regards,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    One or more of your recipients failed to receive your message(s) :
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
    You may find additional details at <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

</td></tr>

 <tr style="border-style:none">
    <td align="center">
       <p style="font-size:12px; text-decoration:none">
       More information about the SURFfilesender service can be found at
       <a rel="nofollow" href="https://www.surffilesender.nl/en/" target="_blank">www.surffilesender.nl/en</a>
       </p>
       <p style="font-size:10px; text-decoration:none"> SURFfilesender is powered by <a rel="nofollow" href="https://www.surf.nl/en/" target="_blank">SURF</a>.
       </p>
    </td>
</tr>
</table>
