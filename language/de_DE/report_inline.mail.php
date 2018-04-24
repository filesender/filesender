Betrifft: Bericht über {target.type} #{target.id}

{alternative:plain}

Sehr geehrte Damen und Herren,

hier ist der Bericht über Ihre/n {target.type}:

{target.type} Nummer: {target.id}

{if:target.type == "Transfer"}
Diese Übertragung hat {transfer.files} Dateien mit einer Gesamtgröße von {size:transfer.size}.

Diese Übertragung ist/war verfügbar, bis {date:transfer.expires}.

Sie wurde an {transfer.recipients} Empfänger verschickt.
{endif}
{if:target.type == "File"}
Diese Datei heißt {file.path}. Sie hat eine Größe von {size:file.size} und ist/war verfügbar bis zum {date:file.transfer.expires}.
{endif}
{if:target.type == "Recipient"}
Dieser E-Mail-Adresse des Empfängers lautet {recipient.email} und ist/war gültig bis zum {date:recipient.expires}.
{endif}

Hier ist das vollständige Protokoll zu dieser Übertragung:

{raw:content.plain}

Mit freundlichen Grüßen,
{cfg:site_name}

{alternative:html}

<P>
    Sehr geehrte Damen und Herren,
</ P>

<p>
hier ist der Bericht über Ihre/n {target.type}:<br /><br />


{target.type} Nummer: {target.id}<br /><br />

{if:target.type == "Transfer"}
Diese Übertragung hat {transfer.files} Dateien mit einer Gesamtgröße von {size:transfer.size}.<br /><br />

Diese Übertragung ist/war verfügbar, bis {date:transfer.expires}.<br /><br />

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
Hier ist das vollständige Protokoll zu dieser Übertragung:

{raw:content.plain}

Mit freundlichen Grüßen,
{cfg:site_name}


<P>
    Hier ist das vollständige Protokoll zu dieser Übertragung:
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
{Cfg: site_name} </p>
