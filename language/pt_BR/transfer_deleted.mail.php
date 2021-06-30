<?php 
// WARNING, this is a read only file created by import scripts
// WARNING
// WARNING,  Changes made to this file will be clobbered
// WARNING
// WARNING,  Please make changes on poeditor instead of here
// 
// 
?>
subject: {if:transfer.files>1}Arquivos não mais disponíveis{else}Arquivo não mais disponível{endif} para download
subject: ({if:transfer.files>1}arquivos não mais disponíveis{else}arquivo não mais disponível{endif}) {transfer.subject}

{alternative:plain}

Prezado Senhor(a),

A transferência n° {transfer.id} foi excluída de {cfg:site_name} pelo remetente ({transfer.user_email}) e não está mais disponível para download.

Atenciosamente,
{cfg:site_name}

{alternative:html}

<p>
    Prezado Senhor(a),
</p>

<p>
    A transferência n° {transfer.id} foi excluída de <a href="{cfg:site_url}">{cfg:site_name}</a> pelo remetente (<a href="mailto:{transfer.user_email}">{transfer.user_email}</a>) e não está mais disponível para download.
</p>

<p>
    Atenciosamente,<br />
    {cfg:site_name}
</p>
