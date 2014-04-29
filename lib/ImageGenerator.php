<?php

namespace popcorn\lib;

use popcorn\lib\PDOHelper;
use popcorn\model\content\Image;
use popcorn\lib\ImageGeneratorResult;

class ImageGenerator
{
	const ERROR_ARGS              = 101;
	const ERROR_AUTO_OUTPUT_FILES = 102;
	const ERROR_PERMS             = 103;
	const ERROR_SOURCE            = 104;

	private $hooks,$callbacks,$settings,$remotes = array();

	private static $bin,$dir = array();

	private $documentRoot;

	private $pdo,$image;

	function __construct(){
		$this->pdo = PDOHelper::getPDO();
	}

	public function setImage(Image $image){
		$this->image = $image;
	}

	public static function setup(array $settings){
		if (isset($settings['bin'])){
			self::$bin = $settings['bin'];
		}
		if (isset($settings['dir'])){
			self::$dir = $settings['dir'];
		}
	}

	function setupDirs(array $dirs){
		$this->settings = $dirs;
	}

	function setupRemote(){

	}

	function setupDocumentRoot($path){
		$this->documentRoot = $path;
	}

	function getDocumentRoot(){
		return self::$dir['documentRoot'];
	}

	public function registerCallback($methodName,$callable)
	{
		$this->callbacks[$methodName] = $callable;
	}

	public function registerHook($hookType,$callable){
		if ($hookType == 'source'){
			$this->hooks['source'] = $callable;
		}

	}

	public function __call($name,$args){
		if (in_array($name,array_keys($this->callbacks)))
		{
			return call_user_func_array($this->callbacks[$name],$args);
		}
	}

	private function processSource(&$source){
		$source = call_user_func_array($this->hooks['source'],array($source));
	}

	public function hooksOff(){
		$this->hooks = array();
		return $this;
	}

	public function getUploadDir(){
		return $this->settings['sourceDir'];
	}

	private function getDirByMaskHash($mask,$hash)
	{
		$t = 0;
		$a = [];

		foreach (explode('/',$mask) as $path){

			if (strpos($path,'%') === false){
				$a[] = $path;
			} else {
				$len = strlen($path);

				$a[] = substr($hash,$t,$len);
				$t  += $len;
			}
		}

		return implode('/',$a);
	}

