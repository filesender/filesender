subject: Feedback from {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}

{alternative:plain}

Dear Sir or Madam,

We received an email feedback from {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, please find it enclosed.

Best regards,
{cfg:site_name}

{alternative:html}

<p>
    Dear Sir or Madam,
</p>

<p>
    We received an email feedback from {if:target_type=="recipient"}recipient{endif}{if:target_type=="guest"}guest{endif}#{target_id} {target.email}, please find it enclosed.
</p>

<p>
    Best regards,<br />
    {cfg:site_name}
</p>
