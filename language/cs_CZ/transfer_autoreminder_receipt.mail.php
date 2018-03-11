subject: Automatická připomínka pro přenos č. {transfer.id}

{alternative:plain}

Vážený uživateli,

Právě byla odeslána automatická připomínka příjemcům, kteří si dosud nestáhli soubory z Vašeho přenosu č. {transfer.id} na {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Právě byla odeslána automatická připomínka příjemcům, kteří si dosud nestáhli soubory z Vašeho přenosu č. <a href="{transfer.link}">transfer n°{transfer.id}</a> na <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>

