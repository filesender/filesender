<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Download voltooid

{alternative:plain}

Geachte heer, mevrouw,

Uw download van {if:files>1}onderstaande bestanden{else}onderstaand bestand{endif} is voltooid : 

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    Uw download van {if:files>1}onderstaande bestanden{else}onderstaand bestand{endif} is voltooid : </p>

<p>
    {if:files>1}
    <ul> {each:files as file}
                <li>{file.path} ({size:file.size})</li>
            {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
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
