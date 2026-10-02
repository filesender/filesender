<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: Codzienne podsumowanie transferu

{alternative:plain}

Szanowni Państwo, 

Poniżej znajduje się podsumowanie pobrań dla transferu {transfer.id} (przesłane {data: transfer.created}): 

{if: events} {each: events as event}
 - Odbiorca {event.who} pobrał {if: event.what == "archive"} archiwum {else} plik {event.what_name} {endif} w dniu 
{datetime: event.when} 
{endeach} 
{else} 
Brak pobranych plików
{endif} 

Dodatkowe informacje można znaleźć na stronie {transfer.link} 

Z Poważaniem, 
{cfg: site_name} 

{alternatywa: html} 

<p>
 Szanowni Państwo, 
</p> 

<p>
 Poniżej znajduje się podsumowanie pobrań dla transferu {transfer.id} (przesłane {data: transfer.created}): 
</p> 

{if: events} 
<ul> 
{each: events as event}
 <li> Odbiorca {event.who} pobranł { if: event.what == "archive"} archiwum {else} plik {event.what_name} {endif} w dniu {datetime: event.when} </li> 
{endeach} 
</ul> 
{else} 
<p>
 Brak pobranych plików 
</p> 
{endif} 

<p>
 Dodatkowe informacje można znaleźć na stronie <a href="{transfer.link}"> {transfer.link} </a> 
</p> 

<p>
 Z Poważaniem, <br />
 {cfg: site_name} 
</p>

