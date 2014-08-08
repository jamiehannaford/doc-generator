Feature: Generating sample code
  In order for my users to better understand how an SDK command works
  As an SDK contributor
  I need to generate sample code from service definitions

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
        BarOperation:
          iterator: BarIterator
          parameters:
            Metadata:
              type: array
              required: true
              description: This is the metadata param
        iterators:
          BarIterator:
            modelSchema:
              properties:
                Expires:
                  type: string
                Genre:
                  type: string
        models:
          FooOutput:
            properties:
              Name:
                type: string
              Age:
                type: string
              Location:
                type: string
      """

    Scenario: Generating code samples for a normal command
      When I generate code samples
      Then fooAction.sample.rst should contain:
        """
        $name = '{string}';
        $date = '{string}';

        $response = $service->fooAction($name, $date, [
            'Author' => '{string}',
            'Genre'  => '{string}'
        ]);

        echo $response['Name'];
        echo $response['Age'];
        echo $response['Location'];
        """

    Scenario: Generating code samples for an iterator
      When I generate code samples
      Then barAction.sample.rst should contain:
        """
        $metadata = [];

        $iterator = $service->barAction($metadata);

        foreach ($iterator as $resource) {
            echo $resource['Name'];
            echo $resource['Age'];
            echo $resource['Location'];
        }
        """