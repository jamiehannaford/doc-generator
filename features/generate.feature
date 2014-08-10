@file-clean
Feature: Generating RST include files
  In order for my users to have a great documentation experience
  As an SDK contributor
  I want to generate include files based off of service descriptions

  Background:
    Given the service description contains:
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

  Scenario: Generating docs for a conventional method with no REST association
    Given the service is named FooService
    And the PHP file contains:
      """
      <?php
      namespace OpenStack\FooService\v2;

      class Service
      {
          /**
           * @param string $name    A really cool param
           * @param array  $options An even cooler param
           *
           * @return mixed
           */
          public function fooAction($name, array $options = []) {}
      }
      """
    When I generate documentation for this service
    Then these doc files should exist:
      | filename                |
      | fooAction.signature.rst |
      | fooAction.sample.rst    |

  Scenario: Generating docs for a method where every param is associated with a REST param
    Given the service is named BarService
    And the PHP file contains:
      """
      <?php
      namespace OpenStack\BarService\v2;

      class Service
      {
          /**
           * @param $name    {FooOperation::Name}
           * @param $options {FooOperation}
           *
           * @return {FooOperation}
           */
          public function fooAction($name, array $options = []) {}
      }
      """
    When I generate documentation for this service
    Then these doc files should exist:
      | filename                |
      | fooAction.params.rst    |
      | fooAction.sample.rst    |
      | fooAction.signature.rst |

    Scenario: Generating doc files for a method which has a mix
      Given the service is named BazService
      And the PHP file contains:
        """
        <?php
        namespace OpenStack\BazService\v2;

        class Service
        {
            /**
             * @param $name    {FooOperation::Name}
             * @param $options {FooOperation}
             *
             * @return {FooOperation}
             */
            public function fooAction($name, array $options = []) {}
        }
        """
      When I generate documentation for this service
      Then these doc files should exist:
        | filename                |
        | fooAction.params.rst    |
        | fooAction.sample.rst    |
        | fooAction.signature.rst |