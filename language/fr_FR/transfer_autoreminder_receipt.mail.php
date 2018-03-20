subject: Rappels automatiques envoyés pour le dépôt n°{transfer.id}

{alternative:plain}

Madame, Monsieur,

Un rappel automatique a été envoyé aux destinataires n'ayant téléchargé aucun fichiers de votre dépôt n°{transfer.id} sur {cfg:site_name} ({transfer.link}) :

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Un rappel automatique a été envoyé aux destinataires n'ayant téléchargé aucun fichiers de votre <a href="{transfer.link}">dépôt n°{transfer.id}</a> sur <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Cordialement,<br />
    {cfg:site_name}
</p>
