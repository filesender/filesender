Betreff: Feedback von Ihrem {if:target_type=="recipient"}Empfänger{endif}{if:target_type=="guest"}Gast{endif} {target.email}

{alternative:plain}

Sehr geehrte Damen und Herren,

Wir erhielten eine Feedback E-Mail von Ihrem {if:target_type=="recipient"}Empfänger{endif}{if:target_type=="guest"}Gast{endif} {target.email}, diese finden Sie in der Anlage.

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    Wir erhielten eine Feedback E-Mail von Ihrem {if:target_type=="recipient"}Empfänger{endif}{if:target_type=="guest"}Gast{endif} {target.email}, diese finden Sie in der Anlage.
</p>

<p>
    Mit freundlichen Grüßen,<br />
    {cfg:site_name}
</p>
