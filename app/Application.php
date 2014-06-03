<?php

namespace popcorn\app;

use popcorn\lib\twigExtensions\TwigExtensionRuAge;
use popcorn\lib\twigExtensions\TwigExtensionRuDate;
use popcorn\lib\twigExtensions\TwigExtensionRuDateFriendly;
use Slim\Slim;
use popcorn\lib\ImageGenerator;
use popcorn\lib\twigExtensions\TwigExtensionRuNumber;

class Application {

	/**
	 * @var \Slim\Slim
	 */
	private $slim;
	private $twig;
	private $imageGenerator;

	public function __construct($settings = []) {

		$this->slim = new Slim();

		switch ($settings['mode']) {
			case 'production':
				ini_set('display_errors', false);
				error_reporting(0);
				break;
			default:
				ini_set('display_errors', true);
				error_reporting(E_ALL ^ E_STRICT);
				break;
		}

		$twigSettings = [
			'templates.path' => $settings['templates.path'],
			'autoescape'     => false,
			'cache'          => __DIR__ . "/../var/cache/twig/{$settings['mode']}",
			'debug'          => $settings['mode'] == 'production' ? false : true,
			'auto_reload'    => $settings['mode'] == 'production' ? false : true,
		];

		$twigLoader = new \Twig_Loader_Filesystem($twigSettings['templates.path']);

		$this->twig = new \Twig_Environment($twigLoader, $twigSettings);
		$this->twig->addExtension(new \Twig_Extension_Debug());
		$this->twig->addExtension(new \Twig_Extensions_Extension_Text());
		$this->twig->addExtension(new TwigExtensionRuNumber());
		$this->twig->addExtension(new TwigExtensionRuDate());
		$this->twig->addExtension(new TwigExtensionRuDateFriendly());
		$this->twig->addExtension(new TwigExtensionRuAge());

		ImageGenerator::setup([
			'bin' => [
				'convert'  => '/usr/bin/convert',
				'identify' => '/usr/bin/identify',
				'mogrify'  => '/usr/bin/mogrify',
				'lock'     => '/usr/bin/flock -n'
			],
			'dir' => [
				'documentRoot' => __DIR__ . '/../htdocs',
				'source'       => __DIR__ . '/../htdocs/upload',
				'output'       => __DIR__ . '/../htdocs/k/%%/%%',
				'locks'        => '/tmp',
			]
		]);

		$this->getTwig()->addGlobal('app', [
			'request' => $this->getSlim()->request,
			'session' => $_SESSION,
			'flash'   => isset($_SESSION['slim.flash']) ? $_SESSION['slim.flash'] : null
		]);

		$this->twig->addGlobal('slim', array(
			'request' => $this->slim->request,
			'path'    => explode('/', $this->slim->request->getPath())
		));

	}

	public function run() {
		$this->slim->run();
	}

	protected function initRoutes() {

	}

	/**
	 * @return \Slim\Slim
	 */
	public final function getSlim() {
		return $this->slim;
	}

	final public function getTwig() {
		return $this->twig;
	}
}