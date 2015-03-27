subject: Feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}

{alternative:plain}

Dear Sir or Madam,

We received an email feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, please find it enclosed.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    We received an email feedback from your {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif} {target.email}, please find it enclosed.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
