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
            'frequent_recipients' => $user->frequent_recipients,
            'eventcount' => $user->eventcount,
            'eventip' => $user->eventip
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

            $since = null;
            if (array_key_exists('since', $_REQUEST)) {
                $since = Utilities::sanitizeInput($_REQUEST['since']);
                if( !is_numeric($since) ) {
                    $since = null;
                }
            }
            
            if (array_key_exists('hitlimit', $_REQUEST) && $_REQUEST['hitlimit']!='') {
                $s = Utilities::sanitizeInput($_REQUEST['hitlimit']);
                $ttype = Utilities::sanitizeInput($_REQUEST['ttype']);
                // Force to only valid values
                if( $ttype != "User" && $ttype != "Guest" ) {
                    $ttype = "User";
                }
                return array_map(function ($user) {
                    return self::cast($user);
                }, array_values(AuditLog::findUsers($s,$ttype,$since)));
            }
            if (array_key_exists('hitlimitbycount', $_REQUEST)) {
                $s = Utilities::sanitizeInput($_REQUEST['hitlimitbycount']);

                return array_map(function ($user) {
                    return self::cast($user);
                }, array_values(AuditLog::findUsersOrderedByCount($s,'User',$since)));
            }
            if (array_key_exists('decryptfailed', $_REQUEST)) {
                $s = Utilities::sanitizeInput($_REQUEST['decryptfailed']);

                $logs = AuditLog::getUsersForTargetTypeSince( LogEventTypes::TRANSFER_DECRYPT_FAILED, 'Transfer', $since );

                return array_map(function ($user) {
                    return self::cast($user);
                }, array_values( $logs ));
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
            // Throwing an exception for this bad access case is
            // a bit of overkill, frequent_recipients are not a
            // critical feature so it might be better to fallback
            // to just offering nothing if they access things via
            // GET instead of POST.
            return array(
                'path' => '/user/user',
                'data' => ''
            );
        }

        if( $property == 'filesender-python-client-configuration-file' ) {

            $username = $user->saml_user_identification_uid;
            $authsecret = $user->auth_secret;
            $site_url = Config::get('site_url');
            $days_valid = Config::get('default_transfer_days_valid');
            
            $doc = <<<END
[system]
base_url = $site_url/rest.php
default_transfer_days_valid = $days_valid

[user]
username = $username
apikey = $authsecret
END;
            
            header('Content-Type: text/plain');
            echo $doc;
            exit;
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
        if ($id && $id!='@all') {
            $user = User::fromId($id);
            
            if (!Auth::user()->is($user) && !Auth::isAdmin()) {
                throw new RestOwnershipRequiredException(Auth::user()->id, 'user = '.$user->id);
            }
        } else {        
            $user = Auth::user();
        }
        
        // Update data
        $data = $this->request->input;

        if( $data->save_transfer_preferences || $data->save_frequent_email_address ) {

            $user->save_frequent_email_address = Utilities::validateCheckboxValue( $data->save_frequent_email_address );
            $user->save_transfer_preferences   = Utilities::validateCheckboxValue( $data->save_transfer_preferences );

            $user->save();
        }
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
            if ($id && $id=='@all')
            {
                if (!$user->is(Auth::user()) && !Auth::isAdmin()) {
                    throw new RestAdminRequiredException();
                }
                User::authSecretDeleteAll();
            }
            else
            {
                $user->authSecretDelete();
            }
        }
        if( $data->clear_frequent_recipients ) {
            $user->frequent_recipients = null;
            $user->save();
        }
        
        if( $data->clear_user_transfer_preferences ) {
            $user->transfer_preferences = null;
            $user->save();
        }
        if( $data->exists('guest_expiry_default_days')) {
            if (!Auth::isAdmin()) {
                throw new RestAdminRequiredException();
            }
            $user->guest_expiry_default_days = $data->guest_expiry_default_days;
            $user->save();
            
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

    /**
     * Create new user
     *
     * Call examples :
     *  /user : create new user from request
     *
     * @param string username
     * @param string password
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post($id = null, $add = null)
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        // ... and not guest
        if (Auth::isGuest()) {
            throw new RestOwnershipRequiredException((string)AuthGuest::getGuest(), 'user_info');
        }
        
        $user = Auth::user();
        $userid = -1;
        $data = $this->request->input;


        if ($data->property == 'frequent_recipients') {
            // Get frequent recipients with optionnal filter
            $ret = $this->getFrequentRecipients( $data->needle );
            return array(
                'path' => '/user/'.$user->id,
                'data' => $ret
            );
            
        }

        
        /////////
        //
        // WARNING
        //
        // From here down we are only handling updates to local user and password
        // when local_saml_db_auth is in place
        // 
        // The user/password options are only for local db auth
        if(!Config::get('using_local_saml_dbauth')) {
            throw new RestAuthenticationRequiredException();
        }
        // Raw data
        $username = $data->username;
        $password = $data->password;

        if ($username != "@me" && !Auth::isAdmin()) {
            throw new RestOwnershipRequiredException($userid, 'user = '.$userid);
        }

        if( $username == "@me" ) {
            $username = $user->saml_user_identification_uid;
        }

        if( $data->remind ) {
            if(!Auth::isAdmin()) {
                throw new RestOwnershipRequiredException($userid, 'user = '.$userid);
            }

            // Get matching user
            $authid = Authentication::ensureAuthIDFromSAMLUID($username);
            $user = User::fromAuthId($authid);
            $user->email_addresses = $username;
            
            $user->remindLocalAuthDBPassword( $password );
            return array(
                'path' => '/user/'.$username,
                'data' => ''
            );
        }
        
        $aa = Authentication::ensure( $username );
        $aa->password = $password;
        $aa->save();
        $user = User::fromAuthId($aa->id);
        $user->save();

        return array(
            'path' => '/user/'.$username,
            'data' => ''
        );
        
    }
}
