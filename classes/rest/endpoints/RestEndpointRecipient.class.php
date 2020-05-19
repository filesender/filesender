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
 * REST recipient endpoint
 */
class RestEndpointRecipient extends RestEndpoint
{
    /**
     * Cast a Recipient to an array for response
     *
     * @param Recipient $recipient
     *
     * @return array
     */
    public static function cast(Recipient $recipient)
    {
        return array(
            'id' => $recipient->id,
            'transfer_id' => $recipient->transfer_id,
            'token' => $recipient->token,
            'email' => $recipient->email,
            'created' => RestUtilities::formatDate($recipient->created),
            'last_activity' => $recipient->last_activity ? RestUtilities::formatDate($recipient->last_activity) : null,
            'options' => $recipient->options,
            'download_url' => $recipient->download_link,
            'errors' => array_values(array_map(function ($error) {
                return array(
                    'type' => $error->type,
                    'date' => RestUtilities::formatDate($error->created, true),
                    'details' => $error->details
                );
            }, $recipient->errors))
        );
    }
    
    /**
     * Get info about a recipient
     *
     * Call examples :
     *  /recipient/17 : info about recipient with id 17
     *
     * @param int $id recipient id to get info about
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function get($id = null)
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        // Check parameters
        if (!$id) {
            throw new RestMissingParameterException('recipient_id');
        }
        if (!is_numeric($id)) {
            throw new RestBadParameterException('recipient_id');
        }
        
        // Get current user and recipient to get info about
        $user = Auth::user();
        $recipient = Recipient::fromId($id);
        
        // Check ownership
        if (!$recipient->transfer->isOwner($user) && !Auth::isAdmin()) {
            throw new RestOwnershipRequiredException($user->id, 'recipient = '.$recipient->id);
        }
        
        return self::cast($recipient);
    }
    
    /**
     * Update a recipient's status
     *
     * Call examples :
     *  /recipient/17, payload: {remind: true} : remind its transfer to the recipient
     *
     * @param int $id recipient id to act upon
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function put($id = null)
    {
        // Check parameters
        if (!$id) {
            throw new RestMissingParameterException('recipient_id');
        }
        if (!is_numeric($id)) {
            throw new RestBadParameterException('recipient_id');
        }
        
        // Get recipient to update and current user
        $recipient = Recipient::fromId($id);
        $user = Auth::user();
        
        // Raw update data
        $data = $this->request->input;
        
        // check ownership
        if (!$recipient->transfer->isOwner($user) && !Auth::isAdmin()) {
            throw new RestOwnershipRequiredException($user->id, 'recipient = '.$recipient->id);
        }
        
        // Need to remind the transfer's availability to its recipients ?
        if ($data->remind) {
            $recipient->remind();
        }

        return self::cast($recipient);
    }
    
    /**
     * Delete a recipient
     *
     * Call examples :
     *  /recipient/17 : delete recipient with id 17
     *
     * @param int $id recipient id to get info about
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function delete($id = null)
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        // Check parameters
        if (!$id) {
            throw new RestMissingParameterException('recipient_id');
        }
        if (!is_numeric($id)) {
            throw new RestBadParameterException('recipient_id');
        }
        
        // Get current user and recipient to delete
        $user = Auth::user();
        $recipient = Recipient::fromId($id);
        
        // Check ownership
        if (!$recipient->transfer->isOwner($user) && !Auth::isAdmin()) {
            throw new RestOwnershipRequiredException($user->id, 'recipient = '.$recipient->id);
        }
        
        if (count($recipient->transfer->recipients) > 1) {
            // If transfer has several recipients remove the requested one
            $recipient->transfer->removeRecipient($recipient);
            
            if ($recipient->transfer->status == 'available') { // Notify deletion for transfers that are available
                $recipient->transfer->sendToRecipient('recipient_deleted', $recipient);
            }
        } else {
            // Last/only recipient deletion => close transfer
            $recipient->transfer->close();
        }
    }
}
