<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: File(s) expired
subject: (files expired) {transfer.subject}

{alternative:plain}

Hello,

Transfer n°{transfer.id} has expired and is no longer available for download ({transfer.link}).

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Hello,
</p>

<p>
    <a href="{transfer.link}">Transfer n°{transfer.id}</a> has expired and is no longer available for download.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
