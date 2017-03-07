<?php


/**
 * SCKSensorData
 *
 * Authors: Smart Citizen Team
 *
 * SCKSensorData implements all the methods necessary for calibrating 
 * and rescaling the data received from a Smart Citizen Kit 1.1. 
 *
 * 
 * TODO: Data validation based on each sensor ranges.
 *
 * Example usage (receive data, converted and store it in a csv file):
 *
 * <?php
 *   include('../sck_sensor_data.php');
 *   
 *   $headers = getallheaders(); 
 * 
 *   $data = $headers['X-SmartCitizenData'];
 *  
 *   $datapoints = json_decode($data, true);
 *  
 *   foreach ($datapoints as $datapoint) {
 *     $datapoint = SCKSensorData::SCK11Convert($datapoint);
 *     $csv .=  implode(', ', $datapoint);
 *   }
 *
 *   $csv .= PHP_EOL;
 *
 *   file_put_contents('./data.csv', $csv, FILE_APPEND);
 * ?>
 *
 * SCKSensorData is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * SCKSensorData is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with SCKSensorData.  If not, see
 * <http://www.gnu.org/licenses/>. 
 *
 */


class SCKSensorData
{
    
    
    /**
     * SCK11Calibration
     * 
     * Calibrates to propper SI units an SCK datapoint
     *
     * @param array $rawBat Indexed array containing a SCK 1.1 datapoint
     * @return array Indexed arrary with a SCK datapoint calibrated 
     *
     */
    
    public static function SCK11Convert($rawData)
    {
        
        $data = array();
        
        if (self::isValidDateTimeString($rawData['timestamp'])) { //Check calibration....
            
            $data['timestamp'] = $rawData['timestamp'];
            
            $data['temp']  = self::tempConversion($rawData['temp']);
            $data['hum']   = self::humConversion($rawData['hum']);
            $data['noise'] = self::noiseConversion($rawData['noise']);
            $data['co']    = self::coConversion($rawData['co']);
            $data['no2']   = self::no2Conversion($rawData['no2']);
            $data['light'] = self::lightConversion($rawData['light']);
            $data['bat']   = self::batConversion($rawData['bat']);
            $data['panel'] = self::panelConversion($rawData['panel']);
            $data['nets']  = $rawData['nets'];
            
            return $data;
            
        } else {

            return false;

        }
        
    }
    
    /**
     * tempConversion
     *
     * Temperature calibration for SHT21 based on the datasheet:
     * https://github.com/fablabbcn/Smart-Citizen-Kit/wiki/Datasheets/HTU-21D.pdf
     *
     * Formula can be tune depending on the SCK enclosure.
     * 
     *
     * @param float $rawTemp
     * @return float Temperature in ºC
     *
     */
    
    public static function tempConversion($rawTemp)
    {
        return round(-53 + 175.72 / 65536.0 * $rawTemp, 2);
    }
    
    /**
     * humConversion
     *
     * Humidity calibration for SHT21 based on the datasheet:
     * https://github.com/fablabbcn/Smart-Citizen-Kit/wiki/Datasheets/HTU-21D.pdf
     *
     * Formula can be tune depending on the SCK enclosure.
     * 
     *
     * @param float $rawHum
     * @return float Rel. Humidity in %
     *
     */
    
    public static function humConversion($rawHum)
    {
        return round(7 + 125.0 / 65536.0 * $rawHum, 2);
    }
    
    /**
     * noiseConversion
     *
     * Noise calibration for SCK1.1 sound sensor. Converts mV in to dBs. 
     * Based on a linear regresion from a lookup table (db.json) obtained after real measurements from our test facility.
     * 
     *
     * @param float $rawSound
     * @return float noise as sound pressure in dB
     *
     */
    
    public static function noiseConversion($rawSound)
    {
        //$dbTable = json_decode(file_get_contents("./sensors/db.json"), true);
        return round(self::tableCalibration(self::$dbTable, $rawSound), 2);
    }
    
    /**
     * coConversion
     *
     * CO values rescaling. For obtaining ppm check:
     * 
     * @param float $rawCO
     * @return float sensor resistance in KOhm
     *
     */
    
    public static function coConversion($rawCO)
    {
        return round($rawCO / 1000, 2);
    }
    
    /**
     * no2Conversion
     *
     * NO2 values rescaling. For obtaining ppm check:
     * 
     * returns k0hm
     * @param float $rawNO2
     * @return float sensor resistance in KOhm
     *
     */
    
    public static function no2Conversion($rawNO2)
    {
        return round($rawNO2 / 1000, 2);
    }
    
    /**
     * lightConversion
     *
     * Light values rescaling. 
     * 
     * returns lux
     * @param float $rawLight
     * @return float light level in lux
     *
     */
    
    public static function lightConversion($rawLight)
    {
        return round($rawLight / 10, 2);
    }
    
    /**
     * batConversion
     *
     * Battery values rescaling. 
     * 
     * @param float $rawBat
     * @return float battery level in %
     *
     */
    
    
    public static function batConversion($rawBat)
    {
        return round($rawBat / 10, 2);
    }
    
    /**
     * panelConversion
     *
     * Solar panel values rescaling. 
     * 
     * @param float $rawBat
     * @return float Tension in volts
     *
     */
    
    public static function panelConversion($rawBat)
    {
        return round($rawBat / 100, 2);
    }
    
