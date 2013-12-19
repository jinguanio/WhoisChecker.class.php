<?php

/**
 * WhoisChecker.class.php
 * 
 * Class for checking the WHOIS information on a domain
 * 
 * PEAR::Net_Whois is required for this class!
 * 
 **/
class WhoisChecker {

    private $whois; //PEAR::Net_Whois instance
    
    public static $month_array = array(
        1 => array('1','01','jan','january'),
        2 => array('2','02','feb','february'),
        3 => array('3','03','mar','march'),
        4 => array('4','04','apr','april'),
        5 => array('5','05','may','may'),
        6 => array('6','06','jun','june'),
        7 => array('7','07','jul','july'),
        8 => array('8','08','aug','august'),
        9 => array('9','09','sep','september'),
        10 => array('10','10','oct','october'),
        11 => array('11','11','nov','november'),
        12 => array('12','12','dec','december'),
    );

    // Constructor
    public function __construct() {
        require_once "Net/Whois.php";
        $this->whois = new Net_Whois;
    }

    // Returns WHOIS details for given $domain
    public function getWhois($domain) {
        try {
            $domain = $this->validateDomain($domain);
            $data = $this->whois->query($domain);
            return $data;
        } catch (Exception $e) {
            print "Invalid Domain Name Passed!";
        }
        return false;
    }

    // Returns domain if valid, else throws Invalid Domain Exception
    private function validateDomain($domain) {

        //TODO Check if domain is in valid format
        if( $domain )
            return $domain;
        else
            throw new Exception("Invalid Domain Name: \"domain\"");
    }

    // Returns a date formatted to "Y-m-d H:i:s T" or throws Invalid Date Exception
    private function validateDate($dateString) {
	$unixDate = strtotime($dateString);
        $date = date("Y-m-d H:i:s T", $unixDate);
        return $date;
    }

    // Returns the expiration date for $domain in the following format: Y-m-d H:i:s T
    public function getExpirationDate($domain) {
        //Gets WHOIS data for domain
        $whoisData = $this->getWhois($domain);
        
        //converts WHOIS data into an array by line
        $whoisData = nl2br(strtolower($whoisData));
        $whoisData = explode('<br />',$whoisData);

        //Line by line, look for the expiration information
        $ret = array();
        foreach($whoisData as $line) {
            if( (strpos($line, 'expir') !== false) && //Should contain this
                (strpos($line, 'notice') === false) && //Should not contain these
                (strpos($line, 'registrar') === false) &&
                (strpos($line, 'currently') === false) &&
                (strpos($line, 'registered until') === false) )
            {
            	//performing further cleanup on line
                $line = nl2br($line);
                $line = str_replace(array("<br />","\n","\r","\t"),"",$line);

		//extracting date; typical format is "expiration date: SOMEDATE"
                $date = explode(":",$line,2);
                $date = $date[1];
                $date = trim($date," \t\r\n\0");
                
                //format the date into common format and return
                $formatted_date = $this->validateDate($date);
                return $formatted_date;
            }
        }
        //If a line was not found that matched the above conditions, return this string
        return "Date not found in WHOIS";
    }
}

?>

