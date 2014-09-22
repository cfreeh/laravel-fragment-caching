<?php namespace Gchaincl\LaravelFragmentCaching;

class Environment extends \Illuminate\View\Environment {

    public function cache($key, $expires, \Closure $closure)
    {
        $cache = $this->getContainer()['cache'];
        $log = $this->getContainer()['log'];

        $content = $cache->get($key);
        if ( ! $content ) {
            ob_start();

            $closure();
            $content = ob_get_contents();
            ob_end_clean();
            $cache->remember($key, $expires, $content);
            $log->debug('writing cache', [$key]);
        } else {
            $log->debug('reading cache', [$key]);
        }

        return $content;
    }
}
