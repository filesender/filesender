<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Bestand{if:transfer.files>1}en{endif} beschikbaar voor download
subject: {transfer.subject}

{alternative:plain}

Geachte heer, mevrouw,

{if:transfer.files>1}De volgende bestanden zijn {else}Het volgende bestand is {endif} geüpload naar {cfg:site_name} door {transfer.user_email} en u hebt toestemming gekregen om {if:transfer.files>1}ze{else}het{endif} te downloaden:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Download link: {recipient.download_link}

De transactie is beschikbaar tot {date:transfer.expires} na die tijd wordt het automatisch verwijderd.

{if:transfer.message || transfer.subject}
Persoonlijk bericht van {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
 {if:transfer.files>1}De volgende bestanden zijn {else}Het volgende bestand is {endif} geüpload naar <a href="{cfg:site_url}">{cfg:site_name}</a> door <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> en u hebt toestemming gekregen om {if:transfer.files>1}ze{else}het{endif} te downloaden:
</p>

<table style="width:800" align="center" rules="rows">
        <tr>
            <th colspan="2">{if:transfer.files>1}De volgende bestanden zijn {else}Het volgende bestand is {endif} beschikbaar:</th>
        </tr>
        <tr>
            <td style="width:100">Bestand{if:transfer.files>1}en{endif}:</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        <tr>
            <td style="width:125">Totale bestandsgrootte:</td>
            <td>{size:transfer.size}</td>
        </tr>
        {if:transfer.message}
        <tr>
            <td style="width:125">Persoonlijk bericht:</td>
            <td>{transfer.message}</td>
        </tr>
        {endif}
</table>

<br>&nbsp;<br />
<table style="width:800; border-radius:5px; border:0px; padding:0px" align="center">
  <tr>
<td style="width:300">&nbsp;</td>
<td align="center"; style="width:200; color: #ffffff; background-color: #ed6b06; display: block; font-family: Arial, sans-serif; font-size: 20px; font-style: normal; color:#ffffff; text-align: center;
text-decoration: none; word-spacing: 0px; border-radius: 10px; padding: 15px 20px">
<a href="{recipient.download_link}" target="_blank" style="text-decoration:none">
Download Bestand{if:transfer.files>1}en{endif}<br>
  </td>
<td style="width:300">&nbsp;</td>
  </tr>
</table>

<p style="font-size:14px; text-decoration:none; color:Tomato" align="center">Download {if:transfer.files>1}de bestanden{else} het bestand{endif} voor:<br>{date:transfer.expires}</p>
<p>&nbsp;</p>

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
