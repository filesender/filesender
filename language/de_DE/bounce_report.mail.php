Betreff: Nachrichtenübermittlungsfehler

{alternative:plain}

Sehr geehrte Damen und Herren,

eine oder mehrere Ihrer Empfänger haben Ihre Nachricht(en) nicht erhalten:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Übertragung  #{bounce.target.transfer.id} an Empfänger {bounce.target.email} am {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Gast {bounce.target.email} am {datetime:bounce.date}
{endif}
{endeach}

Weitere Details finden Sie unter {cfg:site_url}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    eine oder mehrere Ihrer Empfänger haben Ihre Nachricht(en) nicht erhalten:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Übertragung #{bounce.target.transfer.id}</a> an Empfänger {bounce.target.email} am {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Gast {bounce.target.email} am {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Weitere Details finden Sie unter <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    Mit freundlichen Grüßen,<br />
    {cfg:site_name}
</p>
