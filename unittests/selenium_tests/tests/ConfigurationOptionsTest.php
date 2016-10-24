<?php

require_once 'unittests/selenium_tests/SeleniumTest.php';

class TransferExpiredTest extends SeleniumTest
{

    protected $start_url_path = '';

    protected $use_mails = true;

    public function testSendingToRecipients()
    {
        $this->setupAuthenticated();

        $current_email_address = Config::get('auth_sp_fake_email');

        $this->byClassName('toggle_advanced_options')->click();

        $this->checkCheckBox('add_me_to_recipients', false);

        $this->checkCheckBox('email_me_on_expire', true);

        $this->checkCheckBox('email_daily_statistics', true);

        $this->checkCheckbox('email_me_copies', false);

        $this->checkCheckbox('email_upload_complete', true);

        $this->checkCheckbox('email_download_complete', true);

        $this->checkCheckbox('enable_recipient_email_download_complete', false);


        // check expired
        $checkbox = $this->byCssSelector('[name="add_me_to_recipients"]');
        if($checkbox->selected())
        {
            $checkbox->click();
        }

        $recipients = ['usera@filetestertest.test', 'userb@filetestertest.test', 'userc@filetestertest.test'];
        $subject = 'testSubject_' . rand(0, 100);
        $content = 'testContent_' . rand(0, 100);

        $file_data_contents = $this->uploadFiles();


        $this->sendMessageToRecipients($recipients, $subject, $content);

        $this->checkMessageSent($recipients, $subject, $content);

        $this->checkEmailUploadComplete(true);

        $this->checkRecipientDownloads($recipients, $current_email_address, $file_data_contents, false, true, false);

        $this->checkTransferClosing(true, true);


    }

    public function testSendingToRecipientsAndMyself()
    {
        $this->setupAuthenticated();

        $current_email_address = Config::get('auth_sp_fake_email');

        $this->byClassName('toggle_advanced_options')->click();

        $this->checkCheckBox('add_me_to_recipients', true);

        $this->checkCheckBox('email_me_on_expire', false);

        $this->checkCheckBox('email_daily_statistics', false);

        $this->checkCheckbox('email_me_copies', false);

        $this->checkCheckbox('email_upload_complete', false);

        $this->checkCheckbox('email_download_complete', false);

        $this->checkCheckbox('enable_recipient_email_download_complete', true);


        $recipients = ['usera@filetestertest.test', 'userb@filetestertest.test', 'userc@filetestertest.test'];
        $subject = 'testSubject_' . rand(0, 100);
        $content = 'testContent_' . rand(0, 100);

        $file_data_contents = $this->uploadFiles();


        $this->sendMessageToRecipients($recipients, $subject, $content);

        $recipients[] = $current_email_address;

        $this->checkMessageSent($recipients, $subject, $content);

        $this->checkEmailUploadComplete(false);

        $this->checkRecipientDownloads($recipients, $current_email_address, $file_data_contents, false, false, true);

        $this->checkTransferClosing(false, false);

    }

    public function testSendingToRecipientsSendCopies()
    {
        $this->setupAuthenticated();

        $current_email_address = Config::get('auth_sp_fake_email');

        $this->byClassName('toggle_advanced_options')->click();

        $this->checkCheckBox('add_me_to_recipients', false);

        $this->checkCheckBox('email_me_on_expire', false);

        $this->checkCheckBox('email_daily_statistics', false);

        $this->checkCheckbox('email_me_copies', true);

        $this->checkCheckbox('email_upload_complete', false);

        $this->checkCheckbox('email_download_complete', false);

        $this->checkCheckbox('enable_recipient_email_download_complete', false);

        // check expired
        $checkbox = $this->byCssSelector('[name="add_me_to_recipients"]');
        if($checkbox->selected())
        {
            $checkbox->click();
        }

        $recipients = ['usera@filetestertest.test', 'userb@filetestertest.test', 'userc@filetestertest.test'];
        $subject = 'testSubject_' . rand(0, 100);
        $content = 'testContent_' . rand(0, 100);

        $file_data_contents = $this->uploadFiles();

        $this->sendMessageToRecipients($recipients, $subject, $content);

        $this->checkMessageSent($current_email_address);

        $this->checkRecipientDownloads($recipients, $subject, $content, $current_email_address, $file_data_contents, true, false, false);

        $this->checkEmailUploadComplete(false);

    }


    private function sendMessageToRecipients(array $recipients, $subject, $content)
    {

        $checkbox = $this->byCssSelector('[name="get_a_link"]');
        if($checkbox->selected())
        {
            $checkbox->click();
        }

        foreach($recipients as $recipient)
        {
            $this->byId('to')->value($recipient);
            $this->byId('[name="subject"]')->click();
        }
        $this->byId('[name="subject"]')->value($subject);
        $this->byId('[name="message"]')->value($content);

        $this->byCssSelector('.start.ui-button')->click();
    }

    private function checkMessageSent($current_email_address)
    {
        $mail_found = false;
        foreach($this->yieldRecipientMails($current_email_address) as $mail_file) {
            $mail_contents = file_get_contents($mail_file);
            if (strpos($mail_contents, 'TEMPLATE: upload_complete') !== false) {
                $mail_found = true;
            }
        }

        $this->assertTrue($mail_found);

    }

    private function uploadFiles()
    {
        ${"temp"} = $this->execute(array('script' => "var file_upload_container = document.getElementsByClassName('file_selector')[0];file_upload_container.style.display='block';", 'args' => array()));

        $test1_file = "unittests/selenium_tests/assets/124bytes.txt";
        $test1_file_data = file_get_contents($test1_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test1_file);

        $test2_file = "unittests/selenium_tests/assets/125bytes.txt";
        $test2_file_data = file_get_contents($test2_file);
        $this->sendKeys($this->byCssSelector(".file_selector input[name=\"files\"]"), $test2_file);

        return [$test1_file_data, $test2_file_data];
    }

    private function checkCheckbox($name, $checked = true)
    {
        $checkbox = $this->byCssSelector('[name="'.$name.'"]');
        if($checkbox->selected() != $checked)
        {
            $checkbox->click();
        }
    }
    private function checkRecipientDownloads($recipients, $subject, $content, $current_email_address, $file_data_contents, $check_email_copies, $check_email_download_complete, $check_recipients_email_download_complete)
    {
        $current_user_email_count = count($this->yieldRecipientMails($current_email_address));
        $current_user_new_email_count = $current_user_email_count;

        foreach($recipients as $recipient)
        {
            $mail_found = false;
            foreach($this->yieldRecipientMails($recipient) as $mail_file)
            {
                $mail_contents = file_get_contents($mail_file);

                if(strpos($mail_contents, 'TEMPLATE: transfer_available') !== false)
                {
                    $mail_found = true;
                    $this->assertContains($subject, $mail_contents);
                    $this->assertContains($content, $mail_contents);
                    if($check_email_copies)
                    {
                        $this->assertContains('BCC: '.$current_email_address, $mail_contents);
                    }
                    break;
                }

            }
            $this->assertTrue($mail_found);

            $this->assertTrue(preg_match('/https:\/\/file_sender\.app\/filesender\/\?s=download&amp;token=(.*)?/', $mail_contents, $matches));

            $token = $matches[1];

            // check if the download url works
            $this->checkDownloadUrl('https://file_sender.app/filesender/?s=download&token='.$token, $file_data_contents);

            // the recipient just downloaded all the files + the total zip, add it to the total of notifications the current user should be getting
            if($check_email_download_complete)
            {
                $current_user_new_email_count += count($file_data_contents)+1;
            }
        }

        $this->assertEquals($current_user_new_email_count, count($this->yieldRecipientMails($current_email_address)));




    }

    private function yieldRecipientMails($recipient_id)
    {
        $folder_name = 'testmails/'.$recipient_id.'/';
        $dir = new DirectoryIterator($folder_name);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot()) {
                if(preg_match('/^(\d+)\.mail$/', $fileinfo->getFilename(), $matches))
                {
                    yield $folder_name.$fileinfo->getFilename();
                }
            }
        }
    }

    private function checkTransferClosing($expected_expiration_mail, $expected_daily_statistics_mail)
    {

    }

}
