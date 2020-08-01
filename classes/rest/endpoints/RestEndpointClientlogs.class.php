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
 * REST client logs endpoint
 */
class RestEndpointClientlogs extends RestEndpoint
{
    /**
     * Cast to output
     *
     * @param ClientLog $log
     *
     * @return array
     */
    public static function cast(ClientLog $log)
    {
        return array(
            'id' => $log->id,
            'message' => $log->message,
            'created' => RestUtilities::formatDate($log->created, true)
        );
    }
    
    /**
     * Get user logs
     *
     * Call examples :
     *  /clientlogs/@me (or /clientlogs): get current user logs
     *  /clientlogs/foobar: get logs of specific user (admin restrited)
     *
     * @param string $uid : user id
     *
     * @return array
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestBadParameterException
     */
    public function get($id = null)
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
    
        return array_map(function ($log) {
            return self::cast($log);
        }, array_values(ClientLog::fromUser($user)));
    }
    
    /**
     * Set user client logs
     *
     * Call examples :
     *  /clientlogs/@me (or /clientlogs) : set client logs of current user
     *  /clientlogs/foo@bar.tld : set client of user with uid foo@bar.tld
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
        if ($id && $id != '@me') {
            $user = User::fromId($id);
            
            if (!Auth::user()->is($user) && !Auth::isAdmin()) {
                throw new RestOwnershipRequiredException(Auth::user()->id, 'user = '.$user->id);
            }
        } else {
            $user = Auth::user();
        }
        
        $stash = ClientLog::stash($user, (array)$this->request->input);
        
        return array_map(function ($log) {
            return self::cast($log);
        }, $stash);
    }
}
