<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (रिमाइन्डर) अतिथि भाउचर प्राप्त भयो
subject: (रिमाइन्डर) {guest.subject}

{alternative:plain}

प्रिय महोदय वा महोदया,

यो एउटा रिमाइन्डर हो, कृपया तल एउटा भाउचर फेला पार्नुहोस् जसले {cfg:site_name} लाई पहुँच दिन्छ। तपाईंले फाइलहरूको एउटा सेट अपलोड गर्न र मानिसहरूको समूहलाई डाउनलोड गर्नका लागि उपलब्ध गराउन यो भाउचर प्रयोग गर्न सक्नुहुन्छ।

जारीकर्ता: {guest.user_email}
भाउचर लिङ्क: {guest.upload_link}

यो भाउचर {date:guest.expires} सम्म उपलब्ध छ त्यसपछि यो स्वतः मेटिनेछ।

{if:guest.message} {guest.user_email} बाट व्यक्तिगत सन्देश: {guest.message}{endif}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    यो एउटा रिमाइन्डर हो, कृपया <a href="{cfg:site_url}">{cfg:site_name}</a> मा पहुँच प्रदान गर्ने भाउचर तल फेला पार्नुहोस्। तपाईंले फाइलहरूको एउटा सेट अपलोड गर्न र मानिसहरूको समूहलाई डाउनलोड गर्नका लागि उपलब्ध गराउन यो भाउचर प्रयोग गर्न सक्नुहुन्छ।
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">भाउचर विवरणहरू</th>
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
            <td>सम्म मान्य</td>
            <td>{date:guest.expires}</td>
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