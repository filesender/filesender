<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: फाइल ढुवानीका लागि स्वचालित रिमाइन्डरहरू पठाइयो n°{transfer.id}

{alternative:plain}

प्रिय महोदय वा महोदया,

{cfg:site_name} ({transfer.link}) मा तपाईंको स्थानान्तरण n�{transfer.id} बाट फाइलहरू डाउनलोड नगर्ने प्रापकहरूलाई स्वचालित रिमाइन्डर पठाइयो।

{each:recipients as recipient}
  - {recipient.email}
{endeach}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    <a href="{cfg:site_url}" मा तपाईंको <a href="{transfer.link}">स्थानान्तरण n°{transfer.id}</a> बाट फाइलहरू डाउनलोड नगर्ने प्रापकहरूलाई स्वचालित रिमाइन्डर पठाइयो। >{cfg:site_name}</a> :
</p>

<p>
    <ul>
     {each:recipients as recipient}
      <li>{recipient.email}</li>
    {endeach}
    </ul>
</p>

<p>
    शुभेक्षा सहित, <br />
    {cfg:site_name}
</p>