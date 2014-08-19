subject: {TITLE} - {cfg:site_name} : {date}

{alternative:plain}

Dear Sir or Madam,

Here you have transfer that have been closed:

Transfers
ID: {transferid}
Name: {transfername}
Message: {transfermessage}

Best regards,
{cfg:siteName}

{alternative:html}

<p>Dear Sir or Madam,</p>
<p>Here you have transfer that have been closed:</p>
<br/>
<table>
    <thead>
        <tr>
            <th colspan="2" style='text-align: center'> Transfer</th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <td>ID</td>
        <td>{transferid}</td>
    </tr>
    <tr>
        <td>Name</td>
        <td>{transfername}</td>
    </tr>
    <tr>
        <td>Message</td>
        <td>{transfermessage}</td>
    </tr>
    </tbody>
</table>

<br/>
<p>Best regards,<br />
{cfg:site_name}</p>
