<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

உங்கள் பெறுநர்களில் ஒன்று அல்லது அதற்கு மேற்பட்டவர்கள் உங்கள் செய்திகளைப் பெறத் தவறிவிட்டனர்:

{ஒவ்வொன்றும்:பவுன்ஸ் என துள்ளுகிறது}
{if:bounce.target_type=="பெறுநர்"}
  - #{bounce.target.transfer.id} பெறுநரை {bounce.target.email} {datetime:bounce.date} அன்று மாற்றவும் ({bounce.target.transfer.link})
{endif}{if:bounce.target_type=="Guest"}
  - விருந்தினர் {bounce.target.email} அன்று {datetime:bounce.date}
{endif}
{endeach}

கூடுதல் விவரங்களை {cfg:site_url} இல் காணலாம்

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    உங்கள் பெறுநர்களில் ஒன்று அல்லது அதற்கு மேற்பட்டவர்கள் உங்கள் செய்திகளைப் பெறத் தவறிவிட்டனர்:
</p>

<ul>
{ஒவ்வொன்றும்:பவுன்ஸ் என துள்ளுகிறது}
    <li>
    {if:bounce.target_type=="பெறுநர்"}
        <a href="{bounce.target.transfer.link}">பரிமாற்றம் #{bounce.target.transfer.id}</a> {bounce.target.email} பெறுநரை {datetime:bounce.date} அன்று
    {endif}{if:bounce.target_type=="Guest"}
        {datetime:bounce.date} அன்று விருந்தினர் {bounce.target.email}
    {endif}
    </li>
{endeach}
</ul>

<p>
    கூடுதல் விவரங்களை <a href="{cfg:site_url}">{cfg:site_url}</a> இல் காணலாம்
</p>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>