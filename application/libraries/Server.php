<?php

date_default_timezone_set('Asia/Dhaka');

class Server
{
	private $access_lifetime = 3600;
	function __construct($config = array())
	{
		$CI = &get_instance();
		$credentials = [
			'dsn' => (ENVIRONMENT == 'production') ? 'mysql:dbname=hilibaza_accounts;host=localhost' : 'mysql:dbname=accounts;host=localhost',
			'username' => (ENVIRONMENT == 'production') ? 'hilibaza_production' : 'root',
			'password' => (ENVIRONMENT == 'production') ? '}&Xx2Ohu@+7C' : 'root'
		];
		$this->storage = new OAuth2\Storage\Pdo($credentials);
		if ($CI->input->post('access_lifetime') && intval((int) $CI->input->post('access_lifetime')) > 3600) {
			$this->access_lifetime = intval((int) $CI->input->post('access_lifetime'));
		}
		$this->server = new OAuth2\Server($this->storage, array('allow_implicit' => true, 'id_lifetime' => $this->access_lifetime, 'access_lifetime' => $this->access_lifetime));
		$this->request = OAuth2\Request::createFromGlobals();
		$this->response = new OAuth2\Response();
	}
	public function require_scope($scope = "")
	{
		if (!$this->server->verifyResourceRequest($this->request, $this->response, $scope)) {
			$this->server->getResponse()->send();
			die;
		}
		return $this->server->verifyResourceRequest($this->request, $this->response, $scope);
	}
	public function check_client_id()
	{
		if (!$this->server->validateAuthorizeRequest($this->request, $this->response)) {
			$this->response->send();
			die;
		}
		return $this->server->validateAuthorizeRequest($this->request, $this->response);
	}
	public function token()
	{
		return $this->server->getAccessTokenData(OAuth2\Request::createFromGlobals());
	}
}
