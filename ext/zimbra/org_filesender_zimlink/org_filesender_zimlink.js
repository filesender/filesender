/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURFnet, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *    Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *     names of its contributors may be used to endorse or promote products
 *     derived from this software without specific prior written permission.
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

// Zimlet Class
function org_filesender_zimlink() {}

// Make Zimlet class a subclass of ZmZimletBase class - this makes a Zimlet a Zimlet
org_filesender_zimlink.prototype = new ZmZimletBase();
org_filesender_zimlink.prototype.constructor = org_filesender_zimlink;

var org_filesender_zimlink_instance;

// Initialization stage
org_filesender_zimlink.prototype.init = function() {
    this.servers = [];
    var list = ('' + this.getConfig('serversUrlList')).split(',');
    for(var i=0; i<list.length; i++) {
        var s = list[i].replace(/\s/, '');
        if(s) this.servers.push(s);
    }
    
    org_filesender_zimlink_instance = this;
};

// Detect mail compose view display
org_filesender_zimlink.prototype.onShowView = function(view) {
    // Nothing to do if no HTML5 support
    if(!AjxEnv.supportsHTML5File) return;
    
    // Nothing to do if no servers
    if(!this.servers.length) return;
    
    // Nothing to do except for mail compose view
    if(view.indexOf(ZmId.VIEW_COMPOSE) < 0) return;
    
    // Handle several compose views
    appCtxt.getCurrentView().org_filesender_zimlink = {files: {}, use_filesender: false, filesender_server: null, uploading: false, done_uploading: false};
    
    // Replace original attachment handler with our own, keep the old one to call it when file is small enough
    var zimlet = this;
    var original_submit_attachments = appCtxt.getCurrentView()._submitMyComputerAttachments;
    appCtxt.getCurrentView()._submitMyComputerAttachments = function(files, node, isInline) {
        if(this.org_filesender_zimlink.uploading || this.org_filesender_zimlink.done_uploading) {
            this.showError(zimlet.translate('cannot_add_attachment_anymore'));
            return;
        }
        
        if(!files) files = node.files;
        
        // Accumulate files for size computation and potential sending
        var just_added = [];
        for(var i=0; i<files.length; i++) {
            var id = Dwt.getNextId();
            this.org_filesender_zimlink.files[id] = files[i];
            just_added.push(id);
        }
        
        // Compute size of all attached files
        var size = 0;
        for(var id in this.org_filesender_zimlink.files) {
            var file = this.org_filesender_zimlink.files[id];
            size += file.size || file.fileSize /*Safari*/ || 0;
        }
        
        // Check if max exceeded
        var max_size = appCtxt.get(ZmSetting.MESSAGE_SIZE_LIMIT);
        if(
            (max_size != -1 /* means unlimited */) &&
            (size > max_size)
        ) {
            if(this.org_filesender_zimlink.use_filesender) {
                for(var i=0; i<just_added.length; i++) {
                    var id = just_added[i];
                    zimlet.addAttachmentBubble(this.org_filesender_zimlink.files[id].name, id);
                }
            } else {
                zimlet.popUseFileSenderDlg();
            }
            
        } else {
            // Max not exceeded, run zimbra attachment handler
            original_submit_attachments.apply(appCtxt.getCurrentView(), arguments);
        }
    }; 
    
    if(!appCtxt.getCurrentController().original_send_msg) {
        var original_send_msg = appCtxt.getCurrentController()._sendMsg;
        appCtxt.getCurrentController().original_send_msg = original_send_msg;
        appCtxt.getCurrentController()._sendMsg = function(attId, docIds, draftType, callback, contactId) {
            
            // If done uploading to filesender trigger normal sending
            if(appCtxt.getCurrentView().org_filesender_zimlink.done_uploading) {
                original_send_msg.apply(appCtxt.getCurrentController(), arguments);
                return;
            }
            
            //get draftType to check if the mail is sent
            var isTimed = Boolean(this._sendTime);
            draftType = draftType || (isTimed ? ZmComposeController.DRAFT_TYPE_DELAYSEND : ZmComposeController.DRAFT_TYPE_NONE);
            var isScheduled = draftType == ZmComposeController.DRAFT_TYPE_DELAYSEND;
            var isDraft = (draftType != ZmComposeController.DRAFT_TYPE_NONE && !isScheduled);
            
            //If the mail is sent and filesender is used, then start the upload
            if(!isDraft && appCtxt.getCurrentView().org_filesender_zimlink.use_filesender) {
                //Store arguments in the controller to continue normal sending after the upload
                appCtxt.getCurrentController().original_send_msg_arguments = arguments;
                
                //Start upload
                appCtxt.getCurrentView()._attButton.setEnabled(false);
                org_filesender_zimlink_instance.upload();
                
                return;
            }
            
            // Continue normal sending otherwise
            original_send_msg.apply(appCtxt.getCurrentController(), arguments);
        };
    }
    
    // Catch draft save fileds refresh so we can add filesender files again
    var original_show_forward_field = appCtxt.getCurrentView()._showForwardField;
    appCtxt.getCurrentView()._showForwardField = function(msg, action, incOptions, includeInlineImages, includeInlineAtts) {
        original_show_forward_field.apply(appCtxt.getCurrentView(), arguments);
        
        // If upload done re-disable attachment button
        if(this.org_filesender_zimlink.uploading || this.org_filesender_zimlink.done_uploading)
            appCtxt.getCurrentView()._attButton.setEnabled(false);
        
        // Re-add own html
        if(this.org_filesender_zimlink.use_filesender) {
            zimlet.cleanNativeAttachments();
            zimlet.addOwnAttachmentsHtml();
        }
    };
};

