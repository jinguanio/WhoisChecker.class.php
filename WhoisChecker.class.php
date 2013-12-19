<?php

//Class for checking WHOIS for domain
class WhoisChecker {

    private $whois;
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

    // Returns WHOIS details for $domain
    public function getWhois($domain) {
        try {
            //print "getWhois: " . $domain . "\n";
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

        //Filter unwanted characters from domain
        //$domain = filter_var($domain, FILTER_SANITIZE_URL);
        //$domain = filter_var($domain, FILTER_VALIDATE_URL);

        //Check if previous functions returned false and if domain is in valid format
        if( $domain )
            return $domain;
        else
            throw new Exception("Invalid Domain Name: \"domain\"");
    }

    // Returns date if valid, else throws Invalid Date Exception
    private function validateDate($date) {
	//print "Date 1: $date\n";
        $unixDate = strtotime($date);
        //print "Date 2: $unixDate\n";
        $date = date("Y-m-d H:i:s T", $unixDate);
        //print "Date 3: $date\n";
        return $date;
    }

    // Returns the expiration date for $domain in the following format: YYYY-MM-DD
    public function getExpirationDate($domain) {
        $whoisData = $this->getWhois($domain);
        //print $whoisData;
        $whoisData = nl2br(strtolower($whoisData));
        $whoisData = explode('<br />',$whoisData);

        //look for the expiration information
        $ret = array();
        foreach($whoisData as $line) {
            if( (strpos($line, 'expir') !== false) &&
                (strpos($line, 'notice') === false) &&
                (strpos($line, 'registrar') === false) &&
                (strpos($line, 'currently') === false) &&
                (strpos($line, 'registered until') === false) )
            {
                $line = nl2br($line);
                $line = str_replace(array("<br />","\n","\r","\t"),"",$line);

                $date = explode(":",$line,2);
                $date = $date[1];
                $date = trim($date," \t\r\n\0");
                $date = $this->validateDate($date);
                return $date;
            }
        }
        return "Date not found in WHOIS";
    }
}

?>

