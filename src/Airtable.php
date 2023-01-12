<?php
namespace App\Services;

class Airtable
{
	const BASE = "https://api.airtable.com/v0/";
	public $endpoint;

	protected $http;
	protected $id;
	protected $key;
	protected $table;
	protected $args = [];

	public function __construct($http, $key, $id) {
		$this->http = $http;
		$this->key = $key;
		$this->id = $id;
	}

	public function table($tableName) : Airtable
	{
		$this->setTable($tableName);
		return $this;
	}

	private function setTable(String $tableName)
	{
		$this->table = $tableName;
	}

	public function where($key, $value) : Airtable
	{
		$this->setArg($key, $value);
		return $this;
	}

	public function withView(String $viewName) : Airtable
	{
		$this->setArg('view', $viewName);
		return $this;
	}

	public function sort(Array $sortObjects) : Airtable
	{
		$this->args['sort'] = [];

		foreach ( $sortObjects as $object ) {
			$this->args['sort'][] = [
				'field' => $object[0],
				'direction' => $object[1]
			];
		}

		return $this;
	}

	private function setArg($key, $value)
	{
		$this->args[$key] = $value;
	}

	private function resetArgs()
	{
		$this->args = [];
	}

	private function prepareEndpoint()
	{
		$this->endpoint = self::BASE . "$this->id/$this->table";
	}


	/**
	 * @example $response = Airtable::table('Users')
		 	->withView('Grid')
		 	->sort([['Modified', 'desc']])
		 	->where('view', 'ActiveOnly')
		 	->get(10);
	 */
	public function get($pageSize = 10)
	{
		$this->prepareEndpoint();

		$this->setArg('pageSize', $pageSize);
		$response = $this->http::withToken($this->key)->get($this->endpoint, $this->args);

		return (object) [
			'body' => $response->object(),
			'status' => $response->status(),
			'ok' => $response->ok(),
		];
	}

	/**
	 * @example Airtable::table('Users')->grab('recBg9HNMrGypiIMN');
	 */
	public function grab(String $recordId)
	{
		$this->prepareEndpoint();
		$response = $this->http::withToken($this->key)->get("$this->endpoint/$recordId");

		return (object) [
			'body' => $response->object(),
			'status' => $response->status(),
			'ok' => $response->ok(),
		];
	}

	/**
	 * @example Airtable::table('Users')->grab('recBg9HNMrGypiIMN');
	 */
	public function find(String $columnName, $value)
	{
		$this->setArg('filterByFormula', "{$columnName}='{$value}'");
		return $this->get(1);
	}

	public function firstOrCreate(String $uniqueField, Array $new)
	{
		$response = $this->find($uniqueField, $new[$uniqueField]);

		if ( !empty($response->body->records) ) {
			$response->body = $response->body->records[0];
			return $response;
		}

		$this->resetArgs();
		$response = $this->create($new);

		return $response;
	}

	public function create(Array $new)
	{
		$this->prepareEndpoint();

		$this->setArg('records', [
			[
				'fields' => $new
			]
		]);

		$response = $this->http::withToken($this->key)->post($this->endpoint, $this->args);

		return (object) [
			'body' => $response->object(),
			'status' => $response->status(),
			'ok' => $response->ok(),
		];
	}
}