	public function setStrategy(array $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @return string
	 */
	private function genLockFile(){

		$return = ':lockBin :lockDir/:lockFile';

		return strtr($return,array(
			':lockBin'  => self::$bin['lock'],
			':lockDir'  => self::$dir['locks'],
			':lockFile' => sprintf('imageGenerator_%s.lock'
//				,md5(implode('',array_map('serialize',func_get_args())))
				,microtime(1)
			),
		));
	}

	private function genAutoName(){
		return md5(implode('',array_map('serialize',func_get_args())));
	}

	private function getInfo($file){
		exec(self::$bin['identify'].' '.$file, $output);
		$info = explode(' ', $output[0]);

		return $info;
	}


	/**
	 * @param $source
	 * @param array $commands
	 * @param string $outFile
	 * @return ImageGeneratorResult
	 * @throws ImageGeneratorException
	 */
	public function convert($source, array $commands = array(), $outFile = ''){
		if (!$commands){
			throw new ImageGeneratorException( 'Должны быть заданы команды', self::ERROR_ARGS );
		}

		if (!$outFile && ( !isset($this->settings['groupRules']) && !isset(self::$dir['output']) )){
			throw new ImageGeneratorException( 'Не установлены связанные директории для использования автоматически генерированных путей изображений', self::ERROR_AUTO_OUTPUT_FILES );
		}

		$preparedLocked = ':lock :convert :source :commands :output';

		## -- Обработка комманд

		$coms = array();

		foreach ($commands as $command => $value)
		{
			if (!is_numeric($command)){
				if (is_numeric($value)){
					$coms[] = sprintf('-%s %d', $command, $value);
				} else {
					$coms[] = sprintf('-%s "%s"', $command, $value);
				}
			} else {
				$coms[] = sprintf('-%s', $value);
			}
		}

		## --

//		$this->processSource($source);

		$outExt = strrchr($source,'.');

		//Путь до оригинала НЕ задан абсолютно, применяем маски и прочую магию %%
		if (strpos($source,'/') !== 0){
			$maskKey = explode('/',$source)[0];
			$rules = $this->settings['groupRules'];

			$source  = key($rules[$maskKey]) . '/' . basename($source);
		} else if (strpos(func_get_args()[0],'/') === 0) {
			$source = func_get_args()[0];
		}

		$autoFilename = $this->genAutoName(func_get_args()) . $outExt;

		$stmt = $this->pdo->prepare('select * from pn_images_gen where genName = :genName limit 1');
		$stmt->execute([
			':genName' => $autoFilename
		]);

		if ($stmt->rowCount() == 1){
			$foundImage = $stmt->fetch(\PDO::FETCH_OBJ);
			return new ImageGeneratorResult($this,$foundImage);
		}

		if (!file_exists($source)){
			throw new ImageGeneratorException(sprintf('Не могу прочитать исходный файл "%s"', $source),self::ERROR_SOURCE);
		}

		if (!$outFile){
			//Путь до оригинала указан полностью
			if (strpos($source,'/') === 0){

				$genPath = $this->getDirByMaskHash(self::$dir['output'],$autoFilename);

			} else{
				$maskKey = explode('/',$source)[0];

				$rules   = $this->settings['groupRules'];
				$genPath = $this->getDirByMaskHash(current($rules[$maskKey]),$autoFilename);
			}

			if (!is_dir($genPath))
				mkdir($genPath,0770,true);

			$outFile = $genPath . '/' . $autoFilename;
		}

//		if (file_exists($outFile) && filesize($outFile) > 0){
//			return new ImageGeneratorResult($this, $outFile);
//		}

		if (!is_writeable(dirname($outFile))){
			throw new ImageGeneratorException(sprintf('Не могу записать файл в директорию "%s"',dirname($outFile)),self::ERROR_PERMS);
		}

		$preparedLocked = strtr($preparedLocked, array(
			':lock'     => $this->genLockFile(func_get_args()),
			':convert'  => self::$bin['convert'],
			':source'   => $source,
			':commands' => join(' ', $coms),
			':output'   => $outFile
		));

		exec("$preparedLocked 2>&1",$output,$return_var);

		$info = $this->getInfo($outFile);
		$info = explode('x',$info[2]);

		$stmt = $this->pdo->prepare('insert into pn_images_gen set imageId = :imageId, genName = :genName, relPath = :relPath, width = :width, height = :height');

		$stmt->bindValue(':imageId',$this->image->getId(),\PDO::PARAM_INT);
		$stmt->bindValue(':genName',$autoFilename,\PDO::PARAM_STR);
		$stmt->bindValue(':relPath',str_replace(realpath($this->getDocumentRoot()), '', realpath($outFile)),\PDO::PARAM_STR);
		$stmt->bindValue(':width',$info[0],\PDO::PARAM_INT);
		$stmt->bindValue(':height',$info[1],\PDO::PARAM_INT);
		$stmt->execute();

		if ($output){
			throw new ImageGeneratorException('Не могу выполнить конвертацию',100,join("\n",$output));
		}

		$stmt = $this->pdo->prepare('select * from pn_images_gen where genName = :genName limit 1');
		$stmt->execute([
			':genName' => $autoFilename
		]);

		if ($stmt->rowCount() == 1){
			$foundImage = $stmt->fetch(\PDO::FETCH_OBJ);
			return new ImageGeneratorResult($this,$foundImage);
		}

		//return $outFile;
	}



}




class ImageGeneratorException extends \Exception
{
	public $code = 0;
	private $details = null;

	public function __construct($message,$code=0,$details = null)
	{
		$this->code = $code;
		$this->details = $details;

		parent::__construct($message, $code);
	}

	public function getDetails(){
		return '<pre>'.print_r($this->details,true).'</pre>';
	}

	public function getHint(){
		switch ($this->code){
			case ImageGenerator::ERROR_AUTO_OUTPUT_FILES:
				return 'Необходимо прописать настройки конфигурации';
				break;
		}
	}
}