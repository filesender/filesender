<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
பொருள்: விருந்தினர் வவுச்சர் பெறப்பட்டது
தலைப்பு: {guest.subject}

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

{cfg:site_name}க்கான அணுகலை வழங்கும் வவுச்சரை கீழே காணவும். இந்த வவுச்சரைப் பயன்படுத்தி ஒரு செட் கோப்புகளைப் பதிவேற்றலாம் மற்றும் ஒரு குழுவினருக்குப் பதிவிறக்கம் செய்யலாம்.

வழங்குபவர்: {guest.user_email}
வவுச்சர் இணைப்பு: {guest.upload_link}

{if:guest.does_not_expire}
இந்த வவுச்சர் காலாவதியாகாது.
{வேறு}
வவுச்சர் {date:guest.expires} வரை கிடைக்கும், அதன் பிறகு அது தானாகவே நீக்கப்படும்.
{endif}

{if:guest.message} {guest.user_email} அனுப்பிய தனிப்பட்ட செய்தி: {guest.message}{endif}

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    <a href="{cfg:site_url}">{cfg:site_name}</a>க்கான அணுகலை வழங்கும் வவுச்சரை கீழே காணவும். இந்த வவுச்சரைப் பயன்படுத்தி ஒரு செட் கோப்புகளைப் பதிவேற்றலாம் மற்றும் ஒரு குழுவினருக்குப் பதிவிறக்கம் செய்யலாம்.
</p>

<அட்டவணை விதிகள்="வரிசைகள்">
    <thead>
        <tr>
            <th colspan="2">வவுச்சர் விவரங்கள்</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>வழங்குபவர்</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>வவுச்சர் இணைப்பு</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">இந்த அழைப்பு காலாவதியாகாது</td>
{வேறு}
            <td>வரை செல்லுபடியாகும்</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    {guest.user_email} இலிருந்து தனிப்பட்ட செய்தி:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    வாழ்த்துகள்,<br />
    {cfg:site_name}
</p>