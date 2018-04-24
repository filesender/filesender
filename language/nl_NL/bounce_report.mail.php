onderwerp: fout bij afleveren berichten

{alternative:plain}

Geachte mevrouw, heer,

Het is niet gelukt uw bericht(en) af te leveren bij een of meer van uw ontvangers :

{each:bounces as bounce}
{if:bounce.target_type=="Recipient"}
  - Transfer #{bounce.target.transfer.id} ontvanger
{bounce.target.email} op {datetime:bounce.date} ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
 - Gastgebruiker {bounce.target.email} op {datetime:bounce.date}
{endif}
{endeach}

Verdere bijzonderheden kunt u vinden op {cfg:site_url}

Hoogachtend,
{cfg:site_name}

{alternative:html}

<p>
    Geachte mevrouw, heer,
</p>

<p>
    Het is niet gelukt uw bericht(en) af te leveren bij een of meer van uw ontvangers :
</p>

<ul>
{each:bounces as bounce}
    <li> {if:bounce.target_type=="Recipient"}
               <a href="{bounce.target.transfer.link}">Transfer #{bounce.target.transfer.id}</a> ontvanger
{bounce.target.email} op {datetime:bounce.date}
     {endif}{if:bounce.target_type=="Guest"}
         Gastgebruiker {bounce.target.email} op {datetime:bounce.date}
    {endif}
    </li>
{endeach}
</ul>

<p>
   Verdere bijzonderheden kunt u vinden op <a href="{cfg:site_url}">{cfg:site_url}</a>
</p>

<p>
   Hoogachtend,<br />
   {cfg:site_name}
</p>