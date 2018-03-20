Betreff: Feedback von {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}

{alternative:plain}

Sehr geehrte Damen und Herren,

wir erhielten eine Feedback E-Mail von {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, finden Sie beigefügt.

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    wir erhielten eine Feedback E-Mail von {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, finden Sie beigefügt.
</p>

<p>
    Mit freundliche Grüßen,<br />
    {cfg:site_name}
</p>
