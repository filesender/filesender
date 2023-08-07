<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
தலைப்பு: கோப்புகளைப் பதிவேற்ற விருந்தினர் முடிந்தது

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

விருந்தினர் வவுச்சரைப் பயன்படுத்தி பின்வரும் விருந்தினர் கோப்புகளைப் பதிவேற்ற முடிந்தது:

விருந்தினர்: {guest.email}
வவுச்சர் இணைப்பு: {cfg:site_url}?s=upload&vid={guest.token}

வவுச்சர் {date:guest.expires} வரை கிடைக்கும், அதன் பிறகு அது தானாகவே நீக்கப்படும்.

வாழ்த்துகள்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
விருந்தினர் வவுச்சரைப் பயன்படுத்தி பின்வரும் விருந்தினர் கோப்புகளைப் பதிவேற்ற முடிந்தது:
</p>

<அட்டவணை விதிகள்="வரிசைகள்">
    <thead>
        <tr>
            <th colspan="2">வவுச்சர் விவரங்கள்</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>விருந்தினர்</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>வவுச்சர் இணைப்பு</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>வரை செல்லுபடியாகும்</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>
',