@file-clean
Feature: Generating RST include files
  In order for my users to have a great documentation experience
  As an SDK contributor
  I want to generate include files based off of service descriptions

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

          /**
           * @param array $metadata
           * @param array $options
           * @operation BarOperation
           */
          public function barAction(array $metadata) {}
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
        BarOperation:
          parameters:
            Metadata:
              type: array
              required: true
              description: This is the metadata param
      """

    Scenario: Generating signature files for methods
      When I generate doc files
      Then fooAction.sig.rst should exist
      And barAction.sig.rst should exist

    Scenario: Generating parameter table files for methods
      When I generate doc files
      Then fooAction.params.rst should exist
      And barAction.params.rst should not exist

    Scenario: Generating code sample files for methods
      When I generate doc files
      Then fooAction.sample.rst should exist
      And barAction.sample.rst should exist