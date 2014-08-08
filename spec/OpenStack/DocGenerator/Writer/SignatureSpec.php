<?php

namespace spec\OpenStack\DocGenerator\Writer;

use GuzzleHttp\Stream\StreamInterface;
use OpenStack\Common\Rest\Operation;
use OpenStack\Common\Rest\Parameter;
use OpenStack\Common\Rest\ServiceDescription;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SignatureSpec extends ObjectBehavior
{
    private $stream;
    private $description;

    function let(StreamInterface $stream, ServiceDescription $description)
    {
        $this->stream = $stream;

        $class  = new \ReflectionClass(__NAMESPACE__ . '\\FixturesClass');
        $method = $class->getMethod('fooOperation');

        $this->description = $description;

        $this->beConstructedWith($stream, $method, $description);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('OpenStack\DocGenerator\Writer\Signature');
    }

    function it_should_use_docblock_descriptions_for_non_operation_methods()
    {
        $string = <<<'EOT'
.. method:: fooOperation($string, $bool = false, array $opts = [], GuzzleHttp\Stream\StreamInterface $interface)

    :param string $string: A string parameter
    :param bool $bool: A bool parameter
    :param array $opts: An opts parameter
    :param StreamInterface $interface: An interface parameter
EOT;

        $this->stream->write($string)->shouldBeCalled();

        $this->write();
    }

    function it_should_use_operation_name_to_define_params(
        Operation $operation,
        Parameter $param1,
        Parameter $param2
    ) {
        $param1->getType()->willReturn('string');
        $param1->getDescription()->willReturn('This is the Name desc');
        $operation->getParam('Name')->willReturn($param1);

        $param2->getType()->willReturn('int');
        $param2->getDescription()->willReturn('This is the Container desc');
        $operation->getParam('Container')->willReturn($param2);

        $this->description->getOperation('BarOperation')->shouldBeCalled();
        $this->description->getOperation('BarOperation')->willReturn($operation);

        $class  = new \ReflectionClass(__NAMESPACE__ . '\\FixturesClass');
        $method = $class->getMethod('barOperation');
        $this->beConstructedWith($this->stream, $method, $this->description);

        $string = <<<'EOT'
.. method:: barOperation($name, $container, array $options = [])

    :param string $name: This is the Name desc
    :param int $container: This is the Container desc
    :param array $options: See Additional Parameters table
EOT;
        $this->stream->write($string)->shouldBeCalled();

        $this->write();
    }
}

class FixturesClass
{
    /**
     * @param string          $string    A string parameter
     * @param bool            $bool      A bool parameter
     * @param array           $opts      An opts parameter
     * @param StreamInterface $interface An interface parameter
     */
    public function fooOperation($string, $bool = false, array $opts = [], StreamInterface $interface)
    {}

    /**
     * @param $name      {BarOperation::Name}
     * @param $container {BarOperation::Container}
     * @param $options   {BarOperation}
     */
    public function barOperation($name, $container, array $options = [])
    {}
}