<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: ගොනුව{if:transfer.files>1}s{endif} බාගත කිරීම සඳහා තිබේ
විෂය: {transfer.subject}

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

පහත {if:transfer.files>1}ගොනු ඇති{else}ගොනුව{endif} {cfg:site_name} වෙත {transfer.user_email} මගින් උඩුගත කර ඇති අතර ඔබට {if:transfer.files> බාගත කිරීමට අවසර ලබා දී ඇත 1}ඔවුන්ගේ{else}එහි{endif} අන්තර්ගතය:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

බාගත කිරීමේ සබැඳිය: {recipient.download_link}

ගනුදෙනුව {date:transfer.expires} දක්වා පවතින අතර ඉන් පසුව එය ස්වයංක්‍රීයව මැකෙනු ඇත.

{if:transfer.message || transfer.subject}
{transfer.user_email} වෙතින් පුද්ගලික පණිවිඩය: {transfer.subject}

{transfer.message}
{endif}

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහතමයා හෝ මැතිණියනි,
</p>

<p>
    පහත {if:transfer.files>1}ගොනු ඇත{else}ගොනුව{endif} <a href= විසින් <a href="{cfg:site_url}">{cfg:site_name}</a> වෙත උඩුගත කර ඇත "mailto:{transfer.user_email}">{transfer.user_email}</a> සහ ඔබට {if:transfer.files>1}ඔවුන්ගේ{else}එහි{endif} අන්තර්ගතය බාගත කිරීමට අවසර ලබා දී ඇත.
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
            <td>මාරු ප්‍රමාණය</td>
            <td>{size:transfer.size}</td>
        </tr>
                {endif}
        <tr>
            <td>කල් ඉකුත් වීමේ දිනය</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>සබැඳිය බාගන්න</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    {transfer.user_email} වෙතින් පුද්ගලික පණිවිඩය:
</p>
<p class="message">
    {transfer.message}
</p>
        {endif}

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>
