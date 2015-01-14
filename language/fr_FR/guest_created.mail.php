subject: Invitation

{alternative:plain}

Madame, Monsieur,

Veuillez trouver ci-dessous une invitation de {guest.user_email} pour déposer des fichiers sur {cfg:site_name}.

Lien de dépôt: {cfg:site_url}?s=upload&vid={guest.token}

Cette invitation est valable jusqu'au {date:guest.expires} après quoi elle sera automatiquement revoquée.

{if:guest.message}Message de {guest.user_email}: {guest.message}{endif}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Veuillez trouver ci-dessous une invitation de {guest.user_email} pour déposer des fichiers sur <a href="{cfg:site_url}">{cfg:site_name}</a>.
</p>


<p>
    Lien de dépôt: <a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a>
</p>

<p>
    Cette invitation est valable jusqu'au {date:guest.expires} après quoi elle sera automatiquement revoquée.
</p>

{if:guest.message}
<p>
    Message de {guest.user_email}: {guest.message}
</p>
{endif}

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
