<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2017, AARNet, Belnet, HEAnet, SURF, UNINETT
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * *	Redistributions of source code must retain the above copyright
 * 	notice, this list of conditions and the following disclaimer.
 * *	Redistributions in binary form must reproduce the above copyright
 * 	notice, this list of conditions and the following disclaimer in the
 * 	documentation and/or other materials provided with the distribution.
 * *	Neither the name of AARNet, Belnet, HEAnet, SURF and UNINETT nor the
 * 	names of its contributors may be used to endorse or promote products
 * 	derived from this software without specific prior written permission.
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

/**
 * This program will create a meduim sized dataset for testing FileSender
 * performance and migration. Some database performance issues will only show
 * up when a reasonable amount of data is available in the database. For example,
 * a sequential scan of a table might be a viable resolution when there are hundreds
 * of tuples but will likely give undesirable performance when there are 100k tuples.
 * Having a large dataset exported will also allow for migration tests to be
 * performed locally helping to resolve issues before users see them.
 *
 * Much of the work is handled using callbacks. For example, the performTransfers()
 * call passes each created transaction to an array of callbacks to allow for some files
 * to be downloaded, some transactions to be closed, and in general be able to create
 * a distribution of effects on a collection of transactions.
 */

require_once(dirname(__FILE__).'/../../includes/init.php');

AuthLocal::setUser('uid-initial', 'email' );
Logger::info('Dataset creation started');

$creator  = new TestDatabaseCreator();
$cred     = $creator->getTestDatabaseCredentials();
$tc       = $creator->getTestDatabaseTransfers();
$user     = null;
$transfer = null;




/**
 * Allows rate limiting for the below performTransfers callbacks
 * somebody using one of these objects only has to call shouldFire()
 * and if that is true then perform some work. This decouples how often 
 * something happens from the callback that is actually doing the work.
 */
class RateLimit {
    private $c = 0;
    private $firePoint = 1;
    /**
     * Fire every $firePoint+1 calls. A value of zero means 
     * we fire every time, a value of one is every second time
     * a value of 2 is every third time and so on.
     */
    public function __construct($firePoint = 0) {
        $this->firePoint = $firePoint + 1;
    }

    /**
     * The call is recorded and true is only returned if the caller
     * should run the real code. 
     *
     * The calling pattern is:
     * 
     * if(shouldFire()) { // do the stuff }
     */
    public function shouldFire() {
        $ret = false;
        $this->c++;
        if( $this->c == $this->firePoint ) {
            $this->c = 0;
            $ret = true;
        }
        return $ret;
    }
}

/**
 * This is like RateLimit but we allow a distribution of $target 
 * times to fire per $mx calls. For example RecipientsXperY(30,100) 
 * will fire the first 30 times it is called and then skip the next 70 times.
 */
class RecipientsXperY {
    private $target;
    private $mx;
    private $c = 0;
    public function __construct( $target = 50, $mx = 100 ) {
        $this->c = 0;
        $this->target = $target;
        $this->mx = $mx;
    }
    public function shouldFire() {
        $ret = false;
        $this->c++;
        if( $this->c < $this->target )
            $ret = true;
        if( $this->c > $this->mx )
            $this->c = 0;
        return $ret;
    }        
}

/**
 *  Create a Recipients per transfer profile like the following.
 *
 *   10,000   1 recip_per_transfer
 *   23,000   2 recip_per_transfer
 *    4,000   3 recip_per_transfer
 *      ...   n recip_per_transfer (tapering downwards)
 */
