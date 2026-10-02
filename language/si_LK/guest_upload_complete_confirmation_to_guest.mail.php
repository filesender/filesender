<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: අමුත්තා ගොනු උඩුගත කිරීම අවසන් විය

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

පහත අමුත්තා ආගන්තුක වවුචරයක් භාවිතයෙන් ගොනු උඩුගත කිරීම අවසන් විය:

අමුත්තා: {guest.email}
වවුචර් සබැඳිය: {cfg:site_url}?s=upload&vid={guest.token}

වවුචරය {date:guest.expires} වන තෙක් ලබා ගත හැකි අතර ඉන් පසුව එය ස්වයංක්‍රීයව මැකෙනු ඇත.

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
පහත අමුත්තා ආගන්තුක වවුචරයක් භාවිතයෙන් ගොනු උඩුගත කිරීම අවසන් විය:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">වවුචර් විස්තර</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ආගන්තුකයා</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>වවුචර් සබැඳිය</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>වලංගු කාළය</td> 
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>
',