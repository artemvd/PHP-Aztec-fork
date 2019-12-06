<?php

namespace Aztec;

use Aztec\Encoder\Encoder;

class Aztec
{
	private $options = [];
	private $pixelGrid;

	public function __construct(array $opts = [])
    {
		$this->setColor('color', 0, $opts);
		$this->setColor('bgColor', 255, $opts);

		if (!isset($opts['hint'])) {
			$this->options['hint'] = "dynamic";
		} else {
			if (!in_array($opts['hint'], ["binary", "dynamic"])){
				throw azException::InvalidInput("Invalid value for \"hint\". Expected \"binary\" or \"dynamic\".");
			}
			$this->options['hint'] = $opts['hint'];
		}

		$this->options['ratio'] = (isset($opts['ratio'])) ? $this->option_in_range($opts['ratio'], 1, 10) : 4;
		$this->options['padding'] = (isset($opts['padding'])) ? $this->option_in_range($opts['padding'], 0, 50) : 20;
		$this->options['quality'] = (isset($opts['quality'])) ? $this->option_in_range($opts['quality'], 0, 100) : 90;
		$this->options['eccPercent'] = (isset($opts['eccPercent'])) ? $this->option_in_range($opts['eccPercent'], 1, 200) : 33;
    }

	private function setColor(string $value, int $default, $opts)
	{
		if (!isset($opts[$value])) {
			$this->options[$value] = new azColor($default);
		} else {
			if (!($opts[$value] instanceof azColor)) {
				throw azException::InvalidInput("Invalid value for \"$value\". Expected an azColor object.");
			}
			$this->options[$value] = $opts[$value];
		}
	}

	public function config(array $opts)
	{
		$this->__construct($opts);
	}

	private function option_in_range($value, int $start, int $end)
	{
        if (!is_numeric($value) || $value < $start || $value > $end) {
			throw azException::InvalidInput("Invalid value. Expected an integer between $start and $end.");
        }
	}

	private function render()
	{
		return (new Renderer($this->pixelGrid, $this->options));
	}

	public function toFile(string $filename, bool $forWeb = false)
	{
		$ext = strtoupper(substr($filename, -3));
		($forWeb) AND $filename = null;

		$renderer = $this->render();

		switch($ext)
		{
			case "PNG":
				$renderer->toPNG($filename);
				break;
			case "GIF":
				$renderer->toGIF($filename);
				break;
			case "JPG":
				$renderer->toJPG($filename, $this->options['quality']);
				break;
			default:
				throw azException::InvalidInput('File extension unsupported!');
		}
	}

	public function forWeb(string $ext)
	{
		if (strtoupper($ext) == "BASE64"){
			return ($this->render())->toBase64();
		} else {
			$this->toFile($ext, true);
		}
	}

	public function forPChart(\pChart\pDraw $MyPicture, $X = 0, $Y = 0)
	{
		($this->render())->forPChart($MyPicture->gettheImage(), $X, $Y);
	}

    public function encode($data)
    {
		$this->pixelGrid = (new Encoder())->encode($data, $this->options['eccPercent'], $this->options["hint"]);
		
		return $this;
    }

}
