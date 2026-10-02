<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
பொருள்: சேமிப்பக பயன்பாட்டு எச்சரிக்கை

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

{cfg:site_name} இன் சேமிப்பக பயன்பாடு எச்சரிக்கையாக உள்ளது:

{ஒவ்வொன்றும்:எச்சரிக்கைகள் எச்சரிக்கையாக}
  - {warning.filesystem} ({size:warning.total_space}) இல் {size:warning.free_space} மட்டுமே உள்ளது ({warning.free_space_pct}%)
{endeach}

கூடுதல் விவரங்களை {cfg:site_url} இல் காணலாம்

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    {cfg:site_name} இன் சேமிப்பக பயன்பாடு எச்சரிக்கையாக உள்ளது:
</p>

<ul>
{ஒவ்வொன்றும்:எச்சரிக்கைகள் எச்சரிக்கையாக}
    <li>{warning.filesystem} ({size:warning.total_space}) இல் {size:warning.free_space} மட்டுமே உள்ளது ({warning.free_space_pct}%)</li>
{endeach}
</ul>

<p>
    கூடுதல் விவரங்களை <a href="{cfg:site_url}">{cfg:site_url}</a> இல் காணலாம்
</p>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>