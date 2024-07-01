<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Automaattiset muistutukset lähetetty (tiedostojako #{transfer.id})

{alternative:plain}

Hei!

Tiedostojaon #{transfer.id} vastaanottajille, jotka eivät ole vielä ladanneet tiedostoja, on lähetetty muistutukset tiedostojen noutamiseksi palvelusta {cfg:site_name}.

Jakolinkki: {transfer.link}

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Terveisin,
{cfg:site_name}

{alternative:html}

<p>
    Hei!
</p>

<p>
    Tiedostojaon #{transfer.id} vastaanottajille, jotka eivät ole vielä ladanneet tiedostoja, on lähetetty muistutukset tiedostojen noutamiseksi palvelusta {cfg:site_name}.
</p>

<p>Jakolinkki: {transfer.link}</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Terveisin,<br />
    {cfg:site_name}
</p>

