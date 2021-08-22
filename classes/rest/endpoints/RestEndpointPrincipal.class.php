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
 * REST principal endpoint
 */
class RestEndpointPrincipal extends RestEndpoint
{
    
    
    
    /**
     * Get info about a principal
     *
     * This is not possible yet.
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestBadParameterException
     * @throws RestMissingParameterException
     * @throws RestOwnershipRequiredException
     * @throws AuthRemoteUserRejectedException
     */
    public function get($id = null, $property = null)
    {
        throw new RestBadParameterException('bad_attempt');
    }
    
    /**
     * Set user preference
     *
     * Call examples :
     *  /principal/ : set preferences of principal
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function put($id = null)
    {
        $principal = Auth::getPrincipal();
        if( !$principal ) {
            throw new RestUnknownPrincipalException();
        }
        
        $data = $this->request->input;
        
        if( $data->service_aup_version ) {

            if( $data->service_aup_version != Config::get('service_aup_min_required_version')) {
                throw new RestBadParameterException('service_aup_version');
            }
            $principal->service_aup_accepted_version = $data->service_aup_version;
            $principal->service_aup_accepted_time = time();
            $principal->save();
        }
        return true;
    }


    /**
     * Create new principal
     *
     * This is not possible.
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function post($id = null, $add = null)
    {
        throw new RestBadParameterException('bad_attempt');
    }
}
