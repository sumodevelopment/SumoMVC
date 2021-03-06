<?php
namespace Sumo\Core;

/**
 * Created by PhpStorm.
 * User: forest
 * Date: 2017/06/17
 * Time: 10:36 AM
 */
class App
{
    protected $controller = 'home';
    protected $method = 'index';
    protected $params = [];
    protected $url = '';
    protected $database;

    public function __construct()
    {
        $this->bootstrap_eloquent();
        $this->parse_url();
        $this->load_controller();
        $this->load_method();
        $this->get_params();
        $this->_autoload();
        call_user_func([$this->controller, $this->method], $this->params);
    }

    private function bootstrap_eloquent()
    {
        $this->database = new \Database();
    }

    /**
     * Check the URL for errors and sanitize.
     */
    private function parse_url()
    {
        if (isset($_GET['url'])) {
            $this->url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        } else {
            $this->url = [$this->controller, $this->method];
        }
    }

    /**
     * Load the Controller from the URL.
     */
    private function load_controller()
    {
        if (file_exists(CONTROLLER_DIR . ucfirst($this->url[0]) . '.php')) {
            $this->controller = $this->url[0];
            unset($this->url[0]);
        } else {
            $error = new NotFoundException("The Controller " . $this->url[0] . " could not be found.");
            $error->print_error();
        }


        require_once CONTROLLER_DIR . ucfirst($this->controller) . '.php';
        $this->controller = new $this->controller;
    }

    /**
     * Check the method from the URL.
     */
    private function load_method()
    {
        if (isset($this->url[1])) {
            if (method_exists($this->controller, $this->url[1])) {
                $this->method = $this->url[1];
                unset($this->url[1]);
            } else {
                $error = new NotFoundException("The method " . $this->url[1] . " could not be found in the controller " . get_class($this->controller));
                $error->print_error();
            }
        }
    }

    /**
     * Get params from URL.
     */
    private function get_params()
    {
        $this->params = $this->url ? array_values($this->url) : [];
    }

    /**
     * Autoload Models and Libraries.
     */
    private function _autoload()
    {

        spl_autoload_register(function ($class_name) {

            if (file_exists(LIBRARIES_DIR . $class_name . '.php'))
                include LIBRARIES_DIR . $class_name . '.php';

        });
    }

}