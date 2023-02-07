<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: (මතක් කිරීම) ආගන්තුක වවුචරය ලැබී ඇත
විෂය: (මතක් කිරීම) {guest.subject}

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

මෙය සිහිකැඳවීමකි, කරුණාකර {cfg:site_name} වෙත ප්‍රවේශය ලබා දෙන වවුචරයක් පහතින් සොයා ගන්න. ඔබට මෙම වවුචරය භාවිතා කර එක් ගොනු කට්ටලයක් උඩුගත කිරීමට සහ එය පුද්ගලයන් පිරිසකට බාගත කිරීම සඳහා ලබා ගත හැක.

නිකුත් කරන්නා: {guest.user_email}
වවුචර් සබැඳිය: {guest.upload_link}

වවුචරය {date:guest.expires} වන තෙක් ලබා ගත හැකි අතර ඉන් පසුව එය ස්වයංක්‍රීයව මැකෙනු ඇත.

{if:guest.message}පෞද්ගලික පණිවිඩය {guest.user_email} වෙතින්: {guest.message}{endif}

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    මෙය සිහිකැඳවීමකි, කරුණාකර <a href="{cfg:site_url}">{cfg:site_name}</a> වෙත ප්‍රවේශය ලබා දෙන වවුචරයක් පහතින් සොයා ගන්න. ඔබට මෙම වවුචරය භාවිතා කර එක් ගොනු කට්ටලයක් උඩුගත කිරීමට සහ එය පුද්ගලයන් පිරිසකට බාගත කිරීම සඳහා ලබා ගත හැක.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">වවුචර් විස්තර</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>නිකුත් කරන්නා</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>වවුචර් සබැඳිය</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
            <td>වලංගු කාළය</td> 
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    {guest.user_email} වෙතින් පුද්ගලික පණිවිඩය:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>