<?php
/**
 * User: anubis
 * Date: 12.08.13
 * Time: 14:56
 */

namespace popcorn\app;

use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Factory\AssetFactory;
use popcorn\lib\twigExtensions\TwigExtensionRuAge;
use popcorn\lib\twigExtensions\TwigExtensionRuDate;
use popcorn\lib\twigExtensions\TwigExtensionRuDateFriendly;
use Slim\Slim;
use popcorn\lib\ImageGenerator;
use TwigExtensionRuNumber\TwigExtensionRuNumber;

class Application {

    /**
     * @var \Slim\Slim
     */
    private $slim;
	private $twig,$twigString;
	private $imageGenerator;

    public function __construct($settings = []) {

		$this->slim = new Slim();

		switch ($settings['mode']){
			case 'production':
				ini_set('display_errors',false);
				error_reporting(0);
				break;
			default:
				ini_set('display_errors',true);
				error_reporting(E_ALL ^ E_STRICT);
				break;
		}

		$twigSettings = [
			'templates.path' => $settings['templates.path'],
			'autoescape' => false,
			'cache' => "/tmp/twig/{$settings['mode']}/cache",
			'debug' => $settings['mode'] == 'production' ? false : true,
			'auto_reload' => $settings['mode'] == 'production' ? false : true,
		];

		$twigLoader = new \Twig_Loader_Filesystem($twigSettings['templates.path']);
		$twigLoaderString = new \Twig_Loader_String();

		$this->twig = new \Twig_Environment($twigLoader, $twigSettings);
		$this->twig->addExtension(new \Twig_Extension_Debug());
		$this->twig->addExtension(new \Twig_Extensions_Extension_Text());
		$this->twig->addExtension(new TwigExtensionRuNumber());
		$this->twig->addExtension(new TwigExtensionRuDate());
		$this->twig->addExtension(new TwigExtensionRuDateFriendly());
		$this->twig->addExtension(new TwigExtensionRuAge());

		$this->twigString = new \Twig_Environment($twigLoaderString);
		$this->twigString->addExtension(new TwigExtensionRuNumber());
		$this->twigString->addExtension(new TwigExtensionRuDate());
		$this->twigString->addExtension(new TwigExtensionRuDateFriendly());
		$this->twigString->addExtension(new TwigExtensionRuAge());

		ImageGenerator::setup([
			'bin' => [
				'convert' => '/usr/bin/convert',
				'identify' => '/usr/bin/identify',
				'mogrify' => '/usr/bin/mogrify',
				'lock' => '/usr/bin/flock -n'
			],
			'dir' => [
				'documentRoot' => __DIR__ . '/../htdocs',
				'source' => __DIR__ . '/../htdocs/upload',
				'output' => __DIR__ . '/../htdocs/k/%%/%%',
				'locks' => '/tmp',
			]
		]);

		$this->twig->addGlobal('slim', array(
			'request' => $this->slim->request(),
			'path' => explode('/', $this->slim->request()->getPath())
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

	final public function getTwigString() {
		return $this->twigString;
	}
}