<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
konu: {target.type} #{target.id} hakkında rapor

{alternative:plain}

Merhaba,

{target.type} ile ilgili rapor buradadır:

{target.type} numarası: {target.id}

{if:target.type == "Transfer"}
Bu aktarma {transfer.files} toplam  {size:transfer.size} boyutunda dosyalara sahiptir.

Bu aktarma {date:transfer.expires} tarihine kadar geçerlidir.

Bu aktarma {transfer.recipients} alıcılara gönderilmiştir.
{endif}
{if:target.type == "File"}
Bu dosya {file.path} şeklinde adlandırılır,  {size:file.size} boyutundadır ve  {date:file.transfer.expires} tarihine kadar geçerlidir.
{endif}
{if:target.type == "Recipient"}
Bu alıcı bir e-posta adresinde {recipient.email} sahiptir ve {date:recipient.expires} tarihine kadar geçerlidir.
{endif}

Aktarmada olanlara dair kayıtların tümü buradadır :

{raw:content.plain}

Saygılarımızla,
{cfg:site_name}

{alternative:html}

<p>
    Merhaba,
</p>

<p>
    {target.type} ile ilgili rapor buradadır:<br /><br />
    
    {target.type} numarası: {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    Bu aktarma {transfer.files} toplam  {size:transfer.size} boyutunda dosyalara sahiptir.<br /><br />
    
    aktarma {date:transfer.expires} tarihine kadar geçerlidir.<br /><br />
    
    Bu aktarma {transfer.recipients} alıcılara gönderilmiştir.
    {endif}
    {if:target.type == "File"}
    Bu dosya {file.path} şeklinde adlandırılır,  {size:file.size} boyutundadır ve  {date:file.transfer.expires} tarihine kadar geçerlidir.
    {endif}
    {if:target.type == "Recipient"}
    Bu alıcı bir e-posta adresinde {recipient.email} sahiptir ve {date:recipient.expires} tarihine kadar geçerlidir.
    {endif}
</p>

<p>
    Aktarmada olanlara dair kayıtların tümü buradadır :
    <table class="auditlog" rules="rows">
        <thead>
            <th>Date</th>
            <th>Event</th>
            <th>IP address</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>Saygılarımızla,<br/>
{cfg:site_name}</p>