Mobedic
=====
```
              __ \ / __
             /  \ | /  \
                 \|/
            _,.---v---._
   /\__/\  /            \
   \_  _/ /              \
     \ \_|           @ __|
      \                \_
       \     ,__/       /
     ~~~`~~~~~~~~~~~~~~/~~~~
```

An Dependancy Injection Container *just for fun* its just a POC use it at your own risk

Sample file
-----

```PHP
<?php
namespace {
    use Mobedic\Container;

    require 'vendor/autoload.php';

    class Foo
    {
        public $_i = 0;

        public function __construct()
        {
            echo 'Foo::__construct(' . implode(',', func_get_args()) . ')' . "\n";
        }

        public function bar()
        {
            echo 'Foo::bar()' . "\n";
        }

        public function qux($i)
        {
            $this->_i = $i;
            echo 'Foo::qux(' . $i . ') and define Foo::_i = ' . $i . "\n";
        }

        public function getI()
        {
            return $this->_i;
        }
    }

    class Bar
    {

        public function get(Foo $a)
        {
            echo 'Bar::get(Object Foo)' . "\n";
            echo 'Foo::_i = ' . $a->getI() . "\n";
        }

    }

    class Qux
    {

    }

    $container = Container::getInstance();
    $container->set('a', 'Foo')
        ->share()
        ->alias('alias')
        ->property('_i', 'waza');

    $container->set('b', function () {
        return new  \Bar();
    })
        ->argument()
        ->call('get', array('@a'));

    echo 'Start execution' . "\n";

    $b = $container->get('b');
    echo '$b class is ' . get_class($b) . "\n";
}
```