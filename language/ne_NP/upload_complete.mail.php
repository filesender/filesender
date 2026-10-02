<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: फाइल{if:transfer.files>1}s{endif} सफलतापूर्वक अपलोड गरियो

{alternative:plain}

प्रिय महोदय वा महोदया,

निम्न {if:transfer.files>1}फाइलहरू{else}फाइलहरू{endif} सफलतापूर्वक {cfg:site_name} मा अपलोड गरिएको छ।

यी फाइलहरू निम्न लिङ्क प्रयोग गरेर डाउनलोड गर्न सकिन्छ: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

थप जानकारी: {transfer.link}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    निम्न {if:transfer.files>1}फाइलहरू{else}फाइलहरू{endif} सफलतापूर्वक <a href="{cfg:site_url}">{cfg:site_name}</a> मा अपलोड गरिएको छ।
</p>

<p>
यी फाइलहरू निम्न लिङ्क प्रयोग गरेर डाउनलोड गर्न सकिन्छ <a href="{transfer.download_link}">{transfer.download_link}</a>
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
        <tr>
            <td>साइज</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>थप जानकारी</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>