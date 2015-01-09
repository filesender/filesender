subject: {cfg:site_name}: Invitation envoyée

{alternative:plain}

Madame, Monsieur,

Une invitation à déposer des fichiers sur {cfg:site_name} a été envoyée à {guest.email}.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Une invitation à déposer des fichiers sur <a href="{cfg:site_url}">{cfg:site_name}</a> a été envoyée à <a href="mailto:{guest.email}">{guest.email}</a>.
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
