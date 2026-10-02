<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Rapport sur le {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} n°{target.id}

{alternative:plain}

Madame, Monsieur,

Veuillez trouver en pièce-jointe le rapport sur le {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} n°{target.id}.

Cordialement,
{cfg:site_name}

{alternative:html}

<p>
    Madame, Monsieur,
</p>

<p>
    Veuillez trouver en pièce-jointe le rapport sur le {if:target.type=="Transfer"}dépôt{endif}{if:target.type=="File"}fichier{endif}{if:target.type=="Recipient"}destinataire{endif} n°{target.id}.
</p>

<p>Cordialement,<br/>
{cfg:site_name}</p>
