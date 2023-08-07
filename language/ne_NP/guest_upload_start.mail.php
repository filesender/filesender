<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: अतिथि फाइलहरू अपलोड गर्न सुरु गर्नुहोस्

{alternative:plain}

प्रिय महोदय वा महोदया,

निम्न अतिथिले तपाईंको भाउचरबाट फाइलहरू अपलोड गर्न थाले:

अतिथि: {guest.email}
भाउचर लिङ्क: {cfg:site_url}?s=upload&vid={guest.token}

यो भाउचर {date:guest.expires} सम्म उपलब्ध छ त्यसपछि यो स्वतः मेटिनेछ।

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    निम्न अतिथिले तपाईंको भाउचरबाट फाइलहरू अपलोड गर्न थाले:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">भाउचर विवरणहरू</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>अतिथि</td>
            <td><a href="mailto:{guest.email}">{guest.email}</a></td>
        </tr>
        <tr>
            <td>भाउचर लिङ्क</td>
            <td><a href="{cfg:site_url}?s=upload&vid={guest.token}">{cfg:site_url}?s=upload&vid={guest.token}</a></td>
        </tr>
        <tr>
            <td>सम्म मान्य</td>
            <td>{date:guest.expires}</td>
        </tr>
    </tbody>
</table>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>