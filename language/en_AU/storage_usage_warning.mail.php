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

<p>
    Dear Sir or Madam,
</p>

<p>
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

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
