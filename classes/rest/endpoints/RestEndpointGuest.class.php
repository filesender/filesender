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

// Require environment (fatal)
if (!defined('FILESENDER_BASE')) 
    die('Missing environment');

/**
 * REST transfer endpoint
 */
class RestEndpointGuest extends RestEndpoint {
    /**
     * Cast a GuestVoucher to an array for response
     * 
     * @param Transfer $transfer
     * 
     * @return array
     */
    public static function cast(GuestVoucher $voucher) {
        return array(
            'id' => $voucher->id,
            'user_id' => $voucher->user_id,
            'user_email' => $voucher->user_email,
            'email' => $voucher->email,
            'token' => $voucher->token,
            'transfer_count' => $voucher->transfer_count,
            'subject' => $voucher->subject,
            'message' => $voucher->message,
            'options' => $voucher->options,
            'created' => RestUtilities::formatDate($voucher->created),
            'expires' => RestUtilities::formatDate($voucher->expires),
        );
    }
    
    /**
     * Get info about a guest voucher
     * 
     * Call examples :
     *  /guest : list of user guests (same as /guest/@me)
     *  /guest/@all : list of all available guests (admin only)
     *  /guest/17 : info about guest with id 17
     * 
     * @param int $id guest voucher id to get info about
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function get($id = null) {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        if(is_numeric($id)) {
            $voucher = GuestVoucher::fromId($id);
            
            if(!$voucher->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'voucher = '.$voucher->id);
            
            return self::cast($voucher);
        }
        
        if(!in_array($id, array('', '@me', '@all'))) throw new RestBadParameterException('voucher_id');
        
        if($id == '@all') {
            if(!Auth::isAdmin()) throw new RestAdminRequiredException();
            
            $vouchers = GuestVoucher::all(GuestVoucher::AVAILABLE);
            
        }else{ // $id == @me or empty
            $vouchers = GuestVoucher::fromUser($user);
        }
        
        $out = array();
        foreach($vouchers as $voucher) $out[] = self::cast($voucher);
        
        return $out;
    }
    
    /**
     * Create new guest voucher
     * 
     * Call examples :
     *  /guest : create new guest voucher from request
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post() {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        // New guest voucher
        $data = $this->request->input;
        
        $voucher = GuestVoucher::create($data->recipient, $data->from);
        
        if($data->subject) $voucher->subject = $data->subject;
        if($data->message) $voucher->message = $data->message;
        
        $voucher->options = $data->options;
        
        //if($voucher->options) {
            //if(in_array('no_expiry', $voucher->options) && option is available) {
            //    ...
            //} else {
                $voucher->expires = $data->expires;
            //}
        //}
        
        $voucher->makeAvailable(); // Saves
        
        return array(
            'path' => '/guest/'.$voucher->id,
            'data' => self::cast($voucher)
        );
    }
    
    /**
     * Delete (closes) a guest voucher
     * 
     * Call examples :
     *  /guest/17 : close guest voucher with id 17
     * 
     * @param int $id guest voucher id to close
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function delete($id = null) {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        if(!$id) throw new RestMissingParameterException('voucher_id');
        if(!is_numeric($id)) throw new RestBadParameterException('voucher_id');
        
        $voucher = GuestVoucher::fromId($id);
        
        if(!$voucher->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'voucher = '.$voucher->id);
        
        $voucher->close();
    }
}
