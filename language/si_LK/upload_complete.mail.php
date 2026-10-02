<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: ගොනුව{if:transfer.files>1}s{endif} සාර්ථකව උඩුගත කරන ලදී

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

පහත {if:transfer.files>1}ගොනු ඇත{else}ගොනුව{endif} {cfg:site_name} වෙත සාර්ථකව උඩුගත කර ඇත.

මෙම ගොනු පහත සබැඳිය භාවිතයෙන් බාගත කළ හැක: {transfer.download_link}

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

වැඩි විස්තර: {transfer.link}

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    පහත {if:transfer.files>1}ගොනු ඇත{else}ගොනුව{endif} <a href="{cfg:site_url}">{cfg:site_name}</a> වෙත සාර්ථකව උඩුගත කර ඇත.
</p>

<p>
මෙම ගොනු පහත සබැඳිය භාවිතයෙන් බාගත හැක <a href="{transfer.download_link}">{transfer.download_link}</a>
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">ගනුදෙනු විස්තර</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>ගොනුව{if:transfer.files>1}s{endif}</td>
            <td>
                {if:transfer.files>1}
                <ul>
                    {each:transfer.files ගොනුවක් ලෙස}
                        <li>{file.path} ({size:file.size})</li>
                    {endeach}
                </ul>
                              {else}

                {transfer.files.first().path} ({size:transfer.files.first().size})
                {endif}
            </td>
        </tr>
        <tr>
            <td>ප්‍රමාණය</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>වැඩිදුර තොරතුරු</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>