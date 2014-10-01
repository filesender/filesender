subject: {cfg:site_name} : Report about {target.type} #{target.id}

{alternative:plain}

Dear Sir or Madam,

Here is the report about your {target.type}:

{target.type} number : {target.id}

{if:target.type == "Transfer"}
This transfer has {transfer.files} files with an overall size of {size:transfer.size}.

This transfer is/was available until {date:transfer.expires}.

This transfer was sent to {transfer.recipients} recipients.
{else}{if:target.type == "File"}
This file is named {file.name}, has a size of {size:file.size} and is/was available until {date:file.transfer.expires}.
{else}{if:target.type == "Recipient"}
This recipient has email address {recipient.email} and is/was valid until {date:recipient.expires}.
{endif}{endif}{endif}

{content.plain}

Best regards,
{cfg:siteName}

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
    {else}{if:target.type == "File"}
    This file is named {file.name}, has a size of {size:file.size} and is/was available until {date:file.transfer.expires}.
    {else}{if:target.type == "Recipient"}
    This recipient has email address {recipient.email} and is/was valid until {date:recipient.expires}.
    {endif}{endif}{endif}
</p>

<p>
<table rules="rows">
    <thead>
        <th>Date</th>
        <th>Event</th>
    </thead>
    <tbody>
        {content.html}
    </tbody>
</p>

<p>Best regards,<br/>
{cfg:site_name}</p>