// Remove file from files to upload
org_filesender_zimlink.prototype.removeFile = function(id) {
    var node = document.getElementById(id)
    var parent = node && node.parentNode;
    
    if(parent)
        parent.removeChild(node);
    
    delete appCtxt.getCurrentView().org_filesender_zimlink.files[id];
};

// Translation utility
org_filesender_zimlink.prototype.translate = function(msgid) {
    var translation = this.getMessage(msgid);
    
    return translation ? translation : '{' + msgid + '}';
};

// Ask user wether to use filesender
org_filesender_zimlink.prototype.popUseFileSenderDlg = function() {
    var ctn = [this.translate('use_filesender_dlg_label')];
    
    var srv_sel = null;
    if(this.servers.length > 1) {
        ctn.push(this.translate('use_filesender_dlg_choose_server'));
        srv_sel = Dwt.getNextId();
        ctn.push('<div id="' + srv_sel + '"></div>');
    }
    
    var dialog = this.makeDlg(
        this.translate('use_filesender_dlg_title'),
        {width: 300, height: 150},
        ctn.join(''),
        [DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON]
    );
    
    if(srv_sel) {
        var select = new DwtButton({parent: this.getShell()});
        select.setText(this.servers[0]);
        select.setImage('OrgFilesenderZimlinkIcon');
        var menu = new ZmPopupMenu(select);
        select.setMenu(menu);
        
        for(var i=0; i<this.servers.length; i++) {
            var item = menu.createMenuItem(Dwt.getNextId(), {image: 'OrgFilesenderZimlinkIcon', text: this.servers[i]});
            item.addSelectionListener(new AjxListener(this, function(server) {
                appCtxt.getCurrentView().org_filesender_zimlink.filesender_server = server;
            }, this.servers[i]));
        }
        
        document.getElementById(srv_sel).appendChild(select.getHtmlElement());
    }
    
    this.setDialogButton(
        dialog,
        DwtDialog.OK_BUTTON,
        AjxMsg.yes,
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
            
            zdata.use_filesender = true;
            
            if(!zdata.filesender_server) // Default server not changed or single server
                zdata.filesender_server = this.servers[0];
            
            this.checkFileSenderAuthentication();
        }, dialog)
    );
    
    this.setDialogButton(
        dialog,
        DwtDialog.CANCEL_BUTTON,
        AjxMsg.no,
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            org_filesender_zimlink.showSizeExceededError();
        })
    );
    
    dialog.popup();
};

// Called after choice and checks
org_filesender_zimlink.prototype.useFileSender = function() {
    // Modify "Send" button text
    var btn = appCtxt.getCurrentController()._toolbar.getButton('SEND');
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    zdata.send_btn_text = btn.getText();
    btn.setText(this.translate('upload_to_filesender'));
    
    // Remove already selected native attachments
    this.cleanNativeAttachments();
    
    // Add own attachments html
    this.addOwnAttachmentsHtml();
};

// Clean native attachments except attached mails / vcards
org_filesender_zimlink.prototype.cleanNativeAttachments = function() {
    var view = appCtxt.getCurrentView();
    if(!view._msg || !view._msg._attInfo) return;
    var att_info = view._msg._attInfo;
    
    for(var i=0; i<att_info.length; i++) {
        var att = att_info[i];
        
        if(att.ct == 'message/rfc822') continue;
        if(att.ct == 'text/x-vcard') continue;
        if(att.links.briefcase) continue;
        
        var m = att.link.match(/\sid=['"]([^'"]+)['"]/);
        if(!m) continue;
        
        var id = m[1];
        
        var span = document.getElementById(id).parentNode;
        
        span.parentNode.removeChild(span);
    }
};

