<?php

namespace popcorn\lib;


class GenPic {

	const BinConvertPath  = '/usr/bin/convert';
	const BinIdentifyPath = '/usr/bin/identify';
	const TmpPath         = '/tmp';
	const BinLockPath     = '/usr/bin/flock --timeout 10';

	private $sourceFilePath = '';

	private $options = array('resize', 'strip'
		,'quality' => 90
	);

	public function __construct()
	{
		$this->sourcePath = realpath(__DIR__.'/../htdocs/upload/');
		$this->outputPath = realpath(__DIR__.'/../htdocs/k/');
	}

	public function resize($sourceFile, $resizeOptions)
	{
		#$this->convert($sourceFile,array('strip','resize' => 'x90'));
	}

	public function convert($sourceFile, $options = array('strip'))
	{
		if (isset($options['quality']) && (in_array($options['quality'],range(1,100))))
		{
			$this->options['quality'] = (int)$options['quality'];
		}

		$outputFilename = md5(implode('',array_map('serialize',func_get_args()))).strrchr($sourceFile,'.');

		$outputFilename = substr($outputFilename,0,3).'/'.substr($outputFilename,3,3).'/'.$outputFilename;

		if (file_exists($this->outputPath.'/'.$outputFilename)) return $outputFilename;

		if (!is_dir(dirname($this->outputPath.'/'.$outputFilename)))
			mkdir(dirname($this->outputPath.'/'.$outputFilename),0777,true);

		$buildCommand = sprintf('%s %s -strip -quality %u -resize %s %s'
			,self::BinConvertPath
			,escapeshellarg($this->sourcePath.$sourceFile)
			,$this->options['quality']
			,escapeshellarg($options['resize'])
			,$this->outputPath.'/'.$outputFilename
		);

		$buildCommand = sprintf('%s %s %s 2>&1'
			,self::BinLockPath
			,sprintf('%s/flockGenPic_%s.lock'
				,self::TmpPath
				,md5($buildCommand)
			)
			,$buildCommand
		);

		exec($buildCommand, $output, $return_var);

		if ($return_var === 0)
		{
			return $outputFilename;
		}


//		print '<pre>'.print_r($buildCommand,true).'</pre>';
//		print '<pre>'.print_r($output,true).'</pre>';
//		print '<pre>'.print_r($return_var,true).'</pre>';
	}

}