class addRecipients {
    public function __construct() {
        $this->rtwo   = new RecipientsXperY( 70, 100 );
        $this->rthree = new RecipientsXperY( 13, 100 );
        $this->rfour  = new RecipientsXperY(  3, 100 );
        $this->rfive  = new RecipientsXperY(  1, 100 );
    }
    function visitTransfer( $transfer ) {
        $recipient = $transfer->addRecipient('tester@localhost.localdomain');
        if( $this->rtwo->shouldFire()) {
            $recipient = $transfer->addRecipient('tester2@localhost.localdomain');
        }
        if( $this->rthree->shouldFire()) {
            $recipient = $transfer->addRecipient('tester3@localhost.localdomain');
        }
        if( $this->rfour->shouldFire()) {
            $recipient = $transfer->addRecipient('tester4@localhost.localdomain');
        }
        if( $this->rfive->shouldFire()) {
            $recipient = $transfer->addRecipient('tester5@localhost.localdomain');
        }
        return $recipient;
    }
}

/**
 * performTransfers callback that downloads one or more files from the transaction
 */
class DownloadFiles {
    private $v = 0;
    private $rateLimit = null;
    public function __construct($rateLimit) {
        $this->rateLimit = $rateLimit;
    }
    function visitTransfer( $transfer, $f, $recipient ) {
        if( $this->rateLimit->shouldFire() ) {
            Logger::logActivity(LogEventTypes::DOWNLOAD_STARTED, $f, $recipient);
            Logger::logActivity(LogEventTypes::DOWNLOAD_ENDED,   $f, $recipient);
        }
    }
}

/**
 * performTransfers callback that makes logActivity for a download of archive for the transaction
 */
class DownloadArchive {
    private $v = 0;
    private $rateLimit = null;
    public function __construct($rateLimit) {
        $this->rateLimit = $rateLimit;
    }
    function visitTransfer( $transfer, $f, $recipient ) {
        if( $this->rateLimit->shouldFire() ) {
            Logger::logActivity(LogEventTypes::ARCHIVE_DOWNLOAD_STARTED, $transfer, $recipient);
            Logger::logActivity(LogEventTypes::ARCHIVE_DOWNLOAD_ENDED,   $transfer, $recipient);
        }
    }
}

/**
 * performTransfers callback that close()s the transactions passed to it
 * you set how many are closed with the rateLimit argument
 */
class CloseTransaction {
    private $v = 0;
    private $rateLimit = null;
    private $isDeletedTransfer = false;
    
    public function __construct($rateLimit,$isDeletedTransfer = false) {
        $this->rateLimit = $rateLimit;
        $this->isDeletedTransfer = $isDeletedTransfer;
    }
    function visitTransfer( $transfer ) {
        if( $this->rateLimit->shouldFire() ) {
            $transfer->close($this->isDeletedTransfer);
        }
    }
}

