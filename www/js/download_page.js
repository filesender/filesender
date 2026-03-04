// JavaScript Document

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

$(function() {
    var page = $('.download_page');
    if(!page.length) return;

    const copyToClipboard = (value) => {
        navigator.clipboard.writeText(value).then((x) => {
            filesender.ui.notify('info', lang.tr('copied_to_clipboard'));
        }).catch((e) => {
            console.error(e);
            filesender.ui.notify('error', lang.tr('copied_to_clipboard_error'));
        });
    }

    // Get recipient token
    var m = window.location.search.match(/token=([0-9a-f-]+)/);
    var token = m[1];
    var $this = this;

    page.find('.toggle-select-all').trigger('mousedown');

    filesender.client.setPage( $(this) );
    filesender.client.setToken( token );
    filesender.client.bindDownloadButton('.file .download');
    filesender.client.bindDownloadArchive();
    filesender.client.bindFileCheckButtons();

    $('#check-all').click();

    page.find('.script-links').on('click', function() {
        var encrypted = page.find('.file').attr('data-encrypted') == "1";

        if (encrypted) {
            var popup = filesender.ui.wideInfoPopup(lang.tr('script_download_title'));
            var tabsHtml = `
              <div id="downloadTabs">
                <ul>
                  <li><a href="#fsclitab">FileSender CLI Client</a></li>
                </ul>
                <div id="fsclitab"></div>
              </div>`;
            $(tabsHtml).appendTo(popup);
            $("#downloadTabs").tabs();
            var fscli = 'python3 filesender.py -d "'+window.location.href+'" -e "<password>"';
            var fsclip = $('<p>'+lang.tr('script_download_fscli')+'</p>').appendTo($('#fsclitab'));
            var fsclipre = $('<pre />').text(fscli).appendTo($('#fsclitab'));
            var fscliactions = $('<div class="actions" />').appendTo($('#fsclitab'));
            var fscliclipboard = $('<button type="button" class="fs-button">').html('<i class="fa fa-copy"></i> '+lang.tr('copy')).appendTo(fscliactions);
            $('<p>&nbsp;</p>').prependTo(fscliclipboard);
            fscliclipboard.on('click', function(e) {
                copyToClipboard(fscli);
            });
            return true;
        }

        var ids = [];
        page.find('.file[data-selected="1"]').each(function() {
            ids.push($(this).attr('data-id'));
        });
        if(!ids.length) { // No files selected, supose we want all of them
            page.find('.file').each(function() {
                ids.push($(this).attr('data-id'));
            });
        }
        var curlscript="";
        var wgetscript="";
        var links="";
        for(id in ids) {
            url=location.origin+"/download.php?token="+filesender.client.token+"&files_ids="+ids[id];
            curlscript+="curl -O -L -J \""+url+"\"\n";
            wgetscript+="wget --content-disposition \""+url+"\"\n";
            links+=url+"\n";
        }

        var popup = filesender.ui.wideInfoPopup(lang.tr('script_download_title'));
        //popup.css('overflow','hidden');

        var tabsHtml = `
          <div id="downloadTabs">
            <ul>
              <li><a href="#curltab">Curl</a></li>
              <li><a href="#wgettab">Wget</a></li>
              <li><a href="#linkstab">Links</a></li>
              <li><a href="#fsclitab">FileSender CLI Client</a></li>
            </ul>
            <div id="curltab"></div>
            <div id="wgettab"></div>
            <div id="linkstab"></div>
            <div id="fsclitab"></div>
          </div>`;
        $(tabsHtml).appendTo(popup);
        $("#downloadTabs").tabs();

        var curlpre = $('<pre />').text(curlscript).appendTo($('#curltab'));
        var curlactions = $('<div class="actions" />').appendTo($('#curltab'));
        var curlclipboard = $('<button type="button" class="fs-button">').html('<i class="fa fa-copy"></i> '+lang.tr('copy')).appendTo(curlactions);
        $('<p>&nbsp;</p>').prependTo(curlclipboard);
        curlclipboard.on('click', function(e) {
            copyToClipboard(curlscript);
        });

        var wgetpre = $('<pre />').text(wgetscript).appendTo($('#wgettab'));
        var wgetactions = $('<div class="actions" />').appendTo($('#wgettab'));
        var wgetclipboard = $('<button type="button" class="fs-button">').html('<i class="fa fa-copy"></i> '+lang.tr('copy')).appendTo(wgetactions);
        $('<p>&nbsp;</p>').prependTo(wgetclipboard);
        wgetclipboard.on('click', function(e) {
            copyToClipboard(wgetscript);
        });

        var linkspre = $('<pre />').text(links).appendTo($('#linkstab'));
        var linksactions = $('<div class="actions" />').appendTo($('#linkstab'));
        var linksclipboard = $('<button type="button" class="fs-button">').html('<i class="fa fa-copy"></i> '+lang.tr('copy')).appendTo(linksactions);
        $('<p>&nbsp;</p>').prependTo(linksclipboard);
        linksclipboard.on('click', function(e) {
            copyToClipboard(links);
        });

        var fscli = 'python3 filesender.py -d "'+window.location.href+'"';
        var fsclip = $('<p>'+lang.tr('script_download_fscli')+'</p>').appendTo($('#fsclitab'));
        var fsclipre = $('<pre />').text(fscli).appendTo($('#fsclitab'));
        var fscliactions = $('<div class="actions" />').appendTo($('#fsclitab'));
        var fscliclipboard = $('<button type="button" class="fs-button">').html('<i class="fa fa-copy"></i> '+lang.tr('copy')).appendTo(fscliactions);
        $('<p>&nbsp;</p>').prependTo(fscliclipboard);
        fscliclipboard.on('click', function(e) {
            copyToClipboard(fscli);
        });

    });
});
