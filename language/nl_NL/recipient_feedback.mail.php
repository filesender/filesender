onderwerp: Feedback van uw {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gast{endif} {target.email}

{alternative:plain}

Geachte mevrouw, heer,

We hebben een e-mailfeedback ontvangen van uw {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gast{endif} {target.email}, vind het als bijlage.

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
    We hebben een e-mailfeedback ontvangen van uw {if:target_type=="recipient"}ontvanger{endif}{if:target_type=="guest"}gast{endif} {target.email}, vind het als bijlage.
</p>

<p>
    Hoogachtend,<br />
    {cfg:site_name}
</p>