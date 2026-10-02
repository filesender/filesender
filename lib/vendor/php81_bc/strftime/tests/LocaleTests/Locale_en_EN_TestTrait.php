<?php
  declare(strict_types=1);

  namespace PHP81_BC\Tests\LocaleTests;

  use DateTimeImmutable;
  use function PHP81_BC\strftime;

  trait Locale_en_EN_TestTrait {
    public function testLocale_en_EN () {
      $locale = 'en-EN';

      $result = strftime('%a', '20220306 13:02:03', $locale);
      $this->assertEquals('Sun', $result, '%a: An abbreviated textual representation of the day');

      $result = strftime('%A', '20220306 13:02:03', $locale);
      $this->assertEquals('Sunday', $result, '%A: A full textual representation of the day');

      $result = strftime('%b', '20220306 13:02:03', $locale);
      $this->assertEquals('Mar', $result, '%b: Abbreviated month name, based on the locale');

      $result = strftime('%B', '20220306 13:02:03', $locale);
      $this->assertEquals('March', $result, '%B: Full month name, based on the locale');

      $result = strftime('%h', '20220306 13:02:03', $locale);
      $this->assertEquals('Mar', $result, '%h: Abbreviated month name, based on the locale (an alias of %b)');

      $result = strftime('%X', '20220306 13:02:03', $locale);
      $this->assertThat($result, $this->logicalOr(
        $this->equalTo('1:02:03 PM'), // PHP-7
        $this->equalTo('13:02:03')   // PHP-8
      ), '%X: Preferred time representation based on locale, without the date');

      $result = strftime('%c', '20220306 13:02:03', $locale);
      $this->assertThat($result, $this->logicalOr(
        $this->equalTo('March 6, 2022 at 1:02 PM'), // PHP-7
        $this->equalTo('March 6, 2022 at 13:02')   // PHP-8
      ), '%c: Preferred date and time stamp based on locale');

      $result = strftime('%x', '20220306 13:02:03', $locale);
      $this->assertEquals('3/6/22', $result, '%x: Preferred date representation based on locale, without the time');

      // 1st October 1582 in proleptic Gregorian is the same date as 21st September 1582 Julian
      $prolepticTimestamp = DateTimeImmutable::createFromFormat('Y-m-d|', '1582-10-01')->getTimestamp();
      $result = strftime('%F: %x', $prolepticTimestamp, $locale);
      $this->assertEquals('1582-10-01: 10/1/82', $result, '1st October 1582 in proleptic Gregorian is the same date as 21st September 1582 Julian');

      // In much of Europe, the 10th October 1582 never existed
      $prolepticTimestamp = DateTimeImmutable::createFromFormat('Y-m-d|', '1582-10-10')->getTimestamp();
      $result = strftime('%F: %x', $prolepticTimestamp, $locale);
      $this->assertEquals('1582-10-10: 10/10/82', $result, 'In much of Europe, the 10th October 1582 never existed');

      // The 15th October was the first day after the cutover, after which both systems agree
      $prolepticTimestamp = DateTimeImmutable::createFromFormat('Y-m-d|', '1582-10-15')->getTimestamp();
      $result = strftime('%F: %x', $prolepticTimestamp, $locale);
      $this->assertEquals('1582-10-15: 10/15/82', $result, 'The 15th October was the first day after the cutover, after which both systems agree');
    }
  }