// Add own full set attachment html
org_filesender_zimlink.prototype.addOwnAttachmentsHtml = function() {
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    
    for(var id in zdata.files) {
        var done = false;
        if(zdata.transfer_data) {
            for(var i=0; i<zdata.transfer_data.files.length; i++)
                if(zdata.transfer_data.files[i].cid == id)
                    done = zdata.transfer_data.files[i].done;
        }
        
        this.addAttachmentBubble(zdata.files[id].name, id, done);
    }
};

// Called after upload
org_filesender_zimlink.prototype.doneUploading = function() {
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    
    // Flag upload done
    zdata.uploading = false;
    zdata.done_uploading = true;
    
    // Modify "Send" button text back
    var btn = appCtxt.getCurrentController()._toolbar.getButton('SEND');
    btn.setText(zdata.send_btn_text);
    
    // Re-enable toolbar buttons
    appCtxt.getCurrentController()._toolbar.enableAll(true);
    
    // Disable attachment button
    appCtxt.getCurrentView()._attButton.setEnabled(false);
    
    zdata.uploading_popup.popdown();
    zdata.uploading_popup.dispose();
    delete zdata.uploading_popup;
    
    if(zdata.autosend) {
        var ctrl = appCtxt.getCurrentController();
        ctrl.original_send_msg.apply(ctrl, ctrl.original_send_msg_arguments);
        
        return;
    }
    
    var dialog = this.makeDlg(
        this.translate('done_uploading_to_filesender_title'),
        {width: 300, height: 200},
        this.translate('done_uploading_to_filesender_text'),
        [DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON]
    );
    
    this.setDialogButton(
        dialog,
        DwtDialog.OK_BUTTON,
        AjxMsg.yes,
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            var ctrl = appCtxt.getCurrentController();
            ctrl.original_send_msg.apply(ctrl, ctrl.original_send_msg_arguments);
            
        }, dialog)
    );
    
    this.setDialogButton(
        dialog,
        DwtDialog.CANCEL_BUTTON,
        AjxMsg.no
    );
    
    dialog.popup();
};

// Auth data getter
org_filesender_zimlink.prototype.getAuthenticationData = function() {
    var data = this.getUserProperty('authentication_data');
    return data ? JSON.parse(data) : {};
};

// Auth data setter
org_filesender_zimlink.prototype.setAuthenticationData = function(data) {
    this.setUserProperty('authentication_data', JSON.stringify(data), true);
};

// Data handler for user profile response (FileSender JSONP callback)
org_filesender_zimlink.prototype.filesender_user_profile_handler = function(profile) {
    if(!profile.remote_config) {
        this.showError('FileSender does not support remote user access');
        return;
    }
    
    var cfg = profile.remote_config.split('|');
    
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    zdata.remote_config = {url: cfg[0], uid: cfg[1], secret: cfg[2]};
    
    var auth_data = this.getAuthenticationData();
    
    if(!auth_data[zdata.filesender_server]) auth_data[zdata.filesender_server] = {};
    auth_data[zdata.filesender_server].remote_config = zdata.remote_config;
    
    this.setAuthenticationData(auth_data);
    
    if(this.getFileSenderQuota())
        this.useFileSender();
    
    this.fs_auth_dialog.popdown();
    this.fs_auth_dialog.dispose();
};

/*
 * Get info about filesender instance
 */
org_filesender_zimlink.prototype.getFileSenderInfo = function() {
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    
    var fs_url = zdata.filesender_server;
    if(fs_url.substr(-1) != '/') url += '/';
    var info_url = fs_url + 'rest.php/info';
    
    var proxyUrl = [ZmZimletBase.PROXY, AjxStringUtil.urlComponentEncode(info_url)].join('');
    
    var res = AjxRpc.invoke(null, proxyUrl, null, null, true);
    
    if(!res.success) {
        this.showError('Info getter error', res.text);
        return;
    }
    
    var info = JSON.parse(res.text);
    
    var cs = info.upload_chunk_size ? info.upload_chunk_size : (5 * 1024 * 1024);
    info.upload_chunk_size = cs;
    
    zdata.info = info;
    
    return info;
};

/*
 * Get user quota
 */
org_filesender_zimlink.prototype.getFileSenderQuota = function() {
    var data = this.sendActionToJsp(this.getJspUrl('get_quota'));
    
    if(!data || !data.success) {
        this.showError('Quota getter error', data);
        return null;
    }
    
    appCtxt.getCurrentView().org_filesender_zimlink.user_quota = data.response;
    
    return data.response;
};

