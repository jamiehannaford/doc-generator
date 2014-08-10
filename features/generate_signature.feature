Feature: Generating sample code
  In order for my users to better understand how an SDK command works
  As an SDK contributor
  I need to generate method signatures

  Background:
    Given the service is named FooService
    And a PHP file contains:
      """
      <?php
      namespace OpenStack\FooService\v2;

      class Service
      {
          /**
           * @param $name    {FooOperation::Name}
           * @param $date    {FooOperation::Date}
           * @param $options {FooOperation}
           *
           * @return {FooOperation}
           */
          public function fooAction($name, $date, array $options = []) {}
      }
      """
    And the service description contains:
      """
      operations:
        FooOperation:
          responseModel: FooOutput
          parameters:
            Name:
              type: string
              required: true
              description: This is the name param
            Date:
              type: integer
              required: true
              description: This is the date param
            Author:
              type: string
              description: This is the author param
            Genre:
              type: string
              static: Classical
              description: This is the genre param
      """

  Scenario: Generating code samples for a normal command
    When I generate the signatures for this service
    Then fooAction.signature.rst should contain:
      """
      .. method:: fooAction($name, $date, array $options = [])

          :param string $name: This is the name param
          :param integer $date: This is the date param
          :param array $options: See Additional Parameters table
          :return: an array-like model object (like a read-only struct) that implements \ArrayAccess
          :rtype: OpenStack\Common\Model\ModelInterface
          :raises CommandException: If a HTTP or network connection error occurs
      """