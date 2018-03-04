subject: Zpětná vazba od {if:target_type=="recipient"}příjemce{endif}{if:target_type=="guest"}hosta{endif}#{target_id} {target.email}

{alternative:plain}

Vážený uživateli,

Obdržel jste zpětnou vazbu od  {if:target_type=="recipient"}příjemce{endif}{if:target_type=="guest"}hosta{endif}#{target_id} {target.email}, text naleznete v příloze.

S pozdravem,
{cfg:site_name}

{alternative:html}

<p>
    Vážený uživateli,
</p>

<p>
    Obdržel jste zpětnou vazbu od {if:target_type=="recipient"}příjemce{endif}{if:target_type=="guest"}hosta{endif}#{target_id} {target.email}, text naleznete v příloze.
</p>

<p>
    S pozdravem,<br />
    {cfg:site_name}
</p>
