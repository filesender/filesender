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
 * Represents a recipient in database
 *
 * @property array $transfer related transfer
 */
class TranslatableEmail extends DBObject
{
    /**
     * Database map
     */
    protected static $dataMap = array(
        'id' => array(
            'type' => 'uint',
            'size' => 'medium',
            'primary' => true,
            'autoinc' => true
        ),
        'context_type' => array(
            'type' => 'string',
            'size' => 255
        ),
        'context_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'token' => array(
            'type' => 'string',
            'size' => 60
        ),
        'translation_id' => array(
            'type' => 'string',
            'size' => 255
        ),
        'variables' => array(
            'type' => 'mediumtext',
            'transform' => 'json'
        ),
        'created' => array(
            'type' => 'datetime'
        ),
    );

    public static function getViewMap()
    {
        $a = array();
        foreach (array('mysql','pgsql') as $dbtype) {
            $a[$dbtype] = 'select *'
                        . DBView::columnDefinition_age($dbtype, 'created')
                        . '  from ' . self::getDBTable();
        }
        return array( strtolower(self::getDBTable()) . 'view' => $a );
    }
    
    /**
     * Properties
     */
    protected $id = null;
    protected $context_type = '';
    protected $context_id = null;
    protected $token = '';
    protected $translation_id = '';
    protected $variables = null;
    protected $created = 0;
    
    /**
     * Constructor
     *
     * @param integer $id identifier of translatable email to load from database (null if loading not wanted)
     * @param array $data data to create the translatable email from (if already fetched from database)
     *
     * @throws TranslatableEmailNotFoundException
     */
    protected function __construct($id = null, $data = null)
    {
        if (!is_null($id)) {
            // Load from database if id given
            $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE id = :id');
            $statement->execute(array(':id' => $id));
            $data = $statement->fetch();
            if (!$data) {
                throw new TranslatableEmailNotFoundException('id = '.$id);
            }
        }
        
        // Fill properties from provided data
        if ($data) {
            $this->fillFromDBData($data);
        }
    }
    
    /**
     * Loads translatable email from token
     *
     * @param string $token the token
     *
     * @throws TranslatableEmailNotFoundException
     *
     * @return TranslatableEmail
     */
    public static function fromToken($token)
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE token = :token');
        $statement->execute(array(':token' => $token));
        $data = $statement->fetch();
        if (!$data) {
            throw new TranslatableEmailNotFoundException('token = '.$token);
        }
        
        $email = self::fromData($data['id'], $data);
        
        return $email;
    }
    
    /**
     * Loads translatable email from context
     *
     * @param DBObject $context
     *
     * @return array
     */
    public static function fromContext(DBObject $context)
    {
        $statement = DBI::prepare('SELECT * FROM '.self::getDBTable().' WHERE context_type = :type AND context_id = :id');
        $statement->execute(array(':type' => get_class($context), ':id' => $context->id));
        
        $emails = array();
        foreach ($statement->fetchAll() as $data) {
            $emails[$data['id']] = self::fromData($data['id'], $data);
        } // Don't query twice, use loaded data
        
        return $emails;
    }
    
    /**
     * Create a new translatable email
     *
     * @param DBObject $context
     * @param string $translation_id
     * @param array $variables
     *
     * @return TranslatableEmail
     */
    public static function create(DBObject $context, $translation_id, $variables)
    {
        $email = new self();
        
        // Get context (caller)
        $email->context_type = get_class($context);
        $email->context_id = $context->id;
        
        // Get translation data and variables
        $email->translation_id = $translation_id;
        
        $email->variables = array();
        if ($variables) {
            foreach ($variables as $k => $v) {
                // Convert DBObject types to type/id pairs for saving
                if ($v instanceof DBObject) {
                    $v = array(
                'dbobject_type' => get_class($v),
                'dbobject_id' => $v->id
            );
                }
                $email->variables[$k] = $v;
            }
        }
        
        // Add meta
        $email->created = time();
        
        // Generate token until it is indeed unique
        $email->token = Utilities::generateUID(false, function ($token, $tries) {
            $statement = DBI::prepare('SELECT * FROM '.TranslatableEmail::getDBTable().' WHERE token = :token');
            $statement->execute(array(':token' => $token));
            $data = $statement->fetch();
            if (!$data) {
                Logger::info('TranslatableEmail uid generation took '.$tries.' tries');
            }
            return !$data;
        });
        
        $email->save();
        
        return $email;
    }

    public static function getContext($translation_id, DBObject $to, $vars )
    {
        // Extract context info from arguments
        $context = null;
        switch (get_class($to)) {
            case 'Recipient': // Recipient context is it's transfer
                $context = $to->transfer;
                break;
                
            case 'Guest': // Guest is a context by itself
                $context = $to;
                break;

            case 'Transfer': // Transfer
                $context = $to;
                break;
                
            case 'User': // If recipient is user try to find Transfer, File or Guest in variables
                foreach ($vars as $v) {
                    if (!is_object($v)) {
                        continue;
                    }
                    
                    if (in_array(get_class($v), array('Transfer', 'Guest'))) {
                        $context = $v;
                    }
                    
                    if ('File' == get_class($v)) {
                        $context = $v->transfer;
                    }
                }
                if ($context) {
                    break;
                }
                if( $translation_id == 'local_authdb_password_reminder' ) {
                    $context = $to;
                    break;
                } 
                
                // no break
            default:
                Logger::debug("getContext bad to ", $to );
                throw new TranslatableEmailUnknownContextException(get_class($to));
        }
        return $context;
    }
    
    /**
     * Prepare mail to be sent to recipient (be it Guest, Recipient ...)
     *
     * @param string $translation_id
     * @param DBObject $to recipient is also used to get the context
     * @param mixed * translation args
     *
     * @return ApplicationMail
     */
    public static function prepare($translation_id, DBObject $to)
    {
        $original_to = $to;
        $vars = array_slice(func_get_args(), 2);
        array_unshift($vars, $to);
        
        // Extract context info from arguments
        $context = null;
        switch (get_class($to)) {
            case 'Recipient': // Recipient context is it's transfer
                $context = $to->transfer;
                break;
                
            case 'Guest': // Guest is a context by itself
                $context = $to;
                break;
                
            case 'User': // If recipient is user try to find Transfer, File or Guest in variables
                foreach ($vars as $v) {
                    if (!is_object($v)) {
                        continue;
                    }
                    
                    if (in_array(get_class($v), array('Transfer', 'Guest'))) {
                        $context = $v;
                    }
                    
                    if ('File' == get_class($v)) {
                        $context = $v->transfer;
                    }
                }
                if ($context) {
                    break;
                }
                if( $translation_id == 'local_authdb_password_reminder' ) {
                    $context = $to;
                    break;
                } 
                
                // no break
            default:
                throw new TranslatableEmailUnknownContextException(get_class($to));
        }
        
        // compute lang from arguments
        $lang = null;
        if ($to instanceof User) {
            $lang = $to->lang;
            $to = $to->email;
        }
        if ($to instanceof Recipient) {
            $lang = $to->transfer->lang;
        }
        
        // Translate mail parts
        $email_translation = call_user_func_array(array(Lang::translateEmail($translation_id, $lang), 'replace'), $vars);
        
        // Build mail with body and footer
        $plain = $email_translation->plain->out();
        $html = $email_translation->html->out();
        
        // No need for translatable emails if only one language available ...
        if (count(Lang::getAvailableLanguages()) > 1) {
            // Create object
            $translatable = self::create($context, $translation_id, $vars);
            
            // Translate specific footer
            $footer_translation = Lang::translateEmail('translate_email_footer', $lang)->r($translatable);
            
            $plain .= "\n\n".$footer_translation->plain->out();
            $html .= "\n\n".$footer_translation->html->out();
        }

        
        $mail = new ApplicationMail(new Translation(array(
            'subject' => $email_translation->subject->out(),
            'plain' => $plain,
            'html' => $html,
        )));

        try {
            self::rateLimit( true, $translation_id, $context, ...$vars );
        }
        catch ( RateLimitException $e ) {
            Logger::info("rate limiting action");
            $mail->setReallySend( false );
        }
        
        // Add recipient
        $mail->to($to);
        
        return $mail;
    }

    /**
     * There are two cases for a rateLimit():
     * A) Calling early before an action is performed to see if the limit is reached and
     *    if so then throwing an error rather than doing anything
     * B) Calling once an action has been performed and a resulting email is about to be sent.
     *    In this case we may wish to limit email creation but can not stop the action as it has already been performed.
     *
     * For case (B) you should catch RateLimitException and avoid sending the email.
     * It is useful for the exception to be thrown from this method so that the exception
     * can access all the state that would have been used for the ratelimit record. For example,
     * event type, sender class and id etc.
     *
     */
    public static function rateLimit( $updateDatabase, $translation_id, DBObject $to )
    {
        $vars = array_slice(func_get_args(), 2);
        array_unshift($vars, $to);

        $action = 'email';
        $event  = $translation_id;
        $author = null;
        if( Auth::isGuest() ) {
            $author = AuthGuest::getGuest();
        }
        if( Auth::isAuthenticated()) {
            $author = Auth::user();
        }
        $author_context_type = 'unknown';
        $author_context_id   = 'unknown';
        if( $author ) {
            $author_context_type = get_class($author);
            $author_context_id   = $author->id;
        }
        $target = self::getContext($translation_id, $to, $vars );
        $target_context_type = get_class($target);
        $target_context_id   = $target->id;

        RateLimitHistory::rateLimit( $updateDatabase
                                   , $author_context_type, $author_context_id
                                   , $action, $event
                                   , $target_context_type, $target_context_id );
    }
    
    /**
     * Send to recipient (be it Guest, Recipient ...)
     *
     * @param string $translation_id
     * @param DBObject $to recipient is also used to get the context
     * @param mixed * translation args
     */
    public static function quickSend($translation_id, DBObject $to)
    {
        $mail = call_user_func_array(get_called_class().'::prepare', func_get_args());
        
        $mail->setDebugTemplate($translation_id);
      
        $mail->send();
    }
    
    /**
     * Translate stored in given language
     *
     * @param string $lang lang code (use current env lang if null given)
     *
     * @return Translation
     */
    public function translate($lang = null)
    {
        // Recreate translation variables
        $variables = array();
        if ($this->variables) {
            foreach ($this->variables as $k => $v) {
                if (is_object($v)) {
                    $v = (array)$v;
                }
            
                // Reverse DBObject conversion
                if (array_key_exists('dbobject_type', $v) && array_key_exists('dbobject_id', $v)) {
                    $v = call_user_func($v['dbobject_type'].'::fromId', $v['dbobject_id']);
                }
            
                $variables[$k] = $v;
            }
        }
        
        // Translate mail
        $translation = Lang::translateEmail($this->translation_id, $lang);
        
        // Replace variables
        return call_user_func_array(array($translation, 'replace'), $variables);
    }
    
    /**
     * Getter
     *
     * @param string $property property to get
     *
     * @throws PropertyAccessException
     *
     * @return property value
     */
    public function __get($property)
    {
        if (in_array($property, array('id', 'context_type', 'context_id', 'token', 'translation_id', 'variables', 'created'))) {
            return $this->$property;
        }
        
        if ($property == 'context') {
            return call_user_func($this->context_type.'::fromId', $this->context_id);
        }
        
        if ($property == 'link') {
            return Utilities::http_build_query(
                array( 's'     => 'translate_email',
                       'token' => $this->token )
            );
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Setter
     *
     * @param string $property property to get
     * @param mixed $value value to set property to
     *
     * @throws PropertyAccessException
     */
    public function __set($property, $value)
    {
        throw new PropertyAccessException($this, $property);
    }


    /**
     * Clean old entries
     */
    public static function clean()
    {
        $days = Config::get('translatable_emails_lifetime');
        
        /** @var PDOStatement $statement */
        $statement = DBI::prepare('DELETE FROM '.self::getDBTable().' WHERE created < :date');
        $statement->execute(array(':date' => date('Y-m-d', time() - $days * 86400)));
    }
}
