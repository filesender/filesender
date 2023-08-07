<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: रसिद डाउनलोड

{alternative:plain}

प्रिय महोदय वा महोदया,

{if:files>1}धेरै फाइलहरू{else}एउटा फाइल{endif} तपाईंले अपलोड गर्नुभएको {if:files>1}छ{else}{endif} {cfg:site_name} बाट {recipient.email} द्वारा डाउनलोड गरिएको छ :

{if:files>1}{each:files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{files.first().path} ({size:files.first().size})
{endif}

तपाईं आफ्नो फाइलहरू हेर्न र स्थानान्तरण पृष्ठमा विस्तृत डाउनलोड तथ्याङ्कहरू हेर्न सक्नुहुन्छ {files.first().transfer.link}।

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    {if:files>1}धेरै फाइलहरू{else}एउटा फाइल{endif} तपाईंले अपलोड गर्नुभएको {if:files>1}छ{else}{endif} {cfg:site_name} बाट {recipient.email} द्वारा डाउनलोड गरिएको छ :
</p>

<p>
    {if:files>1}
    <ul>
        {each:files as file}
            <li>{file.path} ({size:file.size})</li>
        {endeach}
    </ul>
    {else}
    {files.first().path} ({size:files.first().size})
    {endif}
</p>

<p>
    तपाईं आफ्नो फाइलहरू हेर्न र स्थानान्तरण पृष्ठमा विस्तृत डाउनलोड तथ्याङ्कहरू हेर्न सक्नुहुन्छ <a href="{files.first().transfer.link}">{files.first().transfer.link}</a>।
</p>

<p>
    शुभेक्षा सहित,<br />
    {cfg:site_name}
</p>