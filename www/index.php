<?php

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
 *  notice, this list of conditions and the following disclaimer.
 * *    Redistributions in binary form must reproduce the above copyright
 *  notice, this list of conditions and the following disclaimer in the
 *  documentation and/or other materials provided with the distribution.
 * *    Neither the name of AARNet, Belnet, HEAnet, SURFnet and UNINETT nor the
 *  names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.
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

// Start filesender
require_once('../includes/init.php');

try {
    ob_start();
    
    Template::display('!header');
    
    try { // At that point we can render exceptions using nice html
        $known_pages = array('home', 'upload', 'transfers', 'vouchers', 'admin', 'logon', 'download', 'exception');
        
        $allowed_pages = array('home', 'download', 'exception');
        
        if(Auth::isAuthenticated()) {
            if(Auth::isVoucher()) {
                $allowed_pages = array('home', 'upload', 'exception');
            } else {
                $allowed_pages = array('home', 'upload', 'transfers', 'vouchers', 'download', 'exception');
                
                if(Auth::isAdmin()) $allowed_pages[] = 'admin';
            }
        }
        
        $page = null;
        $vars = array();
        if(array_key_exists('s', $_REQUEST)) $page = $_REQUEST['s'];
        if(!$page) $page = $allowed_pages[0];
        
        if(!in_array($page, $known_pages)) {
            $page = 'error';
            $vars['error'] = Lang::tr('unknown_page');
            
        }else if(!in_array($page, $allowed_pages)) {
            if(!Auth::isAuthenticated()) {
                $page = 'logon';
                $vars['access_forbidden'] = true;
                
                if(Config::get('auth_sp_autotrigger')) AuthSP::trigger();
            }else{
                $page = 'error';
                $vars['error'] = Lang::tr('access_forbidden');
            }
        }
        
        if(Auth::isAuthenticated() && !Auth::isVoucher() && ($page != 'download'))
            Template::display('menu', array('allowed_pages' => $allowed_pages, 'current_page' => $page));
        
        Template::display('page', array('page' => $page, 'vars' => $vars));
        
        Template::display('!footer');
        
    } catch(LoggingException $e) {
        Template::display('exception', array('message' => $e->getMessage(), 'logid' => $e->getUid()));
    } catch(Exception $e) {
        Template::display('exception', array('message' => $e->getMessage()));
    }
    
    ob_end_flush();
    
} catch(Exception $e) {
    // If all exceptions are catched as expected we should not get there
    die('An exception happened : '.$e->getMessage());
}
