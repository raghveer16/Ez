<?php

/*
 * EZ-AD Library
 * Copyright (c) 2014 CAS Communications. All Rights Reserved.
 */

namespace EzAd\Commerce;

/**
 * Representation of money, with a value, currency, and precision. Contains mathematical methods
 * that use bcmath to avoid floating point errors. Instances of this class are immutable, the methods
 * will return new Money instances with the operation applied.
 *
 * @package EzAd\Commerce
 */
class Money
{
    /**
     * @var string
     */
    private $value = '0.00';

    /**
     * @var int
     */
    private $precision;

    /**
     * @var string
     */
    private $currency;

    /**
     * @param $value
     * @param int $precision
     * @param string $currency
     */
    public function __construct($value, $precision = 2, $currency = 'USD')
    {
        $this->precision = $precision;
        $this->currency = $currency;
        $this->value = number_format($value, $precision, '.', '');
    }

    public static function coerce($value, $precision = 2, $currency = 'USD')
    {
        if ( is_numeric($value) ) {
            return new Money($value, $precision, $currency);
        }

        if ( $value instanceof Money ) {
            return $value;
        }

        throw new \InvalidArgumentException('Value could not be coerced into a Money instance');
    }

    /**
     * @param $other
     * @throws \InvalidArgumentException
     * @return Money
     */
    private function prepareForOperation($other)
    {
        if ( is_numeric($other) ) {
            return new Money($other, $this->precision, $this->currency);
        }

        if ( $other instanceof Money ) {
            if ( $other->precision != $this->precision || $other->currency != $this->currency ) {
                throw new \InvalidArgumentException('Other Money object must have the same precision and currency');
            }
            return $other;
        }

        throw new \InvalidArgumentException('Value passed must be numeric or an instance of Money');
    }

    /**
     * @param $value
     * @return Money
     */
    private function cloneWithValue($value)
    {
        return new Money($value, $this->precision, $this->currency);
    }

    /**
     * @param $other
     * @param $func
     * @return Money
     */
    private function operation($other, $func)
    {
        $other = $this->prepareForOperation($other);
        $value = $func($this->value, $other->value, $this->precision);
        return $this->cloneWithValue($value);
    }

    /**
     * @param $other
     * @return Money
     */
    public function add($other)
    {
        return $this->operation($other, 'bcadd');
    }

    /**
     * @param $other
     * @return Money
     */
    public function sub($other)
    {
        return $this->operation($other, 'bcsub');
    }

    /**
     * @param $other
     * @return Money
     */
    public function mul($other)
    {
        return $this->operation($other, 'bcmul');
    }

    /**
     * @param $other
     * @return Money
     */
    public function div($other)
    {
        return $this->operation($other, 'bcdiv');
    }

    /**
     * Compares this value with another. Returns 1 if this is greater, 0 if equal, -1 if less than the other.
     *
     * @param $other
     * @return int
     */
    public function compare($other)
    {
        $other = $this->prepareForOperation($other);
        return bccomp($this->value, $other->value, $this->precision);
    }

    public function equals($other)
    {
        return $this->compare($other) === 0;
    }

    /**
     * @return bool
     */
    public function isZero()
    {
        return $this->compare($this->cloneWithValue('0')) === 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }
}
