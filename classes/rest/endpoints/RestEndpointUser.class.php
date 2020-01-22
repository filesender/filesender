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
if (!defined('FILESENDER_BASE')) {
    die('Missing environment');
}

/**
 * REST transfer endpoint
 */
class RestEndpointUser extends RestEndpoint
{
    
    /**
     * Cast a User to an array for response
     *
     * @param User user
     *
     * @return array
     */
    public static function cast(User $user)
    {
        return array(
            'id' => $user->id,
            'saml_id' => $user->saml_user_identification_uid,
            'additional_attributes' => $user->additional_attributes,
            'aup_ticked' => $user->aup_ticked,
            'transfer_preferences' => $user->transfer_preferences,
            'guest_preferences' => $user->guest_preferences,
            'created' => RestUtilities::formatDate($user->created),
            'last_activity' => RestUtilities::formatDate($user->last_activity),
            'lang' => $user->lang,
            'frequent_recipients' => $user->frequent_recipients
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
    private function getFrequentRecipients($needle = '')
    {
        $user = Auth::user();
        
        // Get minimum number of characters needed for search
        $minchars = Config::get('autocomplete_min_characters');
        if (is_null($minchars)) {
            $minchars = 3;
        }
        
        $mails = array();
        
        // Get matching if no search or search long enough
        if ($needle == '' || strlen($needle) >= $minchars) {
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
    *  /user?search=foo : search for user with matching id/email (restricted to admin)
    *
    * @param int $id user id to get info about
    * @param string $property to get info about ("file" or "recipient")
    *
    * @return mixed
    *
    * @throws RestAuthenticationRequiredException
    * @throws RestBadParameterException
    * @throws RestMissingParameterException
    * @throws RestOwnershipRequiredException
    * @throws AuthRemoteUserRejectedException
    */
    public function get($id = null, $property = null)
    {
        // Need to be authenticated ...
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        // ... and not guest
        if (Auth::isGuest()) {
            throw new RestOwnershipRequiredException((string)AuthGuest::getGuest(), 'user_info');
        }
        
        $user = Auth::user();
        
        // Check ownership
        if ($id && $id != '@me') {
            $user = User::fromId($id);
            
            if (!$user->is(Auth::user()) && !Auth::isAdmin()) {
                throw new RestOwnershipRequiredException(Auth::user()->id, 'user = '.$user->id);
            }
        }
        
        if (!$id) {
            // Search user
            if (!Auth::isAdmin()) {
                throw new RestOwnershipRequiredException(Auth::user()->id, 'user = '.$user->id);
            }
            
            if (!array_key_exists('match', $_REQUEST)) {
                throw new RestMissingParameterException('match');
            }
    
            $match = Utilities::sanitizeInput($_REQUEST['match']);
            if (strlen($match) < 3) {
                return array();
            }
            
            return array_map(function ($user) {
                return self::cast($user);
            }, array_values(User::search($match)));
        }
        
        if ($property == 'frequent_recipients') {
            // Get frequent recipients with optionnal filter
            $rcpt = array();
            
            if (
                array_key_exists('email', $this->request->filterOp)
                && array_key_exists('contains', $this->request->filterOp['email'])
            ) {
                $rcpt = $this->getFrequentRecipients($this->request->filterOp['email']['contains']);
            }
            
            return $rcpt;
        }
        
        if ($property == 'quota') {
            // Get user quota info (if enabled)
            
            $user_quota = Config::get('user_quota');
            if (!$user_quota) {
                return null;
            }
            
            // Compute size used by user's transfers
            $used = array_sum(array_map(function ($t) {
                return $t->size;
            }, Transfer::fromUser(Auth::user())));
            
            return array(
                'total' => $user_quota,
                'used' => $used,
                'available' => max(0, $user_quota - $used)
            );
        }
        
        if ($property == 'remote_auth_config') {
            $perm = isset($_SESSION) && array_key_exists('remote_auth_sync_request', $_SESSION) ? $_SESSION['remote_auth_sync_request'] : null;
            if (!$perm) {
                throw new RestAuthenticationRequiredException();
            }
            
            unset($_SESSION['remote_auth_sync_request']);
            
            if ($perm['expires'] < time()) {
                throw new RestAuthenticationRequiredException();
            }
            
            $code = func_get_arg(2);
            if (!$code || $code !== $perm['code']) {
                throw new RestAuthenticationRequiredException();
            }
            
            if (!Config::get('auth_remote_user_enabled')) {
                throw new AuthRemoteUserRejectedException($user->id, 'remote auth disabled');
            }
            
            if (!$user->auth_secret) {
                throw new AuthRemoteUserRejectedException($user->id, 'no secret set');
            }
            
            return array('remote_config' => $user->remote_config);
        }
        
        if (!$property) {
            return self::cast($user);
        }
        
        return null;
    }
    
    /**
     * Set user preference
     *
     * Call examples :
     *  /user/foo@bar.tld : set preferences of user with uid foo@bar.tld
     *
     * @param string $id user id
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function put($id = null)
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        // Check ownership if specific user id given
        if ($id) {
            $user = User::fromId($id);
            
            if (!Auth::user()->is($user) && !Auth::isAdmin()) {
                throw new RestOwnershipRequiredException(Auth::user()->id, 'user = '.$user->id);
            }
        } else {
            $user = Auth::user();
        }
        
        // Update data
        $data = $this->request->input;
        
        if ($data->lang) {
            // Lang property update, fail if not allowed
            
            if (!Config::get('lang_userpref_enabled')) {
                throw new RestBadParameterException('user_lang');
            }
            
            // check that requested lang is known
            $availables = Lang::getAvailableLanguages();
            if (!array_key_exists($data->lang, $availables)) {
                throw new RestBadParameterException('user_lang');
            }
            
            // Update user object and save to database
            $user->lang = $data->lang;
            $user->save();
            
            // Remove lang from session if there was one, we don't need it anymore as it was saved in user profile
            if (isset($_SESSION) && array_key_exists('lang', $_SESSION)) {
                unset($_SESSION['lang']);
            }
        }
        if( $data->apisecretcreate ) {
            $user->authSecretCreate();
        }
        if( $data->apisecretdelete ) {
            $user->authSecretDelete();
        }
        
        return true;
    }

    /**
     * Delete (closes) a user
     *
     * Call examples :
     *  /user/@me : close user which you are logged in as
     *
     * @param int $id guest id to close or @me.
     *                NOTE use of a number is not implemented yet.
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function delete($id = null)
    {

        // Check parameters
        if (!$id) {
            throw new RestMissingParameterException('user_id');
        }
        if ($id != '@me' && !is_numeric($id)) {
            throw new RestBadParameterException('user_id');
        }
        
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        $user = Auth::user();

        
        // Delete the user (not recoverable)
        $user->delete();
    }
}
