<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Raportti tapahtumasta {target.type} #{target.id}

{alternative:plain}

Hei!

Ohessa pyytämäsi raportti tapahtumasta {target.type}:

{target.type}, numero: {target.id}

{if:target.type == "Transfer"}
Tiedostojakoon sisältyy {transfer.files} tiedosto(a), kooltaan yhteensä {size:transfer.size}.

Tämä tiedostojako on/oli saatavilla {date:transfer.expires} asti.

Vastaanottajia oli {transfer.recipients} kpl.
{endif}
{if:target.type == "File"}
Tiedosto {file.path}, kooltaan {size:file.size} on/oli saatavilla {date:file.transfer.expires} asti.
{endif}
{if:target.type == "Recipient"}
Vastaanottaja ({recipient.email}) on/oli saatavilla {date:recipient.expires} asti.
{endif}

Tiedostojakoon liittyvät lokimerkinnät:

{raw:content.plain}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Ohessa pyytämäsi raportti tapahtumasta {target.type}:<br /><br />
    
    {target.type}, numero: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Tiedostojakoon sisältyy {transfer.files} tiedosto(a), kooltaan yhteensä {size:transfer.size}.<br /><br />
    
    Tämä tiedostojako on/oli saatavilla {date:transfer.expires} asti.<br /><br />
    
    Vastaanottajia oli {transfer.recipients} kpl.
    {endif}
    {if:target.type == "File"}
    Tiedosto {file.path}, kooltaan {size:file.size} on/oli saatavilla {date:file.transfer.expires} asti.
    {endif}
    {if:target.type == "Recipient"}
    Vastaanottaja ({recipient.email}) on/oli saatavilla {date:recipient.expires} asti.
    {endif}
</p>

<p>
    Tiedostojakoon liittyvät lokimerkinnät:
    <table class="auditlog" rules="rows">
        <thead>
            <th>Ajankohta</th>
            <th>Tapahtuma</th>
            <th>IP-osoite</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Terveisin,<br/>
{cfg:site_name}</p>