@file-clean
Feature: Generating RST include files
  In order for my users to have a great documentation experience
  As an SDK contributor
  I want to generate include files based off of service descriptions

  Background:
    Given the path foo-service/v2/Service.php exists

  Scenario: Generating docs for a conventional method with no REST association
    Given the PHP file contains:
      """
      <?php

      class Service
      {
          /**
           * @param string $name    A really cool param
           * @param array  $options An even cooler param
           */
          public function fooAction($name, array $options = []) {}
      }
      """
    When I generate doc files for this service
    Then these doc files should exist:
      | filename                |
      | fooAction.signature.rst |
      | fooAction.sample.rst    |

  Scenario: Generating docs for a method where every param is associated with a REST param
    Given the PHP file contains:
      """
      <?php

      class Service
      {
          /**
           * @param $name    {FooOperation::Name}
           * @param $options {FooOperation}
           */
          public function fooAction($name, array $options = []) {}
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
      """
    When I generate doc files for this service
    Then these doc files should exist:
      | filename                |
      | fooAction.params.rst    |
      | fooAction.sample.rst    |
      | fooAction.signature.rst |

    Scenario: Generating doc files for a method which has a mix
      Given the PHP file contains:
        """
        <?php

        class Service
        {
            /**
             * @param $name    {FooOperation::Name}
             * @param $options {FooOperation}
             */
            public function fooAction($name, array $options = []) {}
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
        """
      When I generate doc files for this service
      Then these doc files should exist:
        | filename                |
        | fooAction.params.rst    |
        | fooAction.sample.rst    |
        | fooAction.signature.rst |