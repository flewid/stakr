<?php

class UptimeRobot
{
	private $base_uri = 'http://api.uptimerobot.com';
	private $apiKey;
	private $format = 'json';
	private $json_encap = 'jsonUptimeRobotApi()';

	/**
	* Public constructor function
	*
	* @param mixed $apiKey optional
	* @return UptimeRobot
	*/
	public function __construct( $apiKey = null ) {

		$this->apiKey = $apiKey;
	}

	/**
	* Returns the API key
	*
	*/
	public function get_api_key() {

		return $this->apiKey;
	}

	/**
	* Sets the API key
	*
	* @param string $apiKey required
	*/
	public function set_api_key( $apiKey ) {

		if ( empty( $apiKey ) ) {
			throw new Exception( 'Value not specified: apiKey', 1 );
		}
		$this->apiKey = $apiKey;
	}

	/**
	* Gets output format of API calls
	*
	*/
	public function get_format() {

		return $this->format;
	}

	/**
	* Sets output format of API calls
	*
	* @param mixed $format required
	*/
	public function set_format( $format ) {

		if ( empty( $format ) ) {
			throw new Exception( 'Value not specified: format', 1 );
		}
		$this->format = $format;
	}

	/**
	* Returns the result of the API calls
	*
	* @param mixed $url required
	*/
	private function _fetch( $url ) {

		if ( empty( $url ) ) {
			throw new Exception( 'Value not specified: url', 1 );
		}
		$url = trim( $url );
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 50 );
		$file_contents = curl_exec( $ch );
		curl_close( $ch );
		switch ( $this->format ) {
			case 'xml':
				return $file_contents;
			default:
				if ( strpos( $file_contents,'UptimeRobotApi' ) == false ) {
					return $file_contents; } else {
					return substr( $file_contents, strlen( $this->json_encap ) - 1, strlen( $file_contents ) - strlen( $this->json_encap ) ); }
		}
		return false;
	}

	/**
	* This is a Swiss-Army knife type of a method for getting any information on monitors
	*
	* @param array $monitors        optional (if not used, will return all monitors in an account.
	*                               Else, it is possible to define any number of monitors with their IDs like: monitors=15830-32696-83920)
	* @param bool $logs             optional (defines if the logs of each monitor will be returned. Should be set to 1 for getting the logs. Default is 0)
	* @param bool $alertContacts    optional (defines if the notified alert contacts of each notification will be returned.
	*                               Should be set to 1 for getting them. Default is 0. Requires logs to be set to 1)
	*/
	public function get_monitors( $monitors = array(), $logs = 0, $alertContacts = 0, $uptimeRatio = null ) {

		if ( empty( $this->apiKey ) ) {
			throw new Exception( 'Property not set: apiKey', 2 );
		}
		$url = "{$this->base_uri}/getMonitors?apiKey={$this->apiKey}";
		if ( ! empty( $monitors ) ) { $url .= '&monitors=' . implode( '-', $monitors ); }
		if ( ! is_null( $uptimeRatio ) ) { $url .= '&customUptimeRatio='.$uptimeRatio; }
		$url .= "&logs=$logs&showMonitorAlertContacts=$alertContacts&format={$this->format}";
		return $this->_fetch( $url );
	}

	public function get_all_monitors() {
		if ( empty( $this->apiKey ) ) {
			throw new Exception( 'Property not set: apiKey', 2 );
		}
		$offset = 0;
		$url = "{$this->base_uri}/getMonitors?apiKey={$this->apiKey}&offset=" . $offset;
		if ( isset( $uptimeRatio ) && ! is_null( $uptimeRatio ) ) { $url .= '&customUptimeRatio='.$uptimeRatio; }
		$url .= "&logs=0&showMonitorAlertContacts=0&format={$this->format}";
		$result = $this->_fetch( $url );
		$result = str_replace( ',]',']', $result ); // fix json
		$result = str_replace( '[,', '[', $result ); // fix json
		$result = str_replace( ',,', ',', $result ); // fix json
		//error_log($result);
		$result = json_decode( $result );
		$limit = 50;
		//print_r($result);
		$results = array();
		if ( is_object( $result ) && $result->total > $limit ) {
			$total = $result->total;
			while ( $result ) {
				$results[] = $result;
				if ( $offset + $limit >= $total ) {
					break; }
				$offset += $limit;
				$url = "{$this->base_uri}/getMonitors?apiKey={$this->apiKey}&offset=" . $offset;
				if ( isset( $uptimeRatio ) && ! is_null( $uptimeRatio ) ) { $url .= '&customUptimeRatio='.$uptimeRatio; }
				$url .= "&logs=$logs&showMonitorAlertContacts=$alertContacts&format={$this->format}";
				$result = $this->_fetch( $url );
				$result = json_decode( $result );
				//print_r($result);
			}
		} else {
			 $results[] = $result;
		}
		return $results;
	}


	//Get Alert Contacts
	public function get_contacts() {

		if ( empty( $this->apiKey ) ) {
			throw new Exception( 'Property not set: apiKey', 2 );
		}
		$url = "{$this->base_uri}/getAlertContacts?apiKey={$this->apiKey}";
		$url .= "&format={$this->format}";
						return $this->_fetch( $url );

	}

	/**
	* New monitors of any type can be created using this method
	*
	* @param array $params
	*
	* $params can have the following keys:
	*    name           - required
	*    uri            - required
	*    type           - required
	*    subtype        - optional (required for port monitoring)
	*    port           - optional (required for port monitoring)
	*    keyword_type   - optional (required for keyword monitoring)
	*    keyword_value  - optional (required for keyword monitoring)
	*/
	public function new_monitor( $params = array() ) {

		if ( empty( $params['name'] ) || empty( $params['uri'] ) || empty( $params['type'] ) ) {
			throw new Exception( 'Required key "name", "uri" or "type" not specified', 3 );
		} else {
			extract( $params );
		}
		if ( empty( $this->apiKey ) ) {
			throw new Exception( 'Property not set: apiKey', 2 );
		}
		if ( ! isset( $params['monitorAlertContacts'] ) ) {
			$url = "{$this->base_uri}/newMonitor?apiKey={$this->apiKey}&monitorFriendlyName=". urlencode( $name ) . "&monitorURL=$uri&monitorType=$type";
		} else {
			$url = "{$this->base_uri}/newMonitor?apiKey={$this->apiKey}&monitorFriendlyName=" . urlencode( $name ) . "&monitorURL=$uri&monitorType=$type&monitorAlertContacts=$monitorAlertContacts";
		}

		if ( isset( $subtype ) ) { $url .= "&monitorSubType=$subtype"; }
		if ( isset( $port ) ) { $url .= "&monitorPort=$port"; }
		if ( isset( $keyword_type ) ) { $url .= "&monitorKeywordType=$keyword_type"; }
		if ( isset( $keyword_value ) ) { $url .= '&monitorKeywordValue='. urlencode( $keyword_value ); }

		$url .= "&format={$this->format}";
		return $this->_fetch( $url );
	}

	/**
	* monitors can be edited using this method.
	*
	* Important: The type of a monitor can not be edited (like changing a HTTP monitor into a Port monitor).
	* For such cases, deleting the monitor and re-creating a new one is adviced.
	*
	* @param string $monitorId required
	* @param array $params required
	*
	* $params can have the following keys:
	*    name           - required
	*    uri            - required
	*    type           - required
	*    subtype        - optional (required for port monitoring)
	*    port           - optional (required for port monitoring)
	*    keyword_type   - optional (required for keyword monitoring)
	*    keyword_value  - optional (required for keyword monitoring)
	*/
	public function edit_monitor( $monitorId, $params = array() ) {

		if ( empty( $params ) ) {
			throw new Exception( 'Value not specified: params', 1 );
		} else {
			extract( $params );
		}
		if ( empty( $this->apiKey ) ) {
			throw new Exception( 'Property not set: apiKey', 2 );
		}

		$url = "{$this->base_uri}/editMonitor?apiKey={$this->apiKey}&monitorID=$monitorId";

		if ( isset( $name ) ) { $url .= '&monitorFriendlyName='. urlencode( $name ); }
		if ( isset( $status ) ) { $url .= "&monitorStatus=$status"; }
		if ( isset( $uri ) ) { $url .= "&monitorURL=$uri"; }
		if ( isset( $type ) ) { $url .= "&monitorType=$type"; }
		if ( isset( $subtype ) ) { $url .= "&monitorSubType=$subtype"; }
		if ( isset( $port ) ) { $url .= "&monitorPort=$port"; }
		if ( isset( $keyword_type ) ) { $url .= "&monitorKeywordType=$keyword_type"; }
		if ( isset( $keyword_value ) ) { $url .= '&monitorKeywordValue='. urlencode( $keyword_value ); }
		if ( isset( $monitorAlertContacts ) ) { $url .= '&monitorAlertContacts='.$monitorAlertContacts; }

		$url .= "&format={$this->format}";

		return $this->_fetch( $url );
	}

	/**
	* monitors can be deleted using this method.
	*
	* @param string $monitorId required
	*/
	public function delete_monitor( $monitorId ) {

		if ( empty( $monitorId ) ) {
			throw new Exception( 'Value not specified: monitorId', 1 );
		}
		if ( empty( $this->apiKey ) ) {
			throw new Exception( 'Property not set: apiKey', 2 );
		}

		$url = "{$this->base_uri}/deleteMonitor?apiKey={$this->apiKey}&monitorID=$monitorId&format={$this->format}";

		return $this->_fetch( $url );
	}
}
