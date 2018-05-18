subject: Rapport sur le {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} n°{target.id}

{alternative:plain}

Madame, Monsieur,

Voici le rapport sur le {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} n°{target.id} :

{if:target.type == "Transfer"}
Ce dépôt comporte {transfer.files} fichiers pour une taille totale de {size:transfer.size}.

Ce dépôt est/était disponible jusqu'au {date:transfer.expires}.

Ce dépôt a été envoyé à {transfer.recipients} destinataires.
{endif}
{if:target.type == "File"}
Ce fichier nommé {file.path} a une taille de {size:file.size} et est/était disponible jusqu'au {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Ce destinataire a pour adresse email {recipient.email} et est/était autorisé à télécharger les fichiers jusqu'au {date:recipient.expires}.
{endif}

Voici tous les évènements survenus durant la durée de vie du {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} :

{raw:content.plain}

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Voici le rapport sur le {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} n°{target.id} :<br /><br />
    
    {if:target.type == "Transfer"}
    Ce dépôt comporte {transfer.files} fichiers pour une taille totale de {size:transfer.size}.<br /><br />
    
    Ce dépôt est/était disponible jusqu'au {date:transfer.expires}.<br /><br />
    
    Ce dépôt a été envoyé à {transfer.recipients} destinataires.
    {endif}
    {if:target.type == "File"}
    Ce fichier nommé {file.path} a une taille de {size:file.size} et est/était disponible jusqu'au {date:file.transfer.expires}.
    {endif}
    {if:target.type == "Recipient"}
    Ce destinataire a pour adresse email {recipient.email} et est/était autorisé à télécharger les fichiers jusqu'au {date:recipient.expires}.
    {endif}
</p>

<p>
    Voici tous les évènements survenus durant la durée de vie du {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} :
    <table rules="rows">
        <thead>
            <th>Date</th>
            <th>Evènement</th>
            <th>Adresse IP</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Cordialement,<br/>
{cfg:site_name}</p>
