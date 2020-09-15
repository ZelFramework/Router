<?php


namespace ZelFramework\Router;


class Router
{
	
	private $controller = [];
	private $path;
	private $defaultURI = '';
	
	/**
	 * @return array
	 */
	public function getController(): array
	{
		return $this->controller;
	}
	
	/**
	 * @param array $controller
	 */
	public function setController(array $controller): void
	{
		$this->controller = $controller;
	}
	
	/**
	 * @return Route[]
	 */
	public function getRoutes(): array
	{
		return RouteCollection::getRoute();
	}
	
	/**
	 * Router constructor.
	 * @param array $controllers
	 * @param array $params
	 * @param bool $isDev
	 * @throws \Exception
	 */
	public function __construct(array $controllers, array $params = [], $isDev = false)
	{
		$this->setController($controllers);
		if (isset($params['defaultURI']))
			$this->defaultURI = ($params['defaultUri'][strlen($params['defaultUri']) - 1] === '/' ? substr($params['defaultUri'], 0, -1) : $params['defaultUri']);
		
	}
	
	/**
	 * @param string $namespace
	 * @return string
	 * @throws \Exception
	 */
	public function getPathByNamespace(string $namespace): string
	{
		if (empty($this->path)) {
			$reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
			$projectDir = dirname($reflection->getFileName(), 3) . '/';
			$composerJSON = $projectDir . 'composer.json';
			if (!file_exists($composerJSON))
				throw new \Exception('composer.json not found');
			
			$composerJSON = \json_decode(file_get_contents($composerJSON), true);
			
			$autoload['prod'] = $composerJSON['autoload']['psr-4'];
			$autoload['dev'] = $composerJSON['autoload-dev']['psr-4'];
			
			foreach ($autoload as $key => $item) {
				foreach ($item as $defaultNamespace => $path) {
					break;
				}
				$this->path[$key]['namespace'] = $defaultNamespace;
				$path = ($path[0] === '/' ? substr($path, 1) : $path);
				$this->path[$key]['path'] = $projectDir . ($path[strlen($path) - 1] === '/' ? $path : $path . '/');
			}
			unset($composerJSON);
		}
		
		preg_match('/Tests/', $namespace) ? $autoload = 'dev' : $autoload = 'prod';
		$namespace = str_replace($this->path[$autoload]['namespace'], $this->path[$autoload]['path'], $namespace);
		return str_replace('\\', '/', $namespace) . '/';
	}
	
	public function searchAllRoute()
	{
		foreach ($this->getController() as $path => $namespace) {
			foreach ($this->scanFolder($this->getPathByNamespace($namespace)) as $file) {
				$rc = new \ReflectionClass($namespace . '\\' . $file);
				foreach ($rc->getMethods() as $method) {
					if (preg_match('/@Route\(.+\)/', $method->getDocComment(), $matches)) {
						$params = $this->getRouteParams($matches[0]);
						$route = new Route(($path[strlen($path) - 1] === '/' ? substr($path, 0, -1) : $path) . $params[0], $params[1]);
						$route->setClass($method->class);
						$route->setMethod($method->getName());
						RouteCollection::addRoute($route);
					}
				}
			}
		}
	}
	
	/**
	 * @param string $path
	 * @return array
	 */
	private function scanFolder(string $path): array
	{
		$files = [];
		if (file_exists($path))
			foreach (scandir($path) as $file) {
				if (substr($file, -4) === '.php')
					$files[] = substr($file, 0, -4);
			}
		else
			throw new \Exception('Path not found');
		
		return $files;
	}
	
	/**
	 * @param string $call
	 * @return array
	 */
	private function getRouteParams(string $call): array
	{
		preg_match('/\("([a-z\/-{}]*)\"/', $call, $route);
		preg_match_all('/([a-zA-Z0-9]*)=\"([a-zA-Z0-9_]*)\"/', $call, $matches);
		$config = [];
		for ($i = 0; $i < count($matches[0]); $i++) {
			$config[$matches[1][$i]] = $matches[2][$i];
		}
		return [$route[1], $config];
	}
	
	/**
	 * @param string $uri
	 * @return Route
	 * @throws \Exception
	 */
	public function match(string $uri): Route
	{
		$uri = str_replace($this->defaultURI, '', $uri);
		foreach ($this->getRoutes() as $route) {
			preg_match_all('/\{(.[a-zA-Z0-9]*)\}/', $route->getPath(), $matches);
			$uriExplode = explode('?', substr($uri, 1));
			$uriExplode = explode('/', $uriExplode[0]);
			$routeExplode = explode('/', $route->getPath()[0] === '/' ? substr($route->getPath(), 1) : $route->getPath());
			$isGood = false;
			$params = [];
			
			if (count($uriExplode) === count($routeExplode)) {
				$isGood = true;
				for ($i = 0; $i < count($uriExplode); $i++) {
					preg_match('/\{(.[a-zA-Z0-9]*)\}/', $routeExplode[$i], $matches);
					if ($uriExplode[$i] !== $routeExplode[$i] && count($matches) === 0) {
						$isGood = false;
						break;
					}
					if (count($matches) !== 0)
						$params[] = [$matches[1] => $uriExplode[$i]];
				}
			}
			
			if ($isGood) {
				$route->setParameters($params);
				return $route;
			}
		}
		
		throw new \Exception('No route found - 404');
	}
	
	/**
	 * @param Route $route
	 * @return mixed
	 * @throws \Exception
	 */
	public function call(Route $route)
	{
		$controller = $route->getClass();
		$controller = new $controller();
		$method = $route->getMethod();
		
		if ($route->getParameters() !== []) {
			$params = [];
			foreach ($route->getParameters() as $k => $p) {
				foreach ($p as $v) {
					$params[] = $v;
				}
			}
			$html = call_user_func_array([$controller, $method], $params);
		} else
			$html = $controller->$method();
		
		if ($html)
			return $html;
		else
			throw new \Exception("Method return null");
	}
	
}