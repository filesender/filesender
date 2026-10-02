<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
பொருள்: n°{transfer.id} கோப்பு ஏற்றுமதிக்காக தானியங்கி நினைவூட்டல்கள் அனுப்பப்பட்டன

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

{cfg:site_name} ({transfer.link}) இல் உங்கள் பரிமாற்ற n°{transfer.id} இலிருந்து கோப்புகளைப் பதிவிறக்காத பெறுநர்களுக்கு தானியங்கி நினைவூட்டல் அனுப்பப்பட்டது:

{ஒவ்வொருவரும்:பெறுநர்களாக}
  - {பெறுநர் மின்னஞ்சல்}
{endeach}

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    <a href="{transfer.link}">பரிமாற்றம் n°{transfer.id}</a> இலிருந்து <a href="{cfg:site_url}" இல் கோப்புகளைப் பதிவிறக்காத பெறுநர்களுக்கு தானியங்கி நினைவூட்டல் அனுப்பப்பட்டது. >{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {ஒவ்வொருவரும்:பெறுநர்களாக}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>