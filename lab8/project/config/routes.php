<?php
	use \Core\Route;
	
	return [
		new Route('/hello/', 'hello', 'index'), 
		
        new Route('/pages/', 'page', 'all'),
	    new Route('/page/test/', 'page', 'test'),
	    new Route('/page/:id/', 'page', 'one'),

        new Route('/test/act1/', 'test', 'act1'),
		new Route('/test/act2/', 'test', 'act2'),
		new Route('/test/act3/', 'test', 'act3'),
		
		new Route('/nums/:n1/:n2/:n3/', 'num', 'sum'),
		
		new Route('/user/all/', 'user', 'all'),
		new Route('/user/first/:n/', 'user', 'first'),
		new Route('/user/:id/:key/', 'user', 'info'),
		new Route('/user/:id/', 'user', 'show'),
		new Route('/user/:id', 'user', 'show'),
		
		new Route('/product/:id/', 'product', 'show'),
        new Route('/products/all/', 'product', 'all'),
        
	];
	