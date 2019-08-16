<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Bericht über {if:target.type=="transfer"}Dateiübertragung{endif}{if:target.type=="recipient"}Empfänger{endif}{if:target.type=="guest"}Einladung{endif}{if:target.type=="file"}Datei{endif} Nr. {target.id}

{alternative:plain}

Sehr geehrte Damen und Herren,

hier ist der Bericht über {if:target.type=="recipient"}Ihre Dateiübertragung{endif}{if:target.type=="guest"}Ihre Einladung{endif}{if:target.type=="file"}Ihre Datei{endif}:

{target.type} Nummer: {target.id}

{if:target.type == "Transfer"}
Diese Dateiübertragung hat {transfer.files} {if:transfer.files>1}Dateien{else}Datei{endif} mit einer Gesamtgröße von {size:transfer.size}.

Diese Dateiübertragung ist/war verfügbar, bis {date:transfer.expires}.

Sie wurde an {transfer.recipients} Empfänger verschickt.
{endif}
{if:target.type == "File"}
Diese Datei heißt {file.path}. Sie hat eine Größe von {size:file.size} und ist/war verfügbar bis zum {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Dieser E-Mail-Adresse des Empfängers lautet {recipient.email} und ist/war gültig bis zum {date:recipient.expires}.
{endif}

Hier ist das vollständige Protokoll zu dieser Dateiübertragung:

{raw:content.plain}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<P>
    Sehr geehrte Damen und Herren,
</ P>

<p>
hier ist der Bericht über {if:target.type=="recipient"}Ihre Dateiübertragung{endif}{if:target.type=="guest"}Ihre Einladung{endif}{if:target.type=="file"}Ihre Datei{endif}:<br /><br />

{target.type} Nr.: {target.id}<br /><br />

{if:target.type == "Transfer"}
Diese Dateiübertragung hat {transfer.files} {if:transfer.files>1}Dateien{else}Datei{endif} mit einer Gesamtgröße von {size:transfer.size}.
<br /><br />

Diese Dateiübertragung ist/war verfügbar, bis {date:transfer.expires}.<br /><br />

Sie wurde an {transfer.recipients} Empfänger verschickt.
{endif}
{if:target.type == "File"}
Diese Datei heißt {file.path}. Sie hat eine Größe von {size:file.size} und ist/war verfügbar bis zum {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Dieser E-Mail-Adresse des Empfängers lautet {recipient.email} und ist/war gültig bis zum {date:recipient.expires}.
{endif}
</p>

<p>
    Hier ist das vollständige Protokoll zu dieser Dateiübertragung:
    <table class = "auditlog" rules="rows">
        <thead>
            <th>Datum</th>
            <th>Ereignis</th>
            <th>IP-Adresse</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p> Mit freundlichen Grüßen, <br/>
{cfg:site_name}</p>
