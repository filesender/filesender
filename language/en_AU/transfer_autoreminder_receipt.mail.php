subject: Automatic reminders sent for file shipment n°{transfer.id}

{alternative:plain}

Dear Sir or Madam,

An automatic reminder was sent to recipients that did not download files from your shipment n°{transfer.id} on {cfg:site_name} :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    An automatic reminder was sent to recipients that did not download files from your shipment n°{transfer.id} on <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
