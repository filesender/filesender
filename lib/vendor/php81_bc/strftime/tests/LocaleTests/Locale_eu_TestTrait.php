<?php
  declare(strict_types=1);

  namespace PHP81_BC\Tests\LocaleTests;

  use DateTimeImmutable;
  use function PHP81_BC\strftime;

  trait Locale_eu_TestTrait {
    public function testLocale_eu () {
      $locale = 'eu';

      $result = strftime('%a', '20220306 13:02:03', $locale);
      $this->assertEquals('ig.', $result, '%a: An abbreviated textual representation of the day');

      $result = strftime('%A', '20220306 13:02:03', $locale);
      $this->assertEquals('igandea', $result, '%A: A full textual representation of the day');

      $result = strftime('%b', '20220306 13:02:03', $locale);
      $this->assertEquals('mar.', $result, '%b: Abbreviated month name, based on the locale');

      $result = strftime('%B', '20220306 13:02:03', $locale);
      $this->assertThat($result, $this->logicalOr(
        $this->equalTo('martxoak'),
        $this->equalTo('martxoa')   // PHP-7.4, PHP-8.3
      ), '%B: Full month name, based on the locale');

      $result = strftime('%h', '20220306 13:02:03', $locale);
      $this->assertEquals('mar.', $result, '%h: Abbreviated month name, based on the locale (an alias of %b)');

      $result = strftime('%X', '20220306 13:02:03', $locale);
      $this->assertEquals('13:02:03', $result, '%X: Preferred time representation based on locale, without the date');

      $result = strftime('%c', '20220306 13:02:03', $locale);
      $this->assertThat($result, $this->logicalOr(
        $this->equalTo('2022(e)ko martxoaren 6(a) 13:02'),  // PHP-7.4
        $this->equalTo('2022(e)ko martxoakren 6(a) 13:02'),
        $this->equalTo('2022(e)ko martxoaren 6(a) (13:02)') // PHP-8.3
      ), '%c: Preferred date and time stamp based on locale');

      $result = strftime('%x', '20220306 13:02:03', $locale);
      $this->assertEquals('22/3/6', $result, '%x: Preferred date representation based on locale, without the time');

      // 1st October 1582 in proleptic Gregorian is the same date as 21st September 1582 Julian
      $prolepticTimestamp = DateTimeImmutable::createFromFormat('Y-m-d|', '1582-10-01')->getTimestamp();
      $result = strftime('%F: %x', $prolepticTimestamp, $locale);
      $this->assertEquals('1582-10-01: 82/10/1', $result, '1st October 1582 in proleptic Gregorian is the same date as 21st September 1582 Julian');

      // In much of Europe, the 10th October 1582 never existed
      $prolepticTimestamp = DateTimeImmutable::createFromFormat('Y-m-d|', '1582-10-10')->getTimestamp();
      $result = strftime('%F: %x', $prolepticTimestamp, $locale);
      $this->assertEquals('1582-10-10: 82/10/10', $result, 'In much of Europe, the 10th October 1582 never existed');

      // The 15th October was the first day after the cutover, after which both systems agree
      $prolepticTimestamp = DateTimeImmutable::createFromFormat('Y-m-d|', '1582-10-15')->getTimestamp();
      $result = strftime('%F: %x', $prolepticTimestamp, $locale);
      $this->assertEquals('1582-10-15: 82/10/15', $result, 'The 15th October was the first day after the cutover, after which both systems agree');
    }
  }
