<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Report about {target.type} #{target.id}

{alternative:plain}

Dear Sir or Madam,

Here is the report about your {target.type}:

{target.type} number : {target.id}

{if:target.type == "Transfer"}
This transfer has {transfer.files} files with an overall size of {size:transfer.size}.

This transfer is/was available until {date:transfer.expires}.

This transfer was sent to {transfer.recipients} recipients.
{endif}
{if:target.type == "File"}
This file is named {file.path}, has a size of {size:file.size} and is/was available until {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
This recipient has email address {recipient.email} and is/was valid until {date:recipient.expires}.
{endif}

Here is the full log of what happened to the transfer :

{raw:content.plain}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    Here is the report about your {target.type}:<br /><br />
    
    {target.type} number : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    This transfer has {transfer.files} files with an overall size of {size:transfer.size}.<br /><br />
    
    This transfer is/was available until {date:transfer.expires}.<br /><br />
    
    This transfer was sent to {transfer.recipients} recipients.
    {endif}
    {if:target.type == "File"}
    This file is named {file.path}, has a size of {size:file.size} and is/was available until {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    This recipient has email address {recipient.email} and is/was valid until {date:recipient.expires}.
    {endif}
</p>

<p>
    Here is the full log of what happened to the transfer :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Date</th>
            <th>Event</th>
            <th>IP address</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Best regards,<br/>
{cfg:site_name}</p>
