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

require_once(FILESENDER_BASE.'/lib/vendor/autoload.php');

/**
 * Report class
 */
class Report
{
    /**
     * Related audit log entries
     */
    private $logs = array();
    
    /**
     * Target type
     */
    private $target = null;
    
    /**
     * Constructor
     *
     * @param DBObject $target target to get report about
     *
     * @throws AuthAuthenticationNotFoundException
     * @throws ReportFormatNotFoundException
     */
    public function __construct(DBObject $target)
    {
        $this->logs = array();
        switch (get_class($target)) {
            case 'Transfer': // Get all log about transfer or its related files and recipients
                $this->logs = AuditLog::fromTransfer($target);
                break;
            
            case 'File': // Get log about file life
            case 'Recipient': // Get log about recipient activity
                $this->logs = AuditLog::fromTarget($target);
                break;
            
            default: // Object type not handled
                throw new ReportUnknownTargetTypeException(get_class($target));
        }
        
        $this->target = $target;
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
        if (in_array($property, array(
            'logs',
            'target',
        ))) {
            return $this->$property;
        }
        
        if ($property == 'target_type') {
            return get_class($this->target);
        }
        
        if ($property == 'transfer') {
            switch ($this->target_type) {
            case 'Transfer': return $this->target;
            case 'Recipient':
            case 'File': return $this->target->transfer;
        }
        }
        
        throw new PropertyAccessException($this, $property);
    }
    
    /**
     * Sends report by email
     *
     * @param mixed $recipient User, email address
     */
    public function sendTo($recipient)
    {
        // try to get recipient's lang
        $lang = null;
        if (is_object($recipient) && ($recipient instanceof User)) {
            $lang = $recipient->lang;
        }
        
        // Get format, default if not defined
        $format = Config::get('report_format');
        if (!$format) {
            $format = ReportFormats::INLINE;
        }
        
        // Check format
        if (!ReportFormats::isValidName($format)) {
            throw new ReportUnknownFormatException($format);
        }
        
        // Need iconv utility for pdf rendering (dompdf dependency)
        if (($format == ReportFormats::PDF) && !extension_loaded('iconv')) {
            throw new ReportFormatNotAvailableException('iconv not found');
        }
        
        $content = array('plain' => '', 'html' => '');
        $attachment = null;
        
        // Build mail body depending on format
        if ($format == ReportFormats::PDF) {
            $html = Template::process('!report_pdf', array('report' => $this));
            
            $styles = array(
                'www/css/mail.css',
                'www/skin/mail.css',
                'www/css/pdf.css',
                'www/skin/pdf.css'
            );
            $css = '';
            foreach ($styles as $cssfile) {
                if (file_exists(FILESENDER_BASE.'/'.$cssfile)) {
                    $css .= "\n\n".file_get_contents(FILESENDER_BASE.'/'.$cssfile);
                }
            }
            $css = trim($css);
            
            if ($css) {
                $html = '<style type="text/css">'.$css.'</style>'.$html;
            }
            $pdf = new Dompdf\Dompdf();
            $pdf->load_html($html);
            $pdf->render();
            
            $attachment = new MailAttachment('report_'.strtolower($this->target_type).'_'.$this->target->id.'.pdf');
            $attachment->content = $pdf->output();
        } else { // INLINE
            $content['plain'] = Template::process('!report_plain', array('report' => $this));
            $content['html'] = Template::process('!report_html', array('report' => $this));
        }
        
        // Build translated email
        $lid = ($format == ReportFormats::INLINE) ? 'inline' : 'attached';
        $mail = TranslatableEmail::prepare('report_'.$lid, $recipient, $this->target, array(
            'target' => array(
                'type' => $this->target_type,
                'id' => $this->target->id
            ),
            'content' => $content,
        ));
        
        $mail->setDebugTemplate($lid);

        // Attach report file if any
        if ($attachment) {
            $mail->attach($attachment);
        }
        
        // Send the report
        $mail->send();
    }
}
