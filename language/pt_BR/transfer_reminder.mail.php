<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: (lembrete) {if:transfer.files>1}Arquivos disponíveis{else}Arquivo disponível{endif} para download
subject: (lembrete) {transfer.subject}

{alternative:plain}

Prezado Senhor(a),

Este é um lembrete, {if:transfer.files>1}os seguintes arquivos foram enviados{else}o seguinte arquivo foi enviado{endif} para {cfg:site_name} por {transfer.user_email} e você recebeu permissão para fazer o download de {if:transfer.files>1}seus conteúdos{else}seu conteúdo{endif}:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Link para download: {recipient.download_link}

A transação está disponível até {date:transfer.expires} após o qual será automaticamente excluída..

{if:transfer.message || transfer.subject}
Mensagem pessoal de {transfer.user_email}: {transfer.subject}

{transfer.message}
{endif}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado Senhor(a),
</p>

<p>
    Este é um lembrete, os {if:transfer.files>1}os seguintes arquivos foram enviados{else}o seguinte arquivo foi enviado{endif} para <a href="{cfg:site_url}">{cfg:site_name}</a> por <a href="mailto:{transfer.user_email}">{transfer.user_email}</a> e você recebeu permissão para fazer o download de {if:transfer.files>1}seus conteúdos{else}seu conteúdo{endif}.
</p>

<table rules="rows">
    <thead>
        <tr>
            <th colspan="2">Detalhes da transferência</th>
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
    <span class="subject">{transfer.subject}</span>
    {transfer.message}
</p>
{endif}

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
