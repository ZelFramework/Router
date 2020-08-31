<?php


namespace ZelFramework\Router;


class Route
{
	
	private $path;
	private $name;
	
	private $class;
	private $method;
	private $parameters = [];
	
	/**
	 * Route constructor.
	 * @param $path
	 * @param $name
	 */
	public function __construct(string $path, array $params = [])
	{
		$this->setPath($path);
		if (!empty($params['name']))
			$this->setName($params['name']);
	}
	
	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}
	
	/**
	 * @param string $path
	 */
	public function setPath(string $path): void
	{
		$this->path = $path;
	}
	
	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}
	
	/**
	 * @return string
	 */
	public function getClass(): string
	{
		return $this->class;
	}
	
	/**
	 * @param string $class
	 */
	public function setClass(string $class): void
	{
		$this->class = $class;
	}
	
	/**
	 * @return string
	 */
	public function getMethod(): string
	{
		return $this->method;
	}
	
	/**
	 * @param string $method
	 */
	public function setMethod(string $method): void
	{
		$this->method = $method;
	}
	
	/**
	 * @return array
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}
	
	/**
	 * @param array $parameters
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = $parameters;
	}
	
	
}