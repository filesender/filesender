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
class RestEndpointGuest extends RestEndpoint
{
    /**
     * Cast a Guest to an array for response
     *
     * @param Guest $guest
     *
     * @return array
     */
    public static function cast(Guest $guest)
    {
        return array(
            'id' => $guest->id,
            'userid' => $guest->userid,
            'user_email' => $guest->user_email,
            'email' => $guest->email,
            'token' => $guest->token,
            'transfer_count' => $guest->transfer_count,
            'subject' => $guest->subject,
            'message' => $guest->message,
            'transfer_options' => $guest->transfer_options,
            'options' => array_merge( $guest->options, array( 'guest' => $guest->options,
                                                              'transfer' => $guest->transfer_options )),
            'created' => RestUtilities::formatDate($guest->created),
            'expires' => RestUtilities::formatDate($guest->expires),
            'upload_url' => $guest->upload_link,
            'expiry_date_extension' => $guest->expiry_date_extension,
            'errors' => array_values(array_map(function ($error) {
                return array(
                    'type' => $error->type,
                    'date' => RestUtilities::formatDate($error->created, true),
                    'details' => $error->details
                );
            }, $guest->errors))
        );
    }
    
    /**
     * Get info about a guest
     *
     * Call examples :
     *  /guest : list of user guests (same as /guest/@me)
     *  /guest/@all : list of all available guests (admin only)
     *  /guest/17 : info about guest with id 17
     *  /guest/17/transfers : get transfers created by guest with id 17
     *
     * @param int $id guest id to get info about
     * @param string $property related to get info about
     *
     * @return mixed
     *
     * @throws RestAuthenticationRequiredException
     * @throws RestOwnershipRequiredException
     */
    public function get($id = null, $property = null)
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        $user = Auth::user();
        
        // check ownership if info about a specific guest is requested
        if (is_numeric($id)) {
            $guest = Guest::fromId($id);
            
            if (!$guest->isOwner($user) && !Auth::isAdmin()) {
                throw new RestOwnershipRequiredException($user->id, 'guest = '.$guest->id);
            }
            
            if ($property) {
                if ($property == 'transfers') {
                    return array_map('RestEndpointTransfer::cast', Transfer::fromGuest($guest));
                }
                
                throw new RestBadParameterException('property');
            }
            
            return self::cast($guest);
        }
        
        // Check parameters
        if (!in_array($id, array('', '@me', '@all'))) {
            throw new RestBadParameterException('guest_id');
        }
        
        if ($id == '@all') {
            // Getting all guests requires user to be admin
            if (!Auth::isAdmin()) {
                throw new RestAdminRequiredException();
            }
            
            $guests = Guest::all(Guest::AVAILABLE);
        } else {
            // $id == @me or empty
            $guests = Guest::fromUser($user);
        }
        
        // Cast and return
        $out = array();
        foreach ($guests as $guest) {
            $out[] = self::cast($guest);
        }
        
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
    public function post()
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        // User who is creating the new guest
        $user = Auth::user();

        if(!Config::get('guest_support_enabled')) {
            throw new GuestSystemDisabledException($user);
        }

        // Raw guest data
        $data = $this->request->input;

        // Check Guest creation limits
        $existingGuests = Guest::fromUserAvailable($user);
        if (count($existingGuests) >= Config::get('guest_limit_per_user')) {
            Logger::logActivity( LogEventTypes::GUEST_CREATED_LH, $user );
            throw new UserHitGuestLimitException();
        }

        // Can only re(create) $x guests per day
        Logger::logActivityRateLimited( 'UserHitGuestRateLimitException',
                                        'guest_create_limit_per_day',
                                        LogEventTypes::GUEST_CREATED_RATE, $user );

        
        // Create new guest object
        $guest = Guest::create($data->recipient, $data->from);

        // Check rate limit
        TranslatableEmail::rateLimit( false, 'guest_created', $guest );
        
        // Set provided metadata
        if ($data->subject) {
            $guest->subject = $data->subject;
        }
        if ($data->message) {
            $guest->message = $data->message;
            if (!Utilities::isValidMessage($guest->message)) {
                throw new GuestMessageBodyCanNotIncludeURLsException();
            }
        }
        
        // Allow any options for remote applications, check against allowed options otherwise
        $allowed_options = array_keys(Auth::isRemoteApplication() ? Guest::allOptions() : Guest::availableOptions());
        
