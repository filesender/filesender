<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Storage usage warning

{alternative:plain}

Dear Sir or Madam,

The storage usage of {cfg:site_name} is warning :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) only has {size:warning.free_space} left ({warning.free_space_pct}%)
{endeach}

You may find additional details at {cfg:site_url}

Best regards,
{cfg:site_name}

{alternative:html}

<table style="width:800" align="left" border="4" padding="40">
<tr><td><img src="{cfg:site_url}images/banner800.png" alt="SURFfilesender Logo" />

<p style="font-family:Arial, sans-serif; font-size:14px; text-decoration:none; font-style:normal">
    The storage usage of {cfg:site_name} is warning :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) only has {size:warning.free_space} left ({warning.free_space_pct}%)</li>
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
