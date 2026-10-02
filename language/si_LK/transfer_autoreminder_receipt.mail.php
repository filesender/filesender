<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
විෂය: n°{transfer.id} ගොනු නැව්ගත කිරීම සඳහා යවන ලද ස්වයංක්‍රීය සිහිකැඳවීම්

{alternative:plain}

හිතවත් මහත්මයා හෝ මැතිණියනි,

{cfg:site_name} ({transfer.link}) මත ඔබේ මාරු n°{transfer.id} වෙතින් ගොනු බාගත නොකළ ලබන්නන් වෙත ස්වයංක්‍රීය සිහිකැඳවීමක් යවන ලදී:

{each:recipients as recipient}
  - {recipient.email}
{endeach}

සුභ පතමින්,
{cfg:site_name}

{alternative:html}

<p>
    හිතවත් මහත්මයා හෝ මැතිණියනි,
</p>

<p>
    <a href="{transfer.link}">මාරු n°{transfer.id}</a> <a href="{cfg:site_url}" මත ගොනු බාගත නොකළ ලබන්නන් වෙත ස්වයංක්‍රීය සිහිකැඳවීමක් යවන ලදී. >{cfg:site_name}</a> :
</p>

<p>
    <ul>
    {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    සුභ පැතුම්,<br />
    {cfg:site_name}
</p>