        // Set options based on provided ones and defaults
        $guest_options = array();
        if (isset($data->options->guest)) {
            foreach (Guest::allOptions() as $name => $dfn) {
                if (in_array($name, $allowed_options)
                    && $data->options->guest->exists($name)) {
                    $guest_options[$name] = $data->options->guest->$name;
                }
            }
        }

        // options that are available => false and default => value should be forced
        // to being the default rather than anything the user has tried to send
        // as they are not available for users to change.
        foreach (Guest::forcedOptions() as $name => $dfn) {
            if (in_array('default', $dfn)) {
                $guest_options[$name] = $dfn['default'];
            }
        }
        
        $guest->options = $guest_options;
        
        // Set to-be-created transfers options based on provided ones and defaults
        $data_options_transfer = (array)$data->options->transfer;
        
        // Allow any options for remote applications, check against allowed options otherwise
        $allowed_transfer_options = array_keys(Auth::isRemoteApplication() ? Transfer::allOptions() : Transfer::availableOptions());
        
        $transfer_options = array();
        if (isset($data->options->transfer)) {
            foreach (Transfer::allOptions() as $name => $dfn) {
                if (in_array($name, $allowed_transfer_options)
                    && $data->options->transfer->exists($name)) {
                    $transfer_options[$name] = $data->options->transfer->$name;
                }
            }
        }
        // ensure that this option is explicit in the transfer options
        // based on the guest options.
        if( $guest->getOption(GuestOptions::CAN_ONLY_SEND_TO_ME)) {
            $transfer_options[TransferOptions::GET_A_LINK] = false;
        }
        
        if( strtolower(Config::get('storage_type')) == 'clouds3' ) {
            $options = StorageCloudS3::augmentTransferOptions( $options );
        }

        if(Auth::isRemote()) {
            if (!array_key_exists(TransferOptions::ADD_ME_TO_RECIPIENTS, $transfer_options)) {
                $transfer_options[TransferOptions::ADD_ME_TO_RECIPIENTS] = false;
            }
        }
        $guest->transfer_options = $transfer_options;
        
        // Set expiry date
        $expires = $guest->getDefaultExpire();
        if( $data->expires ) {
            $expires = $data->expires;
        }
        $expires = Utilities::clamp( $expires, $guest->getMinExpire(), $guest->getMaxExpire());
        $guest->expires = $expires;

        if($guest->does_not_expire) {
            $guest->expires = null;
        }


        
        // Make guest available, this saves the object and send email to the guest
        $guest->makeAvailable();
        
        return array(
            'path' => '/guest/'.$guest->id,
            'data' => self::cast($guest)
        );
    }
    
    /**
     * Update a guest's status
     *
     * Call examples :
     *  /guest/17, payload: {remind: true} : remind a guest to recipient
     *
     * @param int $id transfer id to get info about
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
            throw new RestMissingParameterException('guest_id');
        }
        if (!is_numeric($id)) {
            throw new RestBadParameterException('guest_id');
        }
        
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        // Get user and guest to update
        $guest = Guest::fromId($id);
        $user = Auth::user();
        
        // Check ownership
        if (!$guest->isOwner($user) && !Auth::isAdmin()) {
            throw new RestOwnershipRequiredException($user->id, 'guest = '.$guest->id);
        }
        
        // Raw update data
        $data = $this->request->input;
        
        // Reminder sending
        if ($data->remind) {
            $guest->remind();
        }

        // Need to extend expiry date
        if ($data->extend_expiry_date) {
            if( !Auth::isAdmin()) {
                if( Config::get("allow_guest_expiry_date_extension") == 0 ) {
                    throw new RestAdminRequiredException();
                }
            }
            $guest->extendObjectExpiryDate();
            return self::cast($guest);
        }
        
        
        return true;
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
    public function delete($id = null)
    {
        // Need to be authenticated
        if (!Auth::isAuthenticated()) {
            throw new RestAuthenticationRequiredException();
        }
        
        $user = Auth::user();
        
        // Check parameters
        if (!$id) {
            throw new RestMissingParameterException('guest_id');
        }
        if (!is_numeric($id)) {
            throw new RestBadParameterException('guest_id');
        }
        
        // Get guest to be removed
        $guest = Guest::fromId($id);
        
        // Check ownership
        if (!$guest->isOwner($user) && !Auth::isAdmin()) {
            throw new RestOwnershipRequiredException($user->id, 'guest = '.$guest->id);
        }

        // Remove guest access rights
        $guest->close();
    }
}
