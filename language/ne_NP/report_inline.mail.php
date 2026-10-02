<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {target.type} #{target.id} बारे रिपोर्ट गर्नुहोस्

{alternative:plain}

प्रिय महोदय वा महोदया,

यहाँ तपाईंको {target.type} बारे रिपोर्ट छ:

{target.type} नम्बर : {target.id}

{if:target.type == "Transfer"}
यस स्थानान्तरणमा {size:transfer.size} को समग्र आकारका {transfer.files} फाइलहरू छन्।

यो स्थानान्तरण {date:transfer.expires} सम्म उपलब्ध छ/छ।

यो स्थानान्तरण {transfer.recipients} प्रापकहरूलाई पठाइएको थियो।
{endif}
{if:target.type == "File"}
यस फाइललाई {file.path} नाम दिइएको छ, यसको आकार {size:file.size} छ र यो {date:file.transfer.expires} सम्म उपलब्ध छ।
{endif}
{if:target.type == "Recipient"}
यो प्रापकको इमेल ठेगाना {recipient.email} छ र यो {date:recipient.expires} सम्म मान्य छ।
{endif}

यहाँ स्थानान्तरणमा के भयो पूर्ण लग छ:

{raw:content.plain}

शुभेक्षा सहित,
{cfg:site_name}

{alternative:html}

<p>
    प्रिय महोदय वा महोदया,
</p>

<p>
    यहाँ तपाईको {target.type} बारे रिपोर्ट छ:<br /><br />
    
    {target.type} नम्बर : {target.id}<br /><br />
    
    {if:target.type == "Transfer"}
    यस स्थानान्तरणमा {size:transfer.size} को समग्र आकारका {transfer.files} फाइलहरू छन्।<br /><br />
    
    यो स्थानान्तरण {date:transfer.expires} सम्म उपलब्ध छ/छ।<br /><br />
    
    यो स्थानान्तरण {transfer.recipients} प्रापकहरूलाई पठाइएको थियो।
    {endif}
    {if:target.type == "File"}
    यस फाइललाई {file.path} नाम दिइएको छ, यसको आकार {size:file.size} छ र यो {date:file.transfer.expires} सम्म उपलब्ध छ।
    {endif}
    {if:target.type == "Recipient"}
    यो प्रापकको इमेल ठेगाना {recipient.email} छ र यो {date:recipient.expires} सम्म मान्य छ।
    {endif}
</p>

<p>
    यहाँ स्थानान्तरणमा के भयो पूर्ण लग छ:
    <table class="auditlog" rules="rows">
        <thead>
            <th>मिति</th>
            <th>घटना</th>
            <th>IP ठेगाना</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>शुभेक्षा सहित,<br/>
{cfg:site_name}</p>