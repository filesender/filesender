<?php

/*
 * FileSender www.filesender.org
 * 
 * Copyright (c) 2009-2012, AARNet, Belnet, HEAnet, SURF, UNINETT
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

require_once dirname(__FILE__) . '/../common/CommonUnitTestCase.php';

/**
 * Tests for the expiry of user API secrets
 * (auth_remote_user_secret_max_age_days).
 *
 * The methods are called with a fixed $now wherever they accept one, so the
 * results don't depend on when the tests run.
 *
 * @backupGlobals disabled
 */
class UserAuthSecretExpiryTest extends CommonUnitTestCase
{
    /**
     * Fixed reference time (2027-01-15 08:00:00 UTC)
     */
    const NOW = 1800000000;

    /**
     * Maximum age used by the tests, in days
     */
    const DAYS = 30;

    /**
     * One day in seconds
     */
    const DAY = 86400;

    /**
     * @var User test user, the same for every test
     */
    private $user;

    /**
     * Get the test user and start every test with expiry disabled.
     */
    protected function setUp(): void
    {
        echo "UserAuthSecretExpiryTest@ " . date("Y-m-d H:i:s") . "\n\n";

        $authid = Authentication::ensureAuthIDFromSAMLUID('api-secret-expiry-test@example.org');
        $this->user = User::fromAuthId($authid);
        $this->setMaxAgeDays(0);
    }

    /**
     * Remove the test user's secret and disable expiry again.
     */
    protected function tearDown(): void
    {
        $this->user->authSecretDelete();
        $this->setMaxAgeDays(0);
    }

    /**
     * Set auth_remote_user_secret_max_age_days for the current test.
     *
     * @param int $days
     */
    private function setMaxAgeDays($days)
    {
        Config::localOverride('auth_remote_user_secret_max_age_days', $days);
    }

    /**
     * Read the test user's auth_secret_expires directly from the database,
     * bypassing any cached User object.
     *
     * @return string|null the stored value, null if empty
     */
    private function readStoredExpiry()
    {
        $statement = DBI::prepare('SELECT auth_secret_expires FROM '.User::getDBTable().' WHERE id = :id');
        $statement->execute(array(':id' => $this->user->id));
        $row = $statement->fetch();
        return $row ? $row['auth_secret_expires'] : null;
    }

    /**
     * With the setting at 0, creating a secret sets no expiry, the secret
     * never expires and extending does nothing.
     */
    public function testNoExpiryWhenDisabled()
    {
        $this->user->authSecretCreate();

        $this->assertNotNull($this->user->auth_secret);
        $this->assertNull($this->user->auth_secret_expires);
        $this->assertFalse($this->user->authSecretIsExpired(self::NOW + 1000 * self::DAY));
        $this->assertFalse($this->user->authSecretExtend(self::NOW));
        $this->assertNull($this->user->auth_secret_expires);
    }

    /**
     * With the setting at N, a new secret expires exactly N * 86400 s after
     * its creation.
     */
    public function testCreateSetsExpiry()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();

        $this->assertSame(
            $this->user->auth_secret_created + self::DAYS * self::DAY,
            $this->user->auth_secret_expires
        );
    }

    /**
     * The expiry is stored in the database and read back unchanged.
     */
    public function testExpiryIsStored()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();

        $this->assertSame(
            date('Y-m-d H:i:s', $this->user->auth_secret_expires),
            $this->readStoredExpiry()
        );
    }

    /**
     * Extending sets now + N days, also when the stored expiry is later,
     * so that a shortened setting applies at the next web login.
     */
    public function testExtendAlwaysCountsFromNow()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();

        $this->assertTrue($this->user->authSecretExtend(self::NOW + 100 * self::DAY));
        $this->assertSame(self::NOW + (100 + self::DAYS) * self::DAY, $this->user->auth_secret_expires);

        // An earlier "now" gives an earlier expiry, even though the stored one is later
        $this->assertTrue($this->user->authSecretExtend(self::NOW));
        $this->assertSame(self::NOW + self::DAYS * self::DAY, $this->user->auth_secret_expires);
    }

    /**
     * Extending without a secret does nothing.
     */
    public function testExtendWithoutSecret()
    {
        $this->setMaxAgeDays(self::DAYS);

        $this->assertFalse($this->user->authSecretExtend(self::NOW));
        $this->assertNull($this->user->auth_secret_expires);
    }

    /**
     * A secret is expired exactly at its expiry time, not a second before.
     */
    public function testExpiryBoundary()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();
        $this->user->authSecretExtend(self::NOW);

        $expires = self::NOW + self::DAYS * self::DAY;
        $this->assertFalse($this->user->authSecretIsExpired($expires - 1));
        $this->assertTrue($this->user->authSecretIsExpired($expires));
    }

    /**
     * A secret created while expiry was disabled has no expiry date. It is
     * not expired, authSecretEnsureExpiry() gives it one, and a second call
     * doesn't change it.
     */
    public function testMissingExpiryGetsGracePeriod()
    {
        $this->user->authSecretCreate();          // setting at 0: no expiry
        $this->setMaxAgeDays(self::DAYS);

        $this->assertNull($this->user->auth_secret_expires);
        $this->assertFalse($this->user->authSecretIsExpired(self::NOW + 1000 * self::DAY));

        $this->assertTrue($this->user->authSecretEnsureExpiry(self::NOW));
        $this->assertSame(self::NOW + self::DAYS * self::DAY, $this->user->auth_secret_expires);

        $this->assertFalse($this->user->authSecretEnsureExpiry(self::NOW + 5 * self::DAY));
        $this->assertSame(self::NOW + self::DAYS * self::DAY, $this->user->auth_secret_expires);
    }

    /**
     * Setting the value back to 0 ignores a stored expiry date, even one in
     * the past.
     */
    public function testDisablingIgnoresStoredExpiry()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();
        $this->user->authSecretExtend(self::NOW);

        $this->setMaxAgeDays(0);
        $this->assertFalse($this->user->authSecretIsExpired(self::NOW + 365 * self::DAY));
        $this->assertNotNull($this->user->auth_secret_expires);   // kept, only ignored
    }

    /**
     * Deleting a secret clears its expiry date, in the object and in the
     * database.
     */
    public function testDeleteClearsExpiry()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();

        $this->user->authSecretDelete();

        $this->assertNull($this->user->auth_secret);
        $this->assertNull($this->user->auth_secret_expires);
        $this->assertNull($this->readStoredExpiry());
    }

    /**
     * Deleting all secrets clears the expiry dates in the database.
     *
     * Note: this deletes the API secrets of all users in the test database.
     */
    public function testDeleteAllClearsExpiry()
    {
        $this->setMaxAgeDays(self::DAYS);
        $this->user->authSecretCreate();
        $this->assertNotNull($this->readStoredExpiry());

        User::authSecretDeleteAll();

        $this->assertNull($this->readStoredExpiry());
    }
}