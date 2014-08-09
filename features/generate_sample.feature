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
           * @param $name    {FooOperation::Name}
           * @param $date    {FooOperation::Date}
           * @param $options {FooOperation}
           */
          public function fooAction($name, $date, array $options = []) {}

          /**
           * @param $metadata {BarOperation::Metadata}
           * @param $options  {BarOperation}
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
      Then the output should be:
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
      When I generate code samples for this service
      Then the output should be:
        """
        $metadata = [];

        $iterator = $service->barAction($metadata);

        foreach ($iterator as $resource) {
            echo $resource['Name'];
            echo $resource['Age'];
            echo $resource['Location'];
        }
        """