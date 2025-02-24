<?php

declare(strict_types=1);

namespace LaminasTest\Form\Element;

use DateTime;
use Laminas\Form\Element\DateTimeSelect as DateTimeSelectElement;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\Validator\Date;
use PHPUnit\Framework\TestCase;

use function get_class;

final class DateTimeSelectTest extends TestCase
{
    public function testProvidesInputSpecificationThatIncludesValidatorsBasedOnAttributes(): void
    {
        $element = new DateTimeSelectElement();

        $inputSpec = $element->getInputSpecification();
        $this->assertArrayHasKey('validators', $inputSpec);
        $this->assertIsArray($inputSpec['validators']);

        $expectedClasses = [
            Date::class,
        ];
        foreach ($inputSpec['validators'] as $validator) {
            $class = get_class($validator);
            $this->assertContains($class, $expectedClasses, $class);
            switch ($class) {
                case Date::class:
                    $this->assertEquals('Y-m-d H:i:s', $validator->getFormat());
                    break;
                default:
                    break;
            }
        }
    }

    public function testInputSpecificationFilterIfSecondNotProvided(): void
    {
        $element     = new DateTimeSelectElement('test');
        $factory     = new InputFilterFactory();
        $inputFilter = $factory->createInputFilter([
            'test' => $element->getInputSpecification(),
        ]);
        $inputFilter->setData([
            'test' => [
                'year'   => '2013',
                'month'  => '02',
                'day'    => '07',
                'hour'   => '03',
                'minute' => '14',
            ],
        ]);
        $this->assertTrue($inputFilter->isValid());
    }

    public function testCanSetDateFromDateTime(): void
    {
        $element = new DateTimeSelectElement();
        $element->setValue(new DateTime('2012-09-24 03:04:05'));

        $this->assertEquals('2012', $element->getYearElement()->getValue());
        $this->assertEquals('09', $element->getMonthElement()->getValue());
        $this->assertEquals('24', $element->getDayElement()->getValue());
        $this->assertEquals('03', $element->getHourElement()->getValue());
        $this->assertEquals('04', $element->getMinuteElement()->getValue());
        $this->assertEquals('05', $element->getSecondElement()->getValue());
    }

    public function testCanSetDateFromString(): void
    {
        $element = new DateTimeSelectElement();
        $element->setValue('2012-09-24 03:04:05');

        $this->assertEquals('2012', $element->getYearElement()->getValue());
        $this->assertEquals('09', $element->getMonthElement()->getValue());
        $this->assertEquals('24', $element->getDayElement()->getValue());
        $this->assertEquals('03', $element->getHourElement()->getValue());
        $this->assertEquals('04', $element->getMinuteElement()->getValue());
        $this->assertEquals('05', $element->getSecondElement()->getValue());
    }

    public function testCanGetValue(): void
    {
        $element = new DateTimeSelectElement();
        $element->setValue(new DateTime('2012-09-24 03:04:05'));

        $this->assertEquals('2012-09-24 03:04:05', $element->getValue());
    }

    public function testThrowsOnInvalidValue(): void
    {
        $element = new DateTimeSelectElement();
        $this->expectException(InvalidArgumentException::class);
        $element->setValue('hello world');
    }

    public function testUseDefaultValueForSecondsIfNotProvided(): void
    {
        $element = new DateTimeSelectElement();
        $element->setValue([
            'year'   => '2012',
            'month'  => '09',
            'day'    => '24',
            'hour'   => '03',
            'minute' => '04',
        ]);

        $this->assertEquals('2012', $element->getYearElement()->getValue());
        $this->assertEquals('09', $element->getMonthElement()->getValue());
        $this->assertEquals('24', $element->getDayElement()->getValue());
        $this->assertEquals('03', $element->getHourElement()->getValue());
        $this->assertEquals('04', $element->getMinuteElement()->getValue());
        $this->assertEquals('00', $element->getSecondElement()->getValue());
    }

    public function testCloningPreservesCorrectValues(): void
    {
        $element = new DateTimeSelectElement();
        $element->setValue(new DateTime('2014-01-02 03:04:05'));

        $cloned = clone $element;

        $this->assertEquals('2014', $cloned->getYearElement()->getValue());
        $this->assertEquals('01', $cloned->getMonthElement()->getValue());
        $this->assertEquals('02', $cloned->getDayElement()->getValue());
        $this->assertEquals('03', $cloned->getHourElement()->getValue());
        $this->assertEquals('04', $cloned->getMinuteElement()->getValue());
        $this->assertEquals('05', $cloned->getSecondElement()->getValue());
    }

    public function testNullSetValueIsSemanticallyTodayWithoutEmptyOption(): void
    {
        $element = new DateTimeSelectElement('foo');
        $element->setShouldCreateEmptyOption(false);
        $now = new DateTime();
        $element->setValue(null);
        $value = $element->getValue();
        // the getValue() function returns the date in 'Y-m-d' format
        $this->assertEquals($now->format('Y-m-d H:i:s'), $value);
    }

    public function testNullSetValueIsNullWithEmptyOption(): void
    {
        $element = new DateTimeSelectElement('foo');
        $element->setShouldCreateEmptyOption(true);
        $element->setValue(null);
        $value = $element->getValue();
        $this->assertEquals(null, $value);
    }

    public function testSettingTimeOnlyUsesCurrentDate(): void
    {
        $now     = new DateTime();
        $element = new DateTimeSelectElement('foo');
        $element->setShouldCreateEmptyOption(true);
        $element->setValue([
            'year'   => null,
            'month'  => null,
            'day'    => null,
            'hour'   => $now->format('H'),
            'minute' => $now->format('i'),
            'second' => $now->format('s'),
        ]);
        $value = $element->getValue();
        $this->assertEquals($now->format('Y-m-d H:i:s'), $value);
    }

    public function testSettingDateOnlyUsesMidnightTime(): void
    {
        $now     = new DateTime();
        $element = new DateTimeSelectElement('foo');
        $element->setShouldCreateEmptyOption(true);
        $element->setValue([
            'year'   => $now->format('Y'),
            'month'  => $now->format('m'),
            'day'    => $now->format('d'),
            'hour'   => null,
            'minute' => null,
            'second' => null,
        ]);
        $value = $element->getValue();
        $this->assertEquals($now->format('Y-m-d 00:00:00'), $value);
    }
}
