<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Arquivos carregados{else}Arquivo carregado{endif} com sucesso

{alternative:plain}

Prezado Senhor(a),

{if:transfer.files>1}Os seguintes arquivos foram enviados{else}O seguinte arquivo foi enviado{endif} com sucesso para {cfg:site_name}:

{if:transfer.files>1}{each:transfer.files as file}
  - {file.path} ({size:file.size})
{endeach}{else}
{transfer.files.first().path} ({size:transfer.files.first().size})
{endif}

Mais informações: {transfer.link}

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado Senhor(a),
</p>

<p>
    {if:transfer.files>1}Os seguintes arquivos foram enviados{else}O seguinte arquivo foi enviado{endif} com sucesso para <a href="{cfg:site_url}">{cfg:site_name}</a>:
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
        <tr>
            <td>Tamanho</td>
            <td>{size:transfer.size}</td>
        </tr>
        <tr>
            <td>Mais informações</td>
            <td><a href="{transfer.link}">{transfer.link}</a></td>
        </tr>
    </tbody>
</table>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>