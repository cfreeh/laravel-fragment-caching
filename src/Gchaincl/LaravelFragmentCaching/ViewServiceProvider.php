<?php namespace Gchaincl\LaravelFragmentCaching;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    protected $defered = false;

    public function register()
    {
        parent::register();
        $this->registerBladeExtensions();
    }

    public function registerEnvironment()
    {
        $this->app->bindShared('view', function($app) {
            $resolver = $app['view.engine.resolver'];
            $finder = $app['view.finder'];
            $env = new Environment($resolver, $finder, $app['events']);

            $env->setContainer($app);
            $env->share('app', $app);

            return $env;
        });
    }

    protected function registerBladeExtensions()
    {
        $blade = $this->app['view']
            ->getEngineResolver()
            ->resolve('blade')
            ->getCompiler();

        $blade->extend(function($view, $compiler) {
            $pattern = $compiler->createMatcher('cache');
            return preg_replace($pattern, '$1' . $this->cacheTemplate(), $view);
        });

        $blade->extend(function($view, $compiler) {
            $pattern = $compiler->createPlainMatcher('endcache');
            return preg_replace($pattern, '$1<?php }); ?>', $view);
        });

    }

    private function cacheTemplate()
    {
        return <<<'EOF'
<?php
$__fc_vars = get_defined_vars();
list($key, $expires) = explode(',',$2); $key = trim($key,'\'"'); $expires = intval($expires);
echo $__env->cache($key, $expires, function() use($__fc_vars) {
    extract($__fc_vars);
    //cached content below
?>
EOF;
    }
}
