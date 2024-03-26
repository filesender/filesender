<?php
  declare(strict_types=1);

  namespace PHP81_BC\Tests;

  use PHP81_BC\Tests\LocaleTests\Locale_en_EN_TestTrait;
  use PHP81_BC\Tests\LocaleTests\Locale_es_ES_TestTrait;
  use PHP81_BC\Tests\LocaleTests\Locale_eu_TestTrait;
  use PHP81_BC\Tests\LocaleTests\Locale_it_CH_TestTrait;
  use PHP81_BC\Tests\LocaleTests\Locale_it_IT_TestTrait;

  trait LocaleFormatterTestTrait {
    use Locale_en_EN_TestTrait;
    use Locale_es_ES_TestTrait;
    use Locale_eu_TestTrait;
    use Locale_it_CH_TestTrait;
    use Locale_it_IT_TestTrait;
  }
