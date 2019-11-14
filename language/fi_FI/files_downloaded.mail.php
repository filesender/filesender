<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Ilmoitus tiedostolatauksesta

{alternative:plain}

Hei,

Käyttäjä {recipient.email} on ladannut siirtämäsi {if:files>1}tiedostot {else}tiedoston{endif} palvelusta {cfg:site_name}:

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

Voit selata tiedostojasi ja katsoa tarkemmat lataustilastot osoitteessa {files.first().transfer.link}.

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei,
</p>

<p>
    Käyttäjä {recipient.email} on ladannut siirtämäsi {if:files>1}tiedostot {else}tiedoston{endif} palvelusta {cfg:site_name}:
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    Voit selata tiedostojasi ja katsoa tarkemmat lataustilastot osoitteessa <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>.
</p>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>