    /**
     * isValidDateTimeString
     *
     * Check if a string is a valid time stamp
     *
     * @param string $str_dt
     * @return bool
     *
     */
     
    private static function isValidDateTimeString($str_dt)
    {
        $date1 = DateTime::createFromFormat('Y-m-d G:i:s', $str_dt);
        $date2 = DateTime::createFromFormat('Y-m-d H:i:s', $str_dt);
        return $date1 && ($date1->format('Y-m-d G:i:s') == $str_dt || $date2->format('Y-m-d H:i:s') == $str_dt);
    }
    
     /**
     * tableCalibration
     *
     * Calculates a point based on a linear regresion from a look up table
     *
     * @param array $refTable
     * @param float $rawValue
     * @return float
     *
     */
    
    private static function tableCalibration($refTable, $rawValue)
    {
        for ($i = 0; $i < sizeof($refTable) - 1; $i++) {
            $prevValueRef = $refTable[$i][0];
            $nextValueRef = $refTable[$i + 1][0];
            if ($rawValue >= $prevValueRef && $rawValue < $nextValueRef) {
                $prevValueOutput = $refTable[$i][1];
                $nextValueOutput = $refTable[$i + 1][1];
                $result          = self::linearRegression($rawValue, $prevValueOutput, $nextValueOutput, $prevValueRef, $nextValueRef);
                return $result;
            }
        }
    }

     /**
     * linearRegression
     *     *
     * @param float $valueInput
     * @param float $prevValueOutput
     * @param float $nextValueOutput
     * @param float $prevValueRef
     * @param float $nextValueRef
     * @return float
     *
     */

    private static function linearRegression($valueInput, $prevValueOutput, $nextValueOutput, $prevValueRef, $nextValueRef)
    {
        $slope  = ($nextValueOutput - $prevValueOutput) / ($nextValueRef - $prevValueRef);
        $result = $slope * ($valueInput - $prevValueRef) + $prevValueOutput;
        return $result;
    }

    //CJD convertion json to array php
    private static $dbTable = array( 0 => array( 0 => 0, 1 => 50 ), 1 => array( 0 => 2, 1 => 55 ), 2 => array( 0 => 3, 1 => 57 ),
        3 => array( 0 => 6, 1 => 58 ), 4 => array( 0 => 20, 1 => 59 ), 5 => array( 0 => 40, 1 => 60 ),
        6 => array( 0 => 60, 1 => 61 ), 7 => array( 0 => 75, 1 => 62 ), 8 => array( 0 => 115, 1 => 63 ),
        9 => array( 0 => 150, 1 => 64 ), 10 => array( 0 => 180, 1 => 65 ), 11 => array( 0 => 220, 1 => 66 ),
        12 => array( 0 => 260, 1 => 67 ), 13 => array( 0 => 300, 1 => 68 ), 14 => array( 0 => 375, 1 => 69 ),
        15 => array( 0 => 430, 1 => 70 ), 16 => array( 0 => 500, 1 => 71 ), 17 => array( 0 => 575, 1 => 72 ),
        18 => array( 0 => 660, 1 => 73 ), 19 => array( 0 => 720, 1 => 74 ), 20 => array( 0 => 820, 1 => 75 ),
        21 => array( 0 => 900, 1 => 76 ), 22 => array( 0 => 975, 1 => 77 ), 23 => array( 0 => 1050, 1 => 78 ),
        24 => array( 0 => 1125, 1 => 79 ), 25 => array( 0 => 1200, 1 => 80 ), 26 => array( 0 => 1275, 1 => 81 ),
        27 => array( 0 => 1320, 1 => 82 ), 28 => array( 0 => 1375, 1 => 83 ), 29 => array( 0 => 1400, 1 => 84 ),
        30 => array( 0 => 1430, 1 => 85 ), 31 => array( 0 => 1450, 1 => 86 ), 32 => array( 0 => 1480, 1 => 87 ),
        33 => array( 0 => 1500, 1 => 88 ), 34 => array( 0 => 1525, 1 => 89 ), 35 => array( 0 => 1540, 1 => 90 ),
        36 => array( 0 => 1560, 1 => 91 ), 37 => array( 0 => 1580, 1 => 92 ), 38 => array( 0 => 1600, 1 => 93 ),
        39 => array( 0 => 1620, 1 => 94 ), 40 => array( 0 => 1640, 1 => 95 ), 41 => array( 0 => 1660, 1 => 96 ),
        42 => array( 0 => 1680, 1 => 97 ), 43 => array( 0 => 1690, 1 => 98 ), 44 => array( 0 => 1700, 1 => 99 ),
        45 => array( 0 => 1710, 1 => 100 ), 46 => array( 0 => 1720, 1 => 101 ), 47 => array( 0 => 1745, 1 => 102 ),
        48 => array( 0 => 1770, 1 => 103 ), 49 => array( 0 => 1785, 1 => 104 ), 50 => array( 0 => 1800, 1 => 105 ),
        51 => array( 0 => 1815, 1 => 106 ), 52 => array( 0 => 1830, 1 => 107 ), 53 => array( 0 => 1845, 1 => 108 ),
        54 => array( 0 => 1860, 1 => 109 ), 55 => array( 0 => 1875, 1 => 110 ) ) ;
    
    
}

?>