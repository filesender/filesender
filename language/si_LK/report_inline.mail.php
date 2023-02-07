<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: {target.type} #{target.id} ගැන වාර්තා කරන්න

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

මෙන්න ඔබේ {target.type} පිළිබඳ වාර්තාව:

{target.type} අංකය : {target.id}

{if:target.type == "Transfer"}
මෙම හුවමාරුව {size:transfer.size} හි සමස්ත ප්‍රමාණයෙන් {transfer.files} ගොනු ඇත.

මෙම මාරුව {date:transfer.expires} තෙක් පවතී/පවතියි.

මෙම මාරුව ලබන්නන් {transfer.recipients} වෙත යවන ලදී.
{endif}

{if:target.type == "File"}
මෙම ගොනුව {file.path} ලෙස නම් කර ඇති අතර, එහි විශාලත්වය {size:file.size} වන අතර එය {date:file.transfer.expires} දක්වා පවතී.
{endif}

{if:target.type == "Recipient"}
මෙම ලබන්නාට ඊමේල් ලිපිනය {recipient.email} ඇති අතර එය {date:recipient.expires} දක්වා වලංගු වේ.
{endif}

මාරුවීමට සිදු වූ දේ පිළිබඳ සම්පූර්ණ සටහන මෙන්න:

{raw:content.plain}

සුභ පතමින්,
{cfg:site_name}

{alternative:html}
<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    මෙන්න ඔබේ {target.type}:<br /><br /> පිළිබඳ වාර්තාව
    
    {target.type} අංකය : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    මෙම හුවමාරුවෙහි සමස්ත ප්‍රමාණය {size:transfer.size} සමග {transfer.files} ගොනු ඇත.<br /><br />
    
    මෙම හුවමාරුව {date:transfer.expires} තෙක් පවතී/පවතියි.<br /><br />
    
    මෙම මාරුව ලබන්නන් {transfer.recipients} වෙත යවන ලදී.
   {endif}
       {if:target.type == "File"}
    මෙම ගොනුව {file.path} ලෙස නම් කර ඇති අතර, එහි විශාලත්වය {size:file.size} වන අතර එය {date:file.transfer.expires} දක්වා පවතී.
     {endif}
     {if:target.type == "Recipient"}
    මෙම ලබන්නාට ඊමේල් ලිපිනය {recipient.email} ඇති අතර එය {date:recipient.expires} දක්වා වලංගු වේ.
     {endif}
</p>

<p>
    මාරුවීමට සිදු වූ දේ පිළිබඳ සම්පූර්ණ සටහන මෙන්න:
    <table class="auditlog" rules="rows">
        <thead>
            <th>දිනය</th>
            <th>සිදුවීම</th>
            <th>IP ලිපිනය</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>සුභ පැතුම්,<br/>
{cfg:site_name}</p>