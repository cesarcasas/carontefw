<?php 
class ClientRules
{
	var $iNetMask;
	var $sNetwork;
	var $aTrustedIps;
	
	function __construct()
	{
		$this->setNetMask (24);
		$this->setNetwork ("192.168.0.0");
		$this->aTrustedIps = array();
	}

	function setNetMask ($i) { $this->iNetMask = $i; }
	function setNetwork ($s) { $this->lNetwork = ip2long($s); }
	function setTrustedNetwork ($s)
	{
		list($ip, $mask) = explode("/", $s);
		$this->setNetwork ($ip);
		$this->setNetMask ($mask);
	}

	function setTrustedIps($aTrustedIps)
	{
		foreach ($aTrustedIps as $ip)
			if (ip2long($ip)) 
				array_push($this->aTrustedIps, $ip);
	}

	function isTrustedIp ($ip)
	{
		if (is_array($this->aTrustedIps) && count($this->aTrustedIps))
			return in_array($ip, $this->aTrustedIps);
		else
			return false;
	}

	function isOnNetwork($ip)
	{
		$ip = ip2long($ip);
		if ($ip)
		{
			$mask = $this->iNetMask == 0 ? 0 : (~0 << (32 - $this->iNetMask));
			// here's our lowest int
			$low = $this->lNetwork & $mask;
			// here's our highest int 
			$high = $this->lNetwork | (~$mask & 0xFFFFFFFF);
			if ($ip >= $low && $ip <= $high)
				return true;
			else
				return false;
        	}
		return false;   
    	}

	function isClientTrustful ()
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		return ($this->isTrustedIp ($ip) || $this->isOnNetwork ($ip));
	}
}
?>