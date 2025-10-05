<?php

namespace App\Services;

use Exception;


class UgandanPhoneValidator
{
    private $msisdn;
    private $smartPrefixes = null;
    private $globePrefixes = null;
    private $sunPrefixes = null;

    private $mtnPrefixes = null;
    private $airtelPrefixes = null;
    private $vodafonePrefixes = null;
    private $utlPrefixes = null;
    private $africellPrefixes = null;

    private $prefix = null;
    private $operator = null;
    protected $countryCode = '256';

    public function __construct($msisdn)
    {
        if (UgandanPhoneValidator::validate($msisdn) === false) {
            throw new Exception(
                'The supplied MSISDN is not valid. ' .
                    'You can use the `UgandanPhoneValidator::validate()` method ' .
                    'to validate the MSISDN being passed.',
                400
            );
        }
        $this->msisdn = UgandanPhoneValidator::clean($msisdn);
    }

    function get($countryCode = false, $separator = '')
    {
        if ($countryCode == false) {
            $formattedNumber = $this->msisdn;
            if (!empty($separator)) {
                $formattedNumber = substr_replace($formattedNumber, $separator, 4, 0);
                $formattedNumber = substr_replace($formattedNumber, $separator, 8, 0);
            }
            return $formattedNumber;
        } else {
            $formattedNumber = $this->countryCode . $this->msisdn;
            if (!empty($separator)) {
                $formattedNumber = substr_replace($formattedNumber, $separator, strlen($this->countryCode), 0);
                $formattedNumber = substr_replace($formattedNumber, $separator, 7, 0);
                $formattedNumber = substr_replace($formattedNumber, $separator, 11, 0);
            }
            return $formattedNumber;
        }
    }

    public function getPrefix()
    {
        if ($this->prefix == null) {
            $this->prefix = substr($this->msisdn, 0, 2);
        }
        return $this->prefix;
    }

    public function getOperator()
    {
        $this->setPrefixes();
        if (!empty($this->operator)) {
            return $this->operator;
        }

        if (in_array($this->getPrefix(), $this->mtnPrefixes)) {
            $this->operator = 'MTN';
        } else if (in_array($this->getPrefix(), $this->airtelPrefixes)) {
            $this->operator = 'AIRTEL';
        } else if (in_array($this->getPrefix(), $this->utlPrefixes)) {
            $this->operator = 'UTL';
        } else if (in_array($this->getPrefix(), $this->africellPrefixes)) {
            $this->operator = 'AFRICELL';
        } else if (in_array($this->getPrefix(), $this->vodafonePrefixes)) {
            $this->operator = 'VODAFONE';
        } else {
            $this->operator = 'UNKNOWN NEW NETWORK';
        }

        return $this->operator;
    }

    private function setPrefixes()
    {
        if (empty($this->mtnPrefixes)) {
            $this->mtnPrefixes = json_decode('["77","78","39"]');
        }
        if (empty($this->airtelPrefixes)) {
            $this->airtelPrefixes = json_decode('["70","75"]');
        }
        if (empty($this->vodafonePrefixes)) {
            $this->vodafonePrefixes = json_decode('["72"]');
        }
        if (empty($this->africellPrefixes)) {
            $this->africellPrefixes = json_decode('["79"]');
        }
        if (empty($this->utlPrefixes)) {
            $this->utlPrefixes = json_decode('["71","41"]');
        }
    }

    public static function validate($mobileNumber)
    {
        $mobileNumber = UgandanPhoneValidator::clean($mobileNumber);
        $result = (!empty($mobileNumber) && (strlen($mobileNumber) === 9) && (is_numeric($mobileNumber)));
        return $result;
    }

    private static function clean($msisdn)
    {
        $msisdn = preg_replace("/[^0-9]/", "", $msisdn);
        // We remove the 0 or 256 from the number
        if (substr($msisdn, 0, 1) == '0') {
            $msisdn = substr($msisdn, 1, strlen($msisdn));
        } else if (substr($msisdn, 0, 3) == '256') {
            $msisdn = substr($msisdn, 3, strlen($msisdn));
        }
        return $msisdn;
    }

    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }
}
