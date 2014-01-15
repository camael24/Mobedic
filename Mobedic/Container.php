<?php
/**
 * Created by PhpStorm.
 * User: Camael24
 * Date: 15/01/14
 * Time: 11:46
 */
namespace Mobedic {


    class Container
    {

        protected static $_instance = null;
        protected $_object = array();
        protected $_current = null;

        public static function getInstance()
        {
            if (static::$_instance === null)
                static::$_instance = new static();

            return static::$_instance;
        }

        public function export()
        {
            return $this->_object;
        }

        public function set($id, $object, $force = false)
        {
            if (!$this->containerExists($id) or $force === true)
                $this->_object[$id] = array(
                    'init'     => $object,
                    'argument' => array(),
                    'calls'    => array(),
                    'object'   => null,
                    'share'    => false
                );

            $this->setCurrentObject($id);

            return $this;
        }

        protected function setCurrentObject($id)
        {
            $this->_current = $id;
        }

        protected function getCurrentObject()
        {
            return $this->_current;
        }

        public function share($share = true)
        {

            $current                          = $this->getCurrentObject();
            $this->_object[$current]['share'] = $share;

            return $this;
        }

        public function call($method, Array $argument = array())
        {

            $current                            = $this->getCurrentObject();
            $this->_object[$current]['calls'][] = array($method, $argument);

            return $this;
        }


        public function argument(Array $argument = array())
        {
            $current                             = $this->getCurrentObject();
            $old                                 = $this->_object[$current]['argument'];
            $this->_object[$current]['argument'] = array_merge($old, $argument);

            return $this;
        }

        public function containerExists($id)
        {
            return array_key_exists($id, $this->_object);
        }

        protected function resolveArguments(Array $arguments)
        {

            foreach ($arguments as $i => $argument)
                if (is_string($argument) and $argument[0] === '@')
                    $arguments[$i] = $this->get(substr($argument, 1));

            return $arguments;
        }

        protected function getContainer($id)
        {
            return $this->_object[$id];
        }

        protected function initContainer($id)
        {
            $container  = $this->getContainer($id);
            $init       = $container['init'];
            $argument   = $this->resolveArguments($container['argument']);
            $calls      = $container['calls'];

            if ($init instanceof \Closure)
                $this->_object[$id]['object'] = call_user_func_array($init, $argument);

            elseif (is_string($init))
                $this->_object[$id]['object'] = $this->initClassFromString($init, $argument);

            else
                $this->_object[$id]['object'] = & $init;

            if (!empty($calls))
                foreach ($calls as $call)
                    $this->initMethodFromString($this->_object[$id]['object'], $call[0], $this->resolveArguments($call[1]));
        }

        protected function initClassFromString($classname, Array $argument = array())
        {

            $class = new \ReflectionClass($classname);

            if (empty($argument) || false === $class->hasMethod('__construct'))
                return $class->newInstance();

            return $class->newInstanceArgs($argument);
        }

        protected function initMethodFromString($object, $method, Array $argument = array())
        {
            $r = new \ReflectionObject($object);

            if ($r->hasMethod($method) === true) {
                if (empty($argument))
                    $r->getMethod($method)->invoke($object);
                else
                    $r->getMethod($method)->invokeArgs($object, $argument);
            }
        }

        public function get($id)
        {
            if ($this->containerExists($id)) {
                $container = $this->getContainer($id);

                if ($container['object'] === null or $container['share'] === false)
                    $this->initContainer($id);

                return $this->_object[$id]['object'];
            } else {
                throw new \Exception('Container ' . $id . ' not found');

            }
        }

        public function reset($id)
        {
            if ($this->containerExists($id))
                $this->_object[$id]['object'] = null;
        }
    }
}
