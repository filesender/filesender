<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (रिमाइन्डर) फाइल{if:transfer.files>1}s{endif} डाउनलोडका लागि उपलब्ध
subject: (रिमाइन्डर) {transfer.subject}

{alternative:plain}

प्रिय महोदय वा महोदया,

यो एउटा रिमाइन्डर हो, निम्न {if:transfer.files>1}फाइलहरू{else}फाइल{endif} {cfg:site_name} मा {transfer.user_email} द्वारा अपलोड गरिएको छ र तपाईंलाई डाउनलोड गर्न अनुमति दिइएको छ {if :transfer.files>1}तिनीहरूको{else}यसको{endif} सामग्री :

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

डाउनलोड लिंक: {recipient.download_link}

लेनदेन {date:transfer.expires} सम्म उपलब्ध छ जुन समय पछि यो स्वतः मेटिनेछ।

{if:transfer.message || transfer.subject}
{transfer.user_email} बाट व्यक्तिगत सन्देश: {transfer.subject}

{transfer.message}
{endif}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    यो एउटा रिमाइन्डर हो, निम्न {if:transfer.files>1}फाइलहरू{else}फाइल{endif} <a href="{cfg:site_url}">{cfg:site_name}</a> मा अपलोड गरिएको छ। <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> द्वारा र तपाईलाई {if:transfer.files>1}उनीहरूको{else}यसको{endif} सामग्रीहरू डाउनलोड गर्न अनुमति दिइएको छ। ।
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">लेनदेन विवरण</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>फाइल{if:transfer.files>1}s{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files as file}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                {else}
                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        {if:transfer.files>1}
        <tr>
            <td> स्थानान्तरण आकार</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>समाप्ति मिति</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td> लिङ्क डाउनलोड गर्नुहोस्</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    {transfer.user_email} बाट व्यक्तिगत सन्देश:
</p>
<p class="message">
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>