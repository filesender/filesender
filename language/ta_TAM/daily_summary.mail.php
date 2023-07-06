<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
பொருள்: தினசரி சுருக்கத்தை மாற்றவும்

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

உங்கள் பரிமாற்றத்திற்கான பதிவிறக்கங்களின் சுருக்கத்தை கீழே காணவும் {transfer.id} (பதிவேற்றப்பட்டது {date:transfer.created}) :

{என்றால்:நிகழ்வுகள்}
{ஒவ்வொரு:நிகழ்வுகளும் நிகழ்வாக}
  - பெறுநர் {event.when} {if:event.what == "archive"}காப்பகம்{else}கோப்பை {event.what_name}{endif} {datetime:event.when} அன்று பதிவிறக்கினார்
{endeach}
{else}
பதிவிறக்கங்கள் இல்லை
{endif}

கூடுதல் விவரங்களை {transfer.link} இல் காணலாம்

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    உங்கள் பரிமாற்றத்திற்கான பதிவிறக்கங்களின் சுருக்கத்தை கீழே காணவும் {transfer.id} (பதிவேற்றப்பட்டது {date:transfer.created}) :
</p>

{என்றால்:நிகழ்வுகள்}
<ul>
{ஒவ்வொரு:நிகழ்வுகளும் நிகழ்வாக}
    <li>பெறுநர் {event.what} {if:event.what == "archive"}காப்பகம்{else}கோப்பை {event.what_name}{endif} {datetime:event.when} அன்று பதிவிறக்கம் செய்தார்</li>
{else}
</ul>
{வேறு}
<p>
    பதிவிறக்கங்கள் இல்லை
</p>
{endif}

<p>
    கூடுதல் விவரங்களை <a href="{transfer.link}">{transfer.link}</a> இல் காணலாம்
</p>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>