try {

    // As at mid 2017 it takes about 1gb of RAM to push
    // 20k transactions in a single run.
    ini_set('memory_limit', '8G');

    // Actually sending email is very, very slow and
    // doesn't actually gain anything. This call disables the
    // sending of email but it is still recorded by FileSender.
    Mail::TESTING_SET_DO_NOT_SEND_EMAIL();

    if(!function_exists('finfo_open'))
        throw new Exception('File Info PHP extention is required but not found');

    $args = new Args(
        array(
            'h' => 'help',
            's:' => 'scale:',
        ));

    $args->getopts();
    $args->maybeDisplayHelpAndExit(
        'Create a populated test database for FileSender....'."\n\n" .
        'Usage '.basename(__FILE__).' -s|--scale=<1.0...0.01> '."\n" .
        "\t".'-s|--scale Amount of data to create 1.0 is full dataset 0.01 is 1% of data'."\n" .
        "\t\n"
    );
    $args->MergeShortToLong();
    $scaleFactor = $args->getArg('scale', false, 1.00 );
    $scaleFactor = $args->clamp( $scaleFactor, 0.01, 1.00 );
    echo "Using a scaling factor of: $scaleFactor \n";
    echo "A run with scale of 1.0 will likely take hours...\n";

    
    $cred->createUsers( 10000 * $scaleFactor );
    $cred->createGuests( 3000 * $scaleFactor );

    // This is a sanity check, if the below performTransfers()
    // calls are going to fail for simple reasons then we want
    // that to happen here first.
    $cred->forceCredentialsToDefaultUser();
    $transfer = $tc->createTransfer( 'file1',
                                     'testdriver test',
                                     'testdriver',
                                     array('encryption' => false,
                                           'email_upload_complete' => true ));
    $recipient = $transfer->addRecipient('tester@localhost.localdomain');
    $transfer->makeAvailable();

    // 
    // Create the transfer, file, recipient, download profile.
    //
    $tc->performTransfers( 30000 * $scaleFactor,  1,
                           new addRecipients(),
                           array(
                               new DownloadFiles(new RateLimit()),
                               new DownloadFiles(new RateLimit()),
                               new DownloadFiles(new RateLimit()),
                               new DownloadFiles(new RateLimit()),
                               new DownloadFiles(new RateLimit(3)),
                               new DownloadFiles(new RateLimit(7)),
                               new DownloadFiles(new RateLimit(13)),
                               new DownloadArchive(new RateLimit(1)),
                               new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(  3000 * $scaleFactor,  2,
                            new addRecipients(),
                            array(
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit(3)),
                                new DownloadFiles(new RateLimit(7)),
                                new DownloadFiles(new RateLimit(13)),
                                new DownloadArchive(new RateLimit(1)),
                                new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(  1000 * $scaleFactor,  3,
                            new addRecipients(),
                            array(
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit()),
                                new DownloadFiles(new RateLimit(3)),
                                new DownloadFiles(new RateLimit(7)),
                                new DownloadFiles(new RateLimit(13)),
                                new DownloadArchive(new RateLimit(1)),
                                new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(   500 * $scaleFactor,  4,
                             new addRecipients(),
                             array(
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit(3)),
                                 new DownloadFiles(new RateLimit(7)),
                                 new DownloadFiles(new RateLimit(13)),
                                 new DownloadArchive(new RateLimit(1)),
                                 new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(   300 * $scaleFactor,  5,
                             new addRecipients(),
                             array(
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit(3)),
                                 new DownloadFiles(new RateLimit(7)),
                                 new DownloadFiles(new RateLimit(13)),
                                 new DownloadArchive(new RateLimit(1)),
                                 new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(   200 * $scaleFactor,  6,
                             new addRecipients(),
                             array(
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit(3)),
                                 new DownloadFiles(new RateLimit(7)),
                                 new DownloadFiles(new RateLimit(13)),
                                 new DownloadArchive(new RateLimit(1)),
                                 new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(   100 * $scaleFactor,  7,
                             new addRecipients(),
                             array(
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit()),
                                 new DownloadFiles(new RateLimit(3)),
                                 new DownloadFiles(new RateLimit(7)),
                                 new DownloadFiles(new RateLimit(13)),
                                 new DownloadArchive(new RateLimit(1)),
                                 new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(    50 * $scaleFactor,  8,
                              new addRecipients(),
                              array(
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit(3)),
                                  new DownloadFiles(new RateLimit(7)),
                                  new DownloadFiles(new RateLimit(13)),
                                  new DownloadArchive(new RateLimit(1)),
                                  new CloseTransaction(    new RateLimit(1)) ));
    $tc->performTransfers(    30 * $scaleFactor,  9,
                              new addRecipients(),
                              array(
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit()),
                                  new DownloadFiles(new RateLimit(3)),
                                  new DownloadFiles(new RateLimit(7)),
                                  new DownloadFiles(new RateLimit(13)),
                                  new DownloadArchive(new RateLimit(1)),
                                  new CloseTransaction(    new RateLimit(1)) ));
    
    $bounce = TrackingEvent::create(
        TrackingEventTypes::BOUNCE,
        $recipient,
        null,
        'take care of the details'
    );
    $bounce->save();


    Logger::logActivity( LogEventTypes::TESTING_SIMPLELOG_ENTRY, $transfer );

} catch(Exception $e) {
    Logger::error('Dataset creation failed : '.$e->getMessage());
    die($e->getMessage()."\n\n");
}

exit(0);

