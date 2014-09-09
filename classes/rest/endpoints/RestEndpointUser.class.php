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
class RestEndpointUser extends RestEndpoint {
    
    /**
     * Cast a User to an array for response
     * 
     * @param User user
     * 
     * @return array
     */
    public static function cast(User $user) {
        return array(
            'id' => $user->id,
            'organization' => $user->organization,
            'aup_ticked' => $user->aup_ticked,
            'transfer_preferences' => $user->transfer_preferences,
            'guest_preferences' => $user->guest_preferences,
            'created' => RestUtilities::formatDate($user->created),
            'last_activity' => RestUtilities::formatDate($user->last_activity),
            'lang' => $user->lang,
            'frequent_recipients' => $user->frequent_recipients,
        );
    }
    
    
    
    /**
     * Get all recipeint frequent of the user
     * 
     * Call examples :
     *  /user/@me/frequent_recipients/?filterOp[contain]=needle : get all 
     * frequent recipients containing needle in the mail
     * 
     * @param string $needle : needle to search
     * 
     * @return mixed
     */
    private function getFrequentRecipients($needle = '') {
        $user = Auth::user();
        
        $minCaracters = Config::get('minimum_characters_for_autocomplete') != null ? 
                Config::get('minimum_characters_for_autocomplete') : 3;
        $mails = '';
        
        if($needle == '' || strlen($needle) >= $minCaracters) {
            $mails = $user->getFrequentRecipients($needle);
        }
        
        return $mails;
    }
    
    
     /**
     * Get info about a user
     * 
     * Call examples :
     *  /user/@me/frequent_recipients : list of all frequent recipients of the current user
     *  /user/17/frequent_recipients : list of all frequent recipients of the user 
     * 
     * @param int $id user id to get info about
     * @param string $property to get info about ("file" or "recipient")
     * 
     * @return mixed
     * 
     * @throws RestAuthenticationRequiredException
     * @throws RestBadParameterException
     */
    public function get($id = null, $property = null) {
        if(!Auth::isAuthenticated()) throw new RestAuthenticationRequiredException();
        
        $user = Auth::user();
        
        if(is_numeric($id)) {
            $user = User::fromId($id);
        }
        
        if(!in_array($id, array('', '@me'))) throw new RestBadParameterException('user_id');
        
        if ($property == "frequent_recipients"){
            if (property_exists($this->request, 'filterOp')){
                return $this->getFrequentRecipients($this->request->filterOp->contains);
            }else{
                return '';
            }
        }else{
            // For now, can get only frequent_recipients from user
            return '';
        }
    }
    
}
