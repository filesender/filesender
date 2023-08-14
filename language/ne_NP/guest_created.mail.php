<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: अतिथि भाउचर प्राप्त भयो
subject: {guest.subject}

{alternative:plain}

प्रिय महोदय वा महोदया,

कृपया {cfg:site_name} मा पहुँच प्रदान गर्ने भाउचर तल फेला पार्नुहोस्। तपाईंले फाइलहरूको एउटा सेट अपलोड गर्न र मानिसहरूको समूहलाई डाउनलोड गर्नका लागि उपलब्ध गराउन यो भाउचर प्रयोग गर्न सक्नुहुन्छ।

जारीकर्ता: {guest.user_email}
भाउचर लिङ्क: {guest.upload_link}

{if:guest.does_not_expire}
यो भाउचरको म्याद सकिने छैन।
{else}
यो भाउचर {date:guest.expires} सम्म उपलब्ध छ त्यसपछि यो स्वतः मेटिनेछ।
{endif}

{if:guest.message} {guest.user_email} बाट व्यक्तिगत सन्देश: {guest.message}{endif}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    कृपया <a href="{cfg:site_url}">{cfg:site_name}</a> मा पहुँच प्रदान गर्ने भाउचर तल फेला पार्नुहोस्। तपाईंले फाइलहरूको एउटा सेट अपलोड गर्न र मानिसहरूको समूहलाई डाउनलोड गर्नका लागि उपलब्ध गराउन यो भाउचर प्रयोग गर्न सक्नुहुन्छ।
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">भाउचर विवरण</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>जारीकर्ता</td>
            <td><a href="mailto:{guest.user_email}">{guest.user_email}</a></td>
        </tr>
        <tr>
            <td>भाउचर लिङ्क</td>
            <td><a href="{guest.upload_link}">{guest.upload_link}</a></td>
        </tr>
        <tr>
{if:guest.does_not_expire}
            <td colspan="2">यो निमन्त्रणा समाप्त हुँदैन</td>
{else}
            <td>मान्य सम्म</td>
            <td>{date:guest.expires}</td>
{endif}

        </tr>
    </tbody>
</table>

{if:guest.message}
<p>
    {guest.user_email} बाट व्यक्तिगत सन्देश:
</p>
<p class="message">
    {guest.message}
</p>
{endif}

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>