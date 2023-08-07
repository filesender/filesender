<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
தலைப்பு: {if:target_type=="recipient"}பெறுநர்{endif}{if:target_type=="guest"}விருந்தினர்{endif}#{target_id} {target.email} இடமிருந்து கருத்து

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

{if:target_type=="recipient"}பெறுநர்{endif}{if:target_type=="guest"}விருந்தினர்{endif}#{target_id} {target.email} இடமிருந்து மின்னஞ்சல் கருத்தைப் பெற்றோம், அதை இணைக்கவும்.

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    {if:target_type=="recipient"}பெறுநர்{endif}{if:target_type=="guest"}விருந்தினர்{endif}#{target_id} {target.email} இடமிருந்து மின்னஞ்சல் கருத்தைப் பெற்றோம், அதை இணைக்கவும்.
</p>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>