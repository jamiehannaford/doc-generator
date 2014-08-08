Feature: Generating sample code
  In order for my users to better understand how an SDK command works
  As an SDK contributor
  I need to generate method signatures

  Background:
    Given a PHP file contains:
    """
      <?php

      class Service
      {
          /**
           * @param string $name
           * @param string $date
           * @param array  $options
           * @operation FooOperation
           */
          public function fooAction($name, $date, array $options = []) {}
      }
      """
    And the service description file contains:
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
    When I generate signatures
    Then fooAction.signature.rst should contain:
      """
      .. method:: fooAction($name, $date, array $options = [])

          :param   string $name: This is the name param
          :param   string $date: This is the date param
          :param   array $options: See Additional Parameters
          :return: an array-like resource model
          :rtype:  OpenStack\\Common\\Model\\ModelInterface
      """