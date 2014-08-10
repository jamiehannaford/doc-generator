Feature: Generating parameter tables
  In order for my users to view the parameters of commands easily
  As an SDK contributor
  I need to generate parameter tables from service definitions

  Scenario: Generating parameter table for an operation
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
           */
          public function fooAction($name, $date, array $options = []) {}
      }
      """
    And the service description contains:
      """
      operations:
        FooOperation:
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
            Location:
              type: string
              static: true
              default: Tokyo
              description: This is the author param
            Genre:
              type: integer
              required: true
              description: This is the genre param
      """
    When I generate the parameter table for this service
    Then fooAction.params.rst should contain:
      """
      Additional Parameters
      ~~~~~~~~~~~~~~~~~~~~~

      +----------+-----------+------------+----------------------------+
      | Name     | Type      | Required   | Description                |
      +==========+===========+============+============================+
      | Author   | string    | No         | This is the author param   |
      +----------+-----------+------------+----------------------------+
      | Genre    | integer   | Yes        | This is the genre param    |
      +----------+-----------+------------+----------------------------+
      """

  Scenario: Generating no parameter table for operations that have no additional params
    Given the service is named BarService
    And a PHP file contains:
      """
      <?php
      namespace OpenStack\BarService\v2;

      class Service
      {
          /**
           * @param $name {FooOperation::Name}
           * @param $date {FooOperation::Date}
           */
          public function barAction($name, $date) {}
      }
      """
    And the service description contains:
      """
      operations:
        BarOperation:
          parameters:
            Name:
              type: string
              required: true
              description: This is the name param
            Date:
              type: integer
              required: true
              description: This is the date param
      """
    When I generate the parameter table for this service
    Then fooAction.params.rst should not exist