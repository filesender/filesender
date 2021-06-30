<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Arquivos disponíveis{else}Arquivo disponível{endif} para download
subject: {transfer.subject}

{alternative:plain}

Prezado(a),

Você recebeu uma permissão de {transfer.user_email} para fazer download de um arquivo por meio do {cfg:site_name}. Esse serviço permite que você receba um arquivo grande, sem sobrecarregar o limite de armazenamento da sua caixa postal. Além disso, você pode escolher o melhor momento e local para baixar o arquivo, dentro da data de validade estabelecida nas especificações abaixo:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link para download: {recipient.download_link}

A transação estará disponível até {date:transfer.expires} após o qual será, automaticamente, excluída.

{if:transfer.message || transfer.subject}
Mensagem pessoal de {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado(a),
</p>

<p>
    Você recebeu uma permissão de {transfer.user_email} para download de um arquivo por meio do {cfg:site_name}. Esse serviço permite que você receba um arquivo grande, sem sobrecarregar o limite de armazenamento da sua caixa postal. Além disso, você pode escolher o melhor momento e local para baixar o arquivo, dentro da data de validade estabelecida nas especificações abaixo:
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalhes da transação</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Arquivo{if:transfer.files>1}s{endif}</td>
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
            <td>Tamanho da transferência</td>
            <td>{size:transfer.size}</td>
        </tr>
        {endif}
        <tr>
            <td>Data de validade</td>
            <td>{date:transfer.expires}</td>
        </tr>
        <tr>
            <td>Link para download</td>
            <td><a href="{recipient.download_link}">{recipient.download_link}</a></td>
        </tr>
    </tbody>
</table>

{if:transfer.message}
<p>
    Mensagem pessoal de {transfer.user_email}:
</p>
<p class="message">
    {transfer.message}
</p>
{endif}

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
