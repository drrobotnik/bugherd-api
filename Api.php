<?php 


class BugHerd_Api {
	const URL = 'http://www.bugherd.com';
	const VERSION = 'api_v2';

	protected $_credentials = array( 'api_key' => null, 'password' => 'x' );

	public function __construct($api_key, $password = 'x' ) {
		$this->setAccountCredentials($api_key, $password);
	}

	public function setAccountCredentials($api_key, $password = 'x' ) {
		$this->_credentials['api_key'] = $api_key;
		$this->_credentials['password'] = $password;
		return $this;
	}

	public function getAccountCredentials() {
		return $this->_credentials;
	}

	public function get_organization(){
		$url = self::URL . "/" . self::VERSION . "/organization.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_users(){
		$url = self::URL . "/" . self::VERSION . "/users.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_users_members(){
		$url = self::URL . "/" . self::VERSION . "/users/members.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_users_guests(){
		$url = self::URL . "/" . self::VERSION . "/users/guests.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_projects(){
		$url = self::URL . "/" . self::VERSION . "/projects.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_projects_active(){
		$url = self::URL . "/" . self::VERSION . "/projects/active.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_projects_by_id( $id ){
		$url = self::URL . "/" . self::VERSION . "/projects/".(int)$id.".json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	/**
	 * {"project":{
	 *   "name":"My Website",
	 *   "devurl":"http://www.example.com",
	 *   "is_active":true,
	 *   "is_public":false
	 * }}
	 */
	public function post_projects( $name, $devurl, $is_active, $is_public ){
		$url = self::URL . "/" . self::VERSION . "/projects.json";
		$method = "POST";

		$args["project"]["name"] = $name;
		$args["project"]["devurl"] = $devurl;
		$args["project"]["is_active"] = $is_active;
		$args["project"]["is_public"] = $is_public;

		return $this->_sendRequest($url, $method, $args);
	}
	/**
	 * POST /api_v2/projects/#{id}/add_guest.json
	 */
	public function post_projects_add_member( $id, $member_id ){
		$url = self::URL . "/" . self::VERSION . "/projects/".$id."/add_member.json";
		$method = "POST";

		$args["user_id"] = $member_id;

		return $this->_sendRequest($url, $method, $args);
	}

	public function post_projects_add_guest( $id, $user_or_email ){

		$url = self::URL . "/" . self::VERSION . "/projects/".$id."/add_guest.json";
		$method = "POST";
		if( filter_var($user_or_email, FILTER_VALIDATE_EMAIL) ){
			$email = $user_or_email;
			$args["email"] = $email;
		}else{
			$user_id = $user_or_email;
			$args["user_id"] = (int)$user_id;
		}
		
		return $this->_sendRequest($url, $method, $args);
	}
	/**
	* Updates project information.
	* @param int $project_id ID of project
	* @param array $args name, devurl, api_key, is_active, is_public
	*/
	public function update_projects( $id, $args = array() ){
		$url = self::URL . "/" . self::VERSION . "/projects/".$id.".json";
		$method = "PUT";
		$project['project'] = $args;
		return $this->_sendRequest($url, $method, $project);
	}

	public function delete_projects( $id ){
		$url = self::URL . "/" . self::VERSION . "/projects/".$id.".json";
		$method = "DELETE";
		return $this->_sendRequest($url, $method);
	}

	public function get_projects_tasks( $project_id, $args = array() ){
		$url = self::URL . "/" . self::VERSION . "/projects/".(int)$project_id."/tasks.json";
		$url .= ( !empty( $args ) ) ? "?" . http_build_query($args) : '';
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function get_projects_tasks_by_id( $project_id, $id ){
		$url = self::URL . "/" . self::VERSION . "/projects/".(int)$project_id."/tasks/".(int)$id.".json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	/**
	 * Sets a task
	 * @param array $args
	 * 					@param string description, 
	 * 					@param string priority: not set|critical|important|normal|minor, 
	 * 					@param string status: backlog|todo|doing|done|closed,
	 *					@param int requester_id,
	 *					@param array tag_names, 
	 *					@param int assigned_to_id,
	 *					@param string external_id
	 */
	public function post_projects_tasks( $project_id, $args = array() ){
		$url = self::URL . "/" . self::VERSION . "/projects/".$project_id."/tasks.json";
		$method = "POST";

		$args["task"] = $args;

		return $this->_sendRequest($url, $method, $args);
	}

	public function update_projects_tasks( $project_id, $id ){
		return true;
	}

	public function get_projects_tasks_comments( $project_id, $task_id ){
		$url = self::URL . "/" . self::VERSION . "/projects/".(int)$project_id."/tasks/".(int)$task_id."/comments.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function post_projects_tasks_comments( $project_id, $task_id ){
		return true;
	}

	public function get_webhooks(){
		$url = self::URL . "/" . self::VERSION . "/webhooks.json";
		$method = "GET";
		return $this->_sendRequest($url, $method);
	}

	public function post_webhooks(){
		return true;
	}

	public function delete_webhooks( $id ){
		return true;
	}

	/**
	* Sends the API request and returns the response
	*
	* @param string $url The url to make the request to
	* @param string $method GET, POST, PUT, or DELETE
	* @param string $xml XML string sent as post body
	*/
	protected function _sendRequest($url, $method = "GET", $args = null ) {
		if (!extension_loaded('curl')) {
			// @codeCoverageIgnoreStart
			throw new Exception("cURL extension is missing.");
			// @codeCoverageIgnoreEnd
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $this->_credentials['api_key'] . ":" . $this->_credentials['password']);
		switch ($method) {
			case "POST":
				curl_setopt($ch, CURLOPT_POST, true);
			break;
			case "PUT":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			break;
			case "DELETE":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
			case "GET":
			default:
				curl_setopt($ch, CURLOPT_HTTPGET, 1);
			break;
		}

		if ($args) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
		}

		$response = curl_exec($ch);
		
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		#var_dump($response);
		#var_dump($status);
		if ($response === false) {
			// No response received
			$error = curl_error($ch);
			$errorno = curl_errno($ch);
			curl_close($ch);
			throw new Exception($error .': '. $errorno);
		} elseif ($status == 404) {
			// The url was incorrect
			throw new Exception("The requested url was not found." .': '. $status);
		} elseif (mb_substr($status, 0, 1) != '2') {
		// Not a sucessful status code
			$json_array = json_decode( $response, true );
			if ( is_array( $json_array['error'] ) ) {
				$errors = array();
				foreach ($json_array['error'] as $error) {
					$errors[] = (string) $error;
				}
				$error = implode(PHP_EOL, $errors);
			} else {
				$error = (string) $json_array['error'];
			}
			// Build Error
			curl_close($ch);
			throw new Exception($error .': '. $status);
		} else {
			// Sucessful response
			curl_close($ch);
			$json_array = json_decode( $response, true );
			return ($json_array !== false) ? $json_array : true;
		}
	}
}

