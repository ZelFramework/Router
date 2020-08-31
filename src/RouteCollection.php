<?php


namespace ZelFramework\Router;


class RouteCollection
{
	
	static $collection = [];
	
	/**
	 * @return Route[]
	 */
	public static function getRoute()
	{
		return self::$collection;
	}
	
	/**
	 * @param Route $route
	 */
	public static function addRoute(Route $route)
	{
		self::$collection[] = $route;
	}
	
	public static function getRouteByName(string $name): Route
	{
		foreach (self::getRoute() as $route) {
			if ($route->getName() === $name)
				return $route;
		}
		throw new \Exception('Route not found');
	}
	
}