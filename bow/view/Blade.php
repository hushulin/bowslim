<?php 
namespace Bow\view;

use \InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;


class Blade
{

    protected $templatePath;

    protected $attributes;

	public function __construct($templatePath = "", $attributes = [])
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
        $this->attributes = $attributes;
    }

	public function render(ResponseInterface $response, $template, array $data = [])
    {
        $output = $this->fetch($template, $data);

        $response->getBody()->write($output);

        return $response;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function addAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function getAttribute($key) {
        if (!isset($this->attributes[$key])) {
            return false;
        }

        return $this->attributes[$key];
    }

    public function getTemplatePath()
    {
        return $this->templatePath;
    }

    public function setTemplatePath($templatePath)
    {
        $this->templatePath = rtrim($templatePath, '/\\') . '/';
    }

    public function fetch($template, array $data = []) {
        if (isset($data['template'])) {
            throw new \InvalidArgumentException("Duplicate template key found");
        }

        // if (!is_file($this->templatePath . $template)) {
        //     throw new \RuntimeException("View cannot render `$template` because the template does not exist");
        // }

        $data = array_merge($this->attributes, $data);


        $path = [$this->templatePath];         // your view file path, it's an array

        if (isset($data['cache_path'])) {
        	$cachePath = $data['cache_path'];     // compiled file path
        }else {
        	$cachePath = $this->templatePath . 'cache';
        }
		
		$compiler = new \Xiaoler\Blade\Compilers\BladeCompiler($cachePath);

		// you can add a custom directive if you want
		$compiler->directive('datetime', function($timestamp) {
		    return preg_replace('/(\(\d+\))/', '<?php echo date("Y-m-d H:i:s", $1); ?>', $timestamp);
		});

		$engine = new \Xiaoler\Blade\Engines\CompilerEngine($compiler);
		$finder = new \Xiaoler\Blade\FileViewFinder($path);

		// if your view file extension is not php or blade.php, use this to add it
		if (isset($data['extension'])) {
			$finder->addExtension($data['extension']);
		}

		// get an instance of factory
		$factory = new \Xiaoler\Blade\Factory($engine, $finder);

		// render the template file and echo it
		return $factory->make($template, $data)->render();

    }

}