// Check if we have authentication data for selected FileSender, get it if not
org_filesender_zimlink.prototype.checkFileSenderAuthentication = function() {
    var info = this.getFileSenderInfo();
    
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    var auth_data = this.getAuthenticationData();
    
    if(auth_data[zdata.filesender_server]) {
        // Auth data already known, attach and exit
        appCtxt.getCurrentView().org_filesender_zimlink.remote_config = auth_data[zdata.filesender_server];
        
        if(this.getFileSenderQuota())
            this.useFileSender();
        
        return;
    }
    
    var landing_url = info.url + 'index.php#zimbra_binding';
    
    var user_url = info.url + 'rest.php/user?callback=org_filesender_zimlink_instance.filesender_user_profile_handler';
    
    var domain = info.url.match(/^(https?:\/\/[^/]+)/)[1];
    
    var logon_url = info.logon_url.match(/^\//) ? domain + info.logon_url : info.logon_url;
    
    var popup_url = logon_url.replace(/__target__/, AjxStringUtil.urlComponentEncode(landing_url));
    
    var auth_trigger_btn = new DwtButton({parent: this.getShell()});
    auth_trigger_btn.setText(this.translate('get_filesender_authentication_popup_button'));
    auth_trigger_btn.setImage('Padlock');
    auth_trigger_btn.addSelectionListener(new AjxListener(this, function() {
        org_filesender_zimlink_instance.authentication_popup = window.open(popup_url);
    }));
    var auth_trigger_id = Dwt.getNextId();
    
    var auth_sync_btn = new DwtButton({parent: this.getShell()});
    auth_sync_btn.setText(this.translate('get_filesender_authentication_check_button'));
    auth_sync_btn.setImage('SendReceive');
    auth_sync_btn.addSelectionListener(new AjxListener(this, function() {
        var profile_script = document.createElement('script');
        document.getElementsByTagName('head')[0].appendChild(profile_script);
        profile_script.src = user_url + '&_=' + (new Date()).getTime();
    }));
    var auth_sync_id = Dwt.getNextId();
    
    var dialog = this.makeDlg(
        this.translate('get_filesender_authentication_dlg_title'),
        {width: 400, height: 350},
        [
            this.translate('get_filesender_authentication_popup_label'),
            '<div id="' + auth_trigger_id + '"></div>',
            this.translate('get_filesender_authentication_check_label'),
            '<div id="' + auth_sync_id + '"></div>'
        ].join(''),
        [DwtDialog.CANCEL_BUTTON]
    );
    
    document.getElementById(auth_trigger_id).appendChild(auth_trigger_btn.getHtmlElement());
    
    document.getElementById(auth_sync_id).appendChild(auth_sync_btn.getHtmlElement());
    
    this.setDialogButton(
        dialog,
        DwtDialog.CANCEL_BUTTON,
        AjxMsg.cancel,
        new AjxListener(this, function() {
            dialog.popdown();
            dialog.dispose();
            
            var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
            
            zdata.use_filesender = false;
            zdata.filesender_server = null;
            
            org_filesender_zimlink.showSizeExceededError();
        })
    );
    
    dialog.popup();
    
    this.fs_auth_dialog = dialog;
};

/*
 * Nicely format a file size
 */
org_filesender_zimlink.prototype.formatFileSize = function(size) {
    var mult = [AjxMsg.sizeBytes, AjxMsg.sizeKiloBytes, AjxMsg.sizeMegaBytes, AjxMsg.sizeGigaBytes];
    
    while((size > 1024) && (mult.length > 1)) {
        size /= 1024;
        mult.shift();
    }
    
    return size.toFixed(Math.max(0, 2 - Math.floor(Math.log10(size)))) + mult.shift();
};

/*
 * In the Compose view, add the text in parameter at the end of the body, before the signature
 * Params:
 * downloadInfos : Array containing the filesender download link and expire date as String
 */
org_filesender_zimlink.prototype.addDownloadInfos = function(downloadInfos) {
    var controller = appCtxt.getCurrentController();
    var view = appCtxt.getCurrentView();
    
    // Gather infos
    var url = downloadInfos.downloadLink;
    var expiry = downloadInfos.expireDate;
    
    var files = [];
    for(var id in view.org_filesender_zimlink.files) {
        var file = view.org_filesender_zimlink.files[id];
        files.push(file.name + ' (' + this.formatFileSize(file.size) + ')');
    }
    
    //Original mail body
    var content = [view.getUserText()];
    var i = 1;
    
    //Add the download link and expiration date at the end of the body
    if(view._composeMode == Dwt.HTML) {
        content[i++] = '<br />';
        content[i++] = this.translate('files_are_attached_using_filesender');
        content[i++] = '<br />';
        content[i++] = '<ul>';
        content[i++] = '<li>' + files.join('</li><li>') + '</li>';
        content[i++] = '</ul>';
        content[i++] = '<br />';
        content[i++] = this.translate('download_link_label') + '<a href="' + url + '">' + url + '</a>';
        content[i++] = '<br />';
        content[i++] = this.translate('download_expire_date_label') + expiry;
    } else {
        content[i++] = "\n";
        content[i++] = this.translate('files_are_attached_using_filesender');
        content[i++] = "\n";
        content[i++] = "\n    * " + files.join("\n    * ") + "\n";
        content[i++] = "\n";
        content[i++] = this.translate('download_link_label') + url;
        content[i++] = "\n";
        content[i++] = this.translate('download_expire_date_label') + expiry;
    }
    
    //Add params with keepAttachments to false to clean attachments
    var params = {
        keepAttachments:    false,
        action:             controller._action,
        msg:                controller._msg,
        extraBodyText:      content.join('')
    };
    
    //Reset the body content
    view.resetBody(params);
};

/*
 * Start upload process
 */
org_filesender_zimlink.prototype.upload = function() {
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    
    zdata.uploading = true;
    
    var transfer_data = this.createTransfer();
    if(!transfer_data) return;
    
    transfer_data.file_idx = 0;
    transfer_data.file_offset = 0;
    
    zdata.transfer_data = transfer_data;
    
    var ctnid = Dwt.getNextId();
    
    zdata.uploading_popup = this.makeDlg(
        this.translate('uploading_dlg_title'),
        {width: 400, height: 400},
        [
            this.translate('uploading_dlg_text'),
            '<div id="' + ctnid + '" class="org_filesender_zimlink_detailed_progress"></div>',
            '<span id="' + ctnid + '_autosend"></span>'
        ].join(''),
        [DwtDialog.OK_BUTTON]
    );
    
    this.setDialogButton(
        zdata.uploading_popup,
        DwtDialog.OK_BUTTON,
        AjxMsg._close
    );
    
    var chk = new DwtCheckbox({parent: this.getShell()});
    chk.setTextPosition('right');
    chk.setText(this.translate('auto_send'));
    chk.addSelectionListener(new AjxListener(this, function() {
        zdata.autosend = chk.isSelected();
    }));
    document.getElementById(ctnid + '_autosend').appendChild(chk.getHtmlElement());
    
    zdata.uploading_popup.popup();
    
    var ctn = document.getElementById(ctnid);
    for(var i=0; i<transfer_data.files.length; i++)
        this.addDetailedUploadProgressbar(ctn, transfer_data.files[i]);
    
    this.uploadNext();
};

// Prepare detailed upload progressbars
org_filesender_zimlink.prototype.addDetailedUploadProgressbar = function(ctn, file) {
    var view = appCtxt.getCurrentView();
    var name = view._clipFile(file.name, true);
    
    var html = [
        '<div class="org_filesender_zimlink_detailed_progressbar">',
        '   <span id="' + file.cid + '_progressbar">0%</span>',
        '</div>',
        '<span title="' + file.name + '">' + name + '</span>'
    ].join('');
    
    var f = document.createElement('DIV');
    f.className = 'org_filesender_zimlink_detailed_progress_file';
    f.id = file.cid + '_detailed_progress';
    f.innerHTML = html;
    
    ctn.appendChild(f);
};

/*
 * Upload next chunk
 */
org_filesender_zimlink.prototype.uploadNext = function() {
    var tdata = appCtxt.getCurrentView().org_filesender_zimlink.transfer_data;
    var file = tdata.files[tdata.file_idx];
    
    this.uploadChunk(file, tdata.file_offset, new AjxCallback(this, function(error) {
        if(error) {
            this.showEndUploadError(error);
            return;
        }
        
        this.updateFileUploadProgress();
        
        tdata.file_offset += appCtxt.getCurrentView().org_filesender_zimlink.info.upload_chunk_size;
        if(tdata.file_offset >= file.size) {
            // File complete
            var resp = this.sendActionToJsp(this.getJspUrl('complete_file', tdata.files[tdata.file_idx].id), {complete: true});
            if(!resp || !resp.success) {
                this.showEndUploadError(resp);
                return;
            }
            
            this.updateFileUploadProgress(true);
            file.done = true;
            
            tdata.file_offset = 0;
            tdata.file_idx++;
            
            if(tdata.file_idx >= tdata.files.length) {
                // Transfer complete
                var resp = this.sendActionToJsp(this.getJspUrl('complete_transfer', tdata.id), {complete: true});
                if(!resp || !resp.success) {
                    this.showEndUploadError(resp);
                    return;
                }
                
                this.addDownloadInfos({
                    downloadLink: tdata.recipients[0].download_url,
                    expireDate: tdata.expires.formatted
                });
                
                this.doneUploading();
                
                return;
            }
        }
        
        this.uploadNext();
    }));
};

/*
 * Upload a chunk
 */
org_filesender_zimlink.prototype.uploadChunk = function(file, offset, callback) {
    var chunk_size = appCtxt.getCurrentView().org_filesender_zimlink.info.upload_chunk_size;
    
    var slicer = file.blob.slice ? 'slice' : (file.blob.mozSlice ? 'mozSlice' : (file.blob.webkitSlice ? 'webkitSlice' : 'slice'));
    
    var blob = file.blob[slicer](offset, offset + chunk_size);
    
    var url = this.getJspUrl('upload_chunk', file.id, file.size, offset);
    
    this.sendBlob(url, blob, callback);
};

/*
 * Create a transfer on selected filesender
 */
org_filesender_zimlink.prototype.createTransfer = function() {
    var creation_data = {
        from: appCtxt.getActiveAccount().name,
        recipients: [],
        options: ['get_a_link'],
        files: []
    };
    
    var files = appCtxt.getCurrentView().org_filesender_zimlink.files;
    for(var id in files) creation_data.files.push({
        name: files[id].name,
        size: files[id].size,
        mime_type: files[id].type,
        cid: id
    });
    
    var data = this.sendActionToJsp(this.getJspUrl('create_transfer'), creation_data);
    
    if(!data || !data.success) {
        this.showEndUploadError(data);
        return false;
    }
    
    transfer_data = data.response;
    
    for(var i=0; i<transfer_data.files.length; i++) {
        transfer_data.files[i].blob = files[transfer_data.files[i].cid];
    }
    
    for(var i=0; i<transfer_data.files.length; i++) {
        if(!transfer_data.files[i].blob) {
            this.showEndUploadError('file_mismatch');
            return false;
        }
    }
    
    return transfer_data;
};

/*
 * Build an url for a request to org_filesender_zimlink.jsp
 * Params:
 * command : String containing the command to execute in org_filesender_zimlink.jsp
 * file_id : String containing the file id
 * transfert_id : String containing the transfert_id
 * offset : String containing the offset
 */
org_filesender_zimlink.prototype.getJspUrl = function(command, target_id, size, offset) {
    // retrieve the server config
    var zdata = appCtxt.getCurrentView().org_filesender_zimlink;
    var auth_data = this.getAuthenticationData();
    if(!auth_data[zdata.filesender_server]) return null;
    
    var remote_config = auth_data[zdata.filesender_server].remote_config;
    
    var enc = AjxStringUtil.urlComponentEncode;
    var args = [
        'command=' + command,
        'filesender_url=' + enc(remote_config.url),
        'uid=' + enc(remote_config.uid),
        'secret=' + enc(remote_config.secret)
    ].join('&');
    
    // add function parameters
    if(command == 'upload_chunk')
        args = [args, 'file_id=' + enc(target_id), 'file_size=' + enc(size), 'offset=' + enc(''+offset)].join('&'); 
    
    if(command == 'complete_file')
        args = [args, 'file_id=' + enc(target_id)].join('&'); 
    
    if(command == 'complete_transfer')
        args = [args, 'transfer_id=' + enc(target_id)].join('&'); 
    
    return this.getResource('org_filesender_zimlink.jsp') + '?' + args;
}

/*
 * Send a request in json format to org_filesender_zimlink.jsp
 * Params:
 * url : String containing the url and the parameters
 * data : Object javascript containing the data to send
 */
org_filesender_zimlink.prototype.sendActionToJsp = function(url, data) {
    //Convert the javascript object into a String
    jsonData = data ? JSON.stringify(data) : null;
    
    //Send a synchronous request
    var resp = AjxRpc.invoke(jsonData, url, {'Content-type': 'application/json'}, null, false);
    
    if(!resp || !resp.success) return null;
    
    return JSON.parse(resp.text);
}

/*
 * Send a blob to org_filesender_zimlink.jsp
 * Params:
 * url : String containing the url and the parameters
 * file : File Object containing the data to send
 * offset : Integer containing the Offset of the blob
 * blocSize : Integer containing the block size from the filesender server
 * callBack : AjxCallBack function executed after the jsp response 
 */
org_filesender_zimlink.prototype.sendBlob = function(url, blob, callBack) {
    //Create the request
    var req = new XMLHttpRequest();
    req.open('POST', url , true);
    req.setRequestHeader('Cache-Control', 'no-cache');
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    req.setRequestHeader('Content-Type', 'application/octet-stream');
    
    //Send the result to the callBack function
    req.onreadystatechange = function() {
        if(req.readyState != 4) return; // Not a progress update
        
        if(req.status == 200) { // All went well
            callBack.run();
            
        }else if(xhr.status == 0) { // Request cancelled (browser refresh or such)
            org_filesender_zimlink_instance.showEndUploadError('request_cancelled');
            
        }else{
            // We have an error
            var msg = req.responseText.replace(/^\s+/, '').replace(/\s+$/, '');
            var error = {message: msg};
            
            try {
                error = JSON.parse(msg);
            } catch(e) {}
            
            callBack.run(error);
        }
    };
    
    //Send the blob
    req.send(blob);
}


/*
 * Generic function to create a dialog box
 * Params :
 * title : String containing the title of the dialog box
 * size : Array with the params width and height to set the size of the dialog box
 * content : String containing the html inserted in the dialog box
 * listenerYes : Listener object for the Yes button 
 * listenerNo : Listener object for the No button 
 * standardButtons : Array with the list of standardButtons (possible value is DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON or both).
 * 
 * call example : filesenderZimlet.makeDlg('title', {width:300,height:300}, 'content', [DwtDialog.OK_BUTTON, DwtDialog.CANCEL_BUTTON])
 */
org_filesender_zimlink.prototype.makeDlg = function(title, size, content, standardButtons) {
    //Create the frame
    var view = new DwtComposite(this.getShell());
    view.setSize(size.width, size.height);
    view.getHtmlElement().style.overflow = 'auto';
    //Add html content in the frame
    view.getHtmlElement().innerHTML = content;

    //pass the title, view and buttons information and create dialog box
    var dialog = this._createDialog({title:title, view:view, standardButtons: standardButtons});
    
    return dialog;
};

/*
 * Customize a ZmDialog button
 * Params :
 * dialog : ZmDialog object
 * buttonId : String containing the id of the button (ex: DwtDialog.OK_BUTTON)
 * text : String containing the displayed text of the button
 * listener : AjxListener object to add to the button
 */
org_filesender_zimlink.prototype.setDialogButton = function(dialog, buttonId, text, listener) {
    var button = dialog.getButton(buttonId);
    button.setText(text);
    if(listener) dialog.setButtonListener(buttonId, listener);
}

/*
 * Generic function to show an error message
 * Params:
 * msg : String containing the msg in html format to display
 */
org_filesender_zimlink.prototype.showError = function(msg, details) {
    if(details) {
        if(details.response) details = details.response;
        if(details.message) details = details.message;
        if(!details) details = 'unknown_error';
        msg += '<pre>' + details + '</pre>';
    }
    var msgDlg = appCtxt.getMsgDialog();
    msgDlg.setMessage(msg, DwtMessageDialog.CRITICAL_STYLE);
    msgDlg.popup();
};

// Popup size exceeded error
org_filesender_zimlink.showSizeExceededError = function() {
    var msgDlg = appCtxt.getMsgDialog();
    var errorMsg = AjxMessageFormat.format(ZmMsg.attachmentSizeError, AjxUtil.formatSize(appCtxt.get(ZmSetting.MESSAGE_SIZE_LIMIT)));
    msgDlg.setMessage(errorMsg, DwtMessageDialog.WARNING_STYLE);
    msgDlg.popup();
};

/*
 * Generic function to show an error message for the files upload, and unlock the composeView
 * Params:
 * msg : String containing the msg in html format to display
 */
org_filesender_zimlink.prototype.showEndUploadError = function(reason) {
    this.showError(this.translate('upload_error'), reason);
    
    appCtxt.getCurrentView().org_filesender_zimlink.uploading = false;
    delete appCtxt.getCurrentView().org_filesender_zimlink.transfer_data;
    
    appCtxt.getCurrentController()._toolbar.enableAll(true);
};

/*
 * Add a file bubble to the file list
 */
org_filesender_zimlink.prototype.addAttachmentBubble = function(fileName, id, done) {
    var view = appCtxt.getCurrentView();
    fileName = view._clipFile(fileName, true);
    
    //Check if the list is empty
    var firstBubble = view._attcDiv.getElementsByTagName('span')[0];
    if (firstBubble) {
        var tempBubbleWrapper = document.createElement('span');
        tempBubbleWrapper.innerHTML = this.getAttachmentBubbleHtml(fileName, id, done);
        var newBubble = tempBubbleWrapper.firstChild;
        firstBubble.parentNode.insertBefore(newBubble, firstBubble); //insert new bubble before first bubble.
    }
    else {
        //first one is enclosed in a wrapper (the template already expands the mail.Message#MailAttachmentBubble template inside the wrapper)
        view._attcDiv.innerHTML = this.getFirstAttachmentBubbleHtml(fileName, id, done);
    }
};

/*
 * Generate html for a file bubble
 */
org_filesender_zimlink.prototype.getAttachmentBubbleHtml = function(fileName, id, done) {
    var buffer = [];
    var _i = 0;
    
    var classes = 'AttachmentLoading attachmentBubble AttachmentSpan org_filesender_zimlink_bubble';
    if(done) classes += ' org_filesender_zimlink_bubble_done';
    
    buffer[_i++] = '<span id="' + id + '" class="' + classes + '" title="' + this.translate('filename_bubble_tip') + '" name="mailAttUploadingSpan">'
    buffer[_i++] = '    <span class="AttProgressSpan1">' + fileName + '</span>';
    buffer[_i++] = '    <span class="AttProgressSpan2">' + fileName + '</span>';
    if(!done)
        buffer[_i++] = '    <span onclick="org_filesender_zimlink_instance.removeFile(\'' + id + '\')" class="ImgBubbleDelete AttachmentClose"></span>';
    buffer[_i++] = '</span>';
    
    return buffer.join('');
};

/*
 * Generate html code for a file bubble when the file tab is empty
 */
org_filesender_zimlink.prototype.getFirstAttachmentBubbleHtml = function(fileName, id, done) {
    var buffer = [];
    var _i = 0;

    buffer[_i++] = '<table role="presentation" width="100%">';
    buffer[_i++] = '    <tr style="display:table-row">';
    buffer[_i++] = '        <td width="96%" colspan="2">';
    buffer[_i++] = '            <div class="attBubbleContainer">';
    buffer[_i++] = '                <div class="attBubbleHolder">';
    buffer[_i++] = '                    ' + this.getAttachmentBubbleHtml(fileName, id, done);
    buffer[_i++] = '                </div>';
    buffer[_i++] = '            </div>';
    buffer[_i++] = '        </td>';
    buffer[_i++] = '    </tr>';
    buffer[_i++] = '</table>';

    return buffer.join('');
};

// Set / get loader
org_filesender_zimlink.prototype.loader = function(rel) {
    var ctn = rel.parentNode;
    
    var loader = null;
    var divs = ctn.getElementsByTagName('div');
    for(var i=0; i<divs.length; i++)
        if(divs[i].className.match(/\bImgUpload\b/))
            loader = divs[i];
    
    if(!loader) {
        loader = document.createElement('div');
        loader.className = 'ImgUpload';
        ctn.insertBefore(loader, rel);
        
        var anim = {cnt: 0, tg: loader, ti: null};
        anim.ti = window.setInterval(function() {
            if(!anim.tg.parentNode) {
                window.clearInterval(anim.ti);
                return;
            }
            
            anim.tg.className = 'ImgUpload ImgUpload' + anim.cnt;
            
            anim.cnt++;
            if(anim.cnt > 12) anim.cnt = 0;
        }, 400);
    }
    
    return loader;
};

// Update file upload progress
org_filesender_zimlink.prototype.updateFileUploadProgress = function(done) {
    var tdata = appCtxt.getCurrentView().org_filesender_zimlink.transfer_data;
    var file = tdata.files[tdata.file_idx];
    
    var progress = tdata.file_offset / file.size;
    
    var span = document.getElementById(file.cid);
    
    var progress_span = span.getElementsByTagName('span')[0];
    var name_span = span.getElementsByTagName('span')[1];
    var remove_span = span.getElementsByTagName('span')[2];
    
    var detailed = document.getElementById(file.cid + '_detailed_progress');
    var detailed_bar = document.getElementById(file.cid + '_progressbar');
    
    if(remove_span) span.removeChild(remove_span);
    
    var loader = this.loader(span.childNodes[0]);
    var d_loader = this.loader(detailed.childNodes[0]);
    
    if(progress >= 1 || done) {
        span.removeChild(loader);
        span.removeChild(progress_span);
        span.className += ' org_filesender_zimlink_bubble_done';
        
        detailed.removeChild(detailed_bar.parentNode);
        detailed.removeChild(d_loader);
        detailed.className += ' org_filesender_zimlink_bubble_done';
        var name = detailed.getElementsByTagName('span')[0];
        name.innerHTML = name.title;
        
        detailed.parentNode.scrollTop = detailed.offsetTop + 'px';
        
        return;
    }
    
    progress_span.style.display = 'inline';
    progress_span.style.width = (name_span.offsetWidth * progress) + 'px';
    
    detailed_bar.innerHTML = (100 * progress).toFixed(1) + '%';
    detailed_bar.style.width = (100 * progress).toFixed(1) + '%';
};
