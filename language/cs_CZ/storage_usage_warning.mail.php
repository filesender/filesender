subject: Varování o obsazeném místě

{alternative:plain}

Vážený uživateli,

Využití úložiště {cfg:site_name} je ve stavu Varování :

{each:warnings as warning}
  - {warning.filesystem} ({size:warning.total_space}) má pouze {size:warning.free_space} volných ({warning.free_space_pct}%)
{endeach}

Více podrobností naleznete na {cfg:site_url}

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Využití úložiště {cfg:site_name} je ve stavu Varování :
</p>

<ul>
{each:warnings as warning}
    <li>{warning.filesystem} ({size:warning.total_space}) má pouze {size:warning.free_space} volných ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    Více podrobností naleznete na <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
