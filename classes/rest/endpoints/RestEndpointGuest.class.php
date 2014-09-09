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
     * Cast a Guest to an array for response
     * 
     * @param Guest $guest
     * 
     * @return array
     */
    public static function cast(Guest $guest) {
        return array(
            'id' => $guest->id,
            'user_id' => $guest->user_id,
            'user_email' => $guest->user_email,
            'email' => $guest->email,
            'token' => $guest->token,
            'transfer_count' => $guest->transfer_count,
            'subject' => $guest->subject,
            'message' => $guest->message,
            'options' => $guest->options,
            'created' => RestUtilities::formatDate($guest->created),
            'expires' => RestUtilities::formatDate($guest->expires),
        );
    }
    
    /**
     * Get info about a guest
     * 
     * Call examples :
     *  /guest : list of user guests (same as /guest/@me)
     *  /guest/@all : list of all available guests (admin only)
     *  /guest/17 : info about guest with id 17
     * 
     * @param int $id guest id to get info about
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
            $guest = Guest::fromId($id);
            
            if(!$guest->isOwner($user) && !Auth::isAdmin())
                throw new RestOwnershipRequiredException($user->id, 'guest = '.$guest->id);
            
            return self::cast($guest);
        }
        
        if(!in_array($id, array('', '@me', '@all'))) throw new RestBadParameterException('guest_id');
        
        if($id == '@all') {
            if(!Auth::isAdmin()) throw new RestAdminRequiredException();
            
            $guests = Guest::all(Guest::AVAILABLE);
            
        }else{ // $id == @me or empty
            $guests = Guest::fromUser($user);
        }
        
        $out = array();
        foreach($guests as $guest) $out[] = self::cast($guest);
        
        return $out;
    }
    
    /**
     * Create new guest
     * 
     * Call examples :
     *  /guest : create new guest from request
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post() {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        $data = $this->request->input;
        
        $guest = Guest::create($data->recipient, $data->from);
        
        if($data->subject) $guest->subject = $data->subject;
        if($data->message) $guest->message = $data->message;
        
        $guest->options = $data->options;
        
        //if($guest->options) {
            //if(in_array('no_expiry', $guest->options) && option is available) {
            //    ...
            //} else {
                $guest->expires = $data->expires;
            //}
        //}
        
        $guest->makeAvailable(); // Saves
        
        return array(
            'path' => '/guest/'.$guest->id,
            'data' => self::cast($guest)
        );
    }
    
    /**
     * Delete (closes) a guest
     * 
     * Call examples :
     *  /guest/17 : close guest with id 17
     * 
     * @param int $id guest id to close
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function delete($id = null) {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        if(!$id) throw new RestMissingParameterException('guest_id');
        if(!is_numeric($id)) throw new RestBadParameterException('guest_id');
        
        $guest = Guest::fromId($id);
        
        if(!$guest->isOwner($user) && !Auth::isAdmin())
            throw new RestOwnershipRequiredException($user->id, 'guest = '.$guest->id);
        
        $guest->close();
    }
}
