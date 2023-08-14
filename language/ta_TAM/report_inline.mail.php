<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
தலைப்பு: {target.type} #{target.id} பற்றிய அறிக்கை

{மாற்று: வெற்று}

அன்புள்ள ஐயா அல்லது அம்மையீர்,

உங்கள் {target.type} பற்றிய அறிக்கை இதோ:

{target.type} எண் : {target.id}

{if:target.type == "பரிமாற்றம்"}
இந்த பரிமாற்றத்தின் மொத்த அளவு {size:transfer.size} உடன் {transfer.files} கோப்புகள் உள்ளன.

இந்தப் பரிமாற்றம் {date:transfer.expires} வரை இருக்கும்/இருக்கிறது.

இந்தப் பரிமாற்றம் {transfer.recipients} பெறுநர்களுக்கு அனுப்பப்பட்டது.
{endif}
{if:target.type == "கோப்பு"}
இந்தக் கோப்பு {file.path} எனப் பெயரிடப்பட்டுள்ளது, அதன் அளவு {size:file.size} மற்றும் {date:file.transfer.expires} வரை இருக்கும்.
{endif}
{if:target.type == "பெறுநர்"}
இந்த பெறுநருக்கு மின்னஞ்சல் முகவரி {recipient.email} உள்ளது மற்றும் அது {date:recipient.expires} வரை செல்லுபடியாகும்.
{endif}

இடமாற்றம் என்ன ஆனது என்பதற்கான முழு பதிவு இங்கே:

{raw:content.plain}

அன்புடன்,
{cfg:site_name}

{மாற்று:html}

<p>
    அன்புள்ள ஐயா அல்லது அம்மையீர்,
</p>

<p>
    உங்கள் {target.type}:<br /><br /> பற்றிய அறிக்கை இதோ
    
    {target.type} எண் : {target.id}<br /><br />
    
    {if:target.type == "பரிமாற்றம்"}
    இந்த பரிமாற்றத்தின் மொத்த அளவு {size:transfer.size} உடன் {transfer.files} கோப்புகள் உள்ளன.<br /><br />
    
    இந்தப் பரிமாற்றம் {date:transfer.expires} வரை இருக்கும்.<br /><br />
    
    இந்தப் பரிமாற்றம் {transfer.recipients} பெறுநர்களுக்கு அனுப்பப்பட்டது.
    {endif}
    {if:target.type == "கோப்பு"}
    இந்தக் கோப்பு {file.path} எனப் பெயரிடப்பட்டுள்ளது, அதன் அளவு {size:file.size} மற்றும் {date:file.transfer.expires} வரை இருக்கும்.
    {endif}
    {if:target.type == "பெறுநர்"}
    இந்த பெறுநருக்கு மின்னஞ்சல் முகவரி {recipient.email} உள்ளது மற்றும் அது {date:recipient.expires} வரை செல்லுபடியாகும்.
    {endif}
</p>

<p>
    இடமாற்றம் என்ன ஆனது என்பதற்கான முழு பதிவு இங்கே:
    <table class="auditlog" rules="row">
        <thead>
            <th>தேதி</th>
            <th>நிகழ்வு</th>
            <th>IP முகவரி</th>
        </thead>
        <tbody>
            {raw:content.html}
        </tbody>
    </table>
</p>

<p>வணக்கங்கள்,<br/>
{cfg:site_name}</p>