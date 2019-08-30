<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Påmindelser afsendt automatisk for filoverførsel nr. {transfer.id}

{alternative:plain}

Kære afsender!

Der er automatisk blevet afsendt påmindelser til modtagere som endnu ikke har hentet filer fra din overførsel nr. {transfer.id} på {cfg:site_name} ({transfer.link}):

{each:recipients as recipient}
  - {recipient.email}
{endeach}

Med venlig hilsen
{cfg:site_name}

{alternative:html}

<p>
Kære afsender!
</p>

<p>
Der er automatisk blevet afsendt påmindelser til modtagere som endnu ikke har hentet filer fra din <a href="{transfer.link}">overførsel nr. {transfer.id}</a> på <a href="{cfg:site_url}">{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    Med venlig hilsen<br />
    {cfg:site_name}
</p>