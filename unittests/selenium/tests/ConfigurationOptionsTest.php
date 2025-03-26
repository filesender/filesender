<?php

require_once 'unittests/selenium/SeleniumTest.php';

class ConfigurationOptionsTest extends SeleniumTest {

    protected $start_url_path = '';
    protected $use_mails = true;

    public function testSendingToRecipients() {
        $this->setupAuthenticated();
        $current_email_address = Config::get('auth_sp_fake_email');


        $this->showFileUploader();
        $fp1 = $this->addFile( "124bytes.txt" );
        $fp2 = $this->addFile( "125bytes.txt" );
        $this->stageXContinue(1);
        
        $this->ensureTransferByEmail();
        $this->uploadPageStage2ShowAdvancedOptions();
        $this->ensureOptions( array(
            'add_me_to_recipients' => false,
            'email_me_on_expire' => true,
            'email_daily_statistics' => true,
            'email_me_copies' => false,
            'email_upload_complete' => true,
            'email_download_complete' => true,
            'enable_recipient_email_download_complete' => false
            ));

            
        $recipients = array('usera@filetestertest.test', 'userb@filetestertest.test', 'userc@filetestertest.test');
        $subject = 'testSubject_' . rand(0, 100);
        $content = 'testContent_' . rand(0, 100);

        $this->sendMessageToRecipients($recipients, $subject, $content);

        $this->waitForUploadCompleteDialog( false );

        // if no time outs from above we assert ok
        $this->assertTrue(true);

    }

    public function testSendingToRecipientsAndMyself() {
        $this->setupAuthenticated();
        $current_email_address = Config::get('auth_sp_fake_email');

        $this->showFileUploader();
        $fp1 = $this->addFile( "124bytes.txt" );
        $fp2 = $this->addFile( "125bytes.txt" );
        $this->stageXContinue(1);

        $this->ensureTransferByEmail();
        $this->uploadPageStage2ShowAdvancedOptions();
        $this->ensureOptions( array(
            'add_me_to_recipients' => true,
            'email_me_on_expire' => false,
            'email_daily_statistics' => false,
            'email_me_copies' => false,
            'email_upload_complete' => false,
            'email_download_complete' => false,
            'enable_recipient_email_download_complete' => true
            ));



        
        $recipients = array('usera@filetestertest.test', 'userb@filetestertest.test', 'userc@filetestertest.test');
        $subject = 'testSubject_' . rand(0, 100);
        $content = 'testContent_' . rand(0, 100);
        $this->sendMessageToRecipients($recipients, $subject, $content);

        $this->waitForUploadCompleteDialog( false );

        // if no time outs from above we assert ok
        $this->assertTrue(true);
    }

    

    public function testSendingToRecipientsSendCopies() {
        $this->setupAuthenticated();
        $current_email_address = Config::get('auth_sp_fake_email');

        $this->showFileUploader();
        $fp1 = $this->addFile( "124bytes.txt" );
        $fp2 = $this->addFile( "125bytes.txt" );
        $this->stageXContinue(1);

        $this->ensureTransferByEmail();
        $this->uploadPageStage2ShowAdvancedOptions();
        $this->ensureOptions( array(
            'add_me_to_recipients' => false,
            'email_me_on_expire' => false,
            'email_daily_statistics' => false,
            'email_me_copies' => true,
            'email_upload_complete' => false,
            'email_download_complete' => false,
            'enable_recipient_email_download_complete' => false
            ));

        

        $recipients = array('usera@filetestertest.test', 'userb@filetestertest.test', 'userc@filetestertest.test');
        $subject = 'testSubject_' . rand(0, 100);
        $content = 'testContent_' . rand(0, 100);

        $this->sendMessageToRecipients($recipients, $subject, $content);

        $this->waitForUploadCompleteDialog( false );

        // if no time outs from above we assert ok
        $this->assertTrue(true);
        
    }

    
    private function sendMessageToRecipients(array $recipients, $subject, $content) {

        $this->ensureTransferByEmail();
        
        foreach ($recipients as $recipient) {
            $this->byId('to')->value($recipient);
            $this->byId('subject')->click();
        }
        $this->byId('subject')->value($subject);
        $this->byCss('[name="message"]')->value($content);

        $this->stageXContinue(2);
        
    }

    private function checkMessageSent($current_email_address) {
        $mail_found = false;
        foreach ($this->yieldRecipientMails($current_email_address) as $mail_file) {
            $mail_contents = file_get_contents($mail_file);
            if (strpos($mail_contents, 'TEMPLATE: upload_complete') !== false) {
                $mail_found = true;
            }
        }

        $this->assertTrue($mail_found);
    }


    private function checkRecipientDownloads($recipients, $subject, $content, $current_email_address, $file_data_contents, $check_email_copies, $check_email_download_complete, $check_recipients_email_download_complete) {
        $current_user_email_count = count($this->yieldRecipientMails($current_email_address));
        $current_user_new_email_count = $current_user_email_count;

        foreach ($recipients as $recipient) {
            $mail_found = false;
            foreach ($this->yieldRecipientMails($recipient) as $mail_file) {
                $mail_contents = file_get_contents($mail_file);

                if (strpos($mail_contents, 'TEMPLATE: transfer_available') !== false) {
                    $mail_found = true;
                    $this->assertContains($subject, $mail_contents);
                    $this->assertContains($content, $mail_contents);
                    if ($check_email_copies) {
                        $this->assertContains('BCC: ' . $current_email_address, $mail_contents);
                    }
                    break;
                }
            }
            $this->assertTrue($mail_found);

            $this->assertTrue(preg_match('/https:\/\/file_sender\.app\/filesender\/\?s=download&amp;token=(.*)?/', $mail_contents, $matches));

            $token = $matches[1];

            // check if the download url works
            $this->checkDownloadUrl('https://file_sender.app/filesender/?s=download&token=' . $token, $file_data_contents);

            // the recipient just downloaded all the files + the total zip, add it to the total of notifications the current user should be getting
            if ($check_email_download_complete) {
                $current_user_new_email_count += count($file_data_contents) + 1;
            }
        }

        $this->assertEquals($current_user_new_email_count, count($this->yieldRecipientMails($current_email_address)));
    }

    private function yieldRecipientMails($recipient_id) {

        $folder_names = array();
        $folder_name = getcwd() . '\\testmails\\' . $recipient_id . '\\';
        //print_r($folder_name); exit;
        $dir = new DirectoryIterator($folder_name);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if (preg_match('/^(\d+)\.mail$/', $fileinfo->getFilename(), $matches)) {
                    $folder_names[] = $folder_name . $fileinfo->getFilename();
                }
            }
        }

        return $folder_names;
    }


}
