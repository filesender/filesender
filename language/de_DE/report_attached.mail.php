<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Bericht über {if:target.type=="Transfer"}Dateiübertragung{endif}{if:target.type=="Recipient"}Empfänger{endif}{if:target.type=="Guest"}Einladung{endif}{if:target.type=="File"}Datei{endif} Nr. {target.id}

{alternative:plain}

Sehr geehrte Damen und Herren,

in der Anlage dieser E-Mail finden Sie den Bericht der Dateiübertragung [{target.type}] Nr. {target.id}.

Mit freundlichen Grüßen
{cfg:site_name}

{alternative:html}

<p>
    Sehr geehrte Damen und Herren,
</p>

<p>
    in der Anlage dieser E-Mail finden Sie den Bericht der Dateiübertragung [{target.type}] Nr. {target.id}.
</p>

<p>Mit freundlichen Grüßen<br/>
{cfg:site_name}</p>