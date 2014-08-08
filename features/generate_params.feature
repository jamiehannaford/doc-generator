Feature: Generating parameter tables
  In order for my users to view the parameters of commands easily
  As an SDK contributor
  I need to generate parameter tables from service definitions

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

  Scenario: Generating parameter tables for methods with additional params
    When I generate a parameter tables
    Then fooAction.params.rst should contain:

    """
    +----------+-----------+------------+--------------------------------------+
    | Name     | Type      | Required   | Description                          |
    +==========+===========+============+======================================+
    | Author   | string    | No         | This is the author param             |
    +----------+-----------+------------+--------------------------------------+
    """
