subject: Chyba doručení zprávy

{alternative:plain}

Vážený uživateli,

u některých z příjemců selhalo doručení zprávy:

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Přenos #{bounce.target.transfer.id} příjemce {bounce.target.email} dne {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - Host {bounce.target.email} dne {datetime:bounce.date}
{endif}
{endeach}

Více podrobností naleznete na adrese {cfg:site_url}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    u některých z příjemců selhalo doručení zprávy:
</p>

<ul>
{each:bounces as bounce}
    <li>
    {if:bounce.target_type=="Recipient"}
        <a href="{bounce.target.transfer.link}">Přenos #{bounce.target.transfer.id}</a> příjemce {bounce.target.email} dne {datetime:bounce.date}
    {endif}{if:bounce.target_type=="Guest"}
        Host {bounce.target.email} dne {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
    Více podrobností naleznete na adrese <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
