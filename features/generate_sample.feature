Feature: Generating sample code
  In order for my users to better understand how an SDK command works
  As an SDK contributor
  I need to generate sample code from service definitions

  Scenario: Generating sample code for a model-based command
    Given a getObjectMetadata operation has these parameters:
      | name              | type           | required | description |
      | Name              | string         | true     | The unique name for the object. |
      | Container         | string         | true     | The unique name for the container. |
      | IfModifiedSince   | date-time-http | false    | Only return the resource if it has been modified since the time specified |
      | IfUnmodifiedSince | date-time-http | false    | Only return the resource if it has not been modified since the time specified |
      | IfNoneMatch       | date-time-http | false    | Only return a resource if nothing matches this ETag value sent. Otherwise send a 304 Not Modified. |
      | IfMatch           | date-time-http | false    | Only return a resource if it matches this ETag value sent. Otherwise send a 412 Precondition Failed. |
    And it has these properties in its response model:
      | name          | type   |
      | AcceptRanges  | string |
      | ContentLength | string |
      | ContentType   | string |
      | Date          | string |
      | DeleteAt      | string |
      | Metadata      | array  |
      | TransId       | string |
    When I generate sample code
    Then the output is:
    """
    Sample code
    ~~~~~~~~~~~

    .. code-block:: php

      $response = $service->getObjectMetadata([
          'Name'              => '{string}', // required
          'Container'         => '{string}', // required
          'IfModifiedSince'   => '{date-time-http}',
          'IfUnmodifiedSince' => '{date-time-http}',
          'IfNoneMatch'       => '{date-time-http}',
          'IfMatch'           => '{date-time-http}',
      ]);

      echo $response['AcceptRanges'];
      echo $response['ContentLength'];
      echo $response['ContentType'];
      echo $response['Date'];
      echo $response['DeleteAt'];
      $metadata = $response['Metadata']; // Array
      echo $response['TransId'];
    """

  Scenario: Generating sample code for an iterator command
    Given a listContainers operation has these parameters:
      | name      | type    | enum | required | description |
      | Limit     | integer |      | false    | For an integer value n, limits the number of results to n. |
      | Marker    | string  |      | false    | For a string value x, returns container names that are greater in value than the specified marker. |
      | EndMarker | string  |      | false    | For a string value x, returns container names that are less in value than the specified marker. |
      | Format    | string  | json | false    | |
      | Prefix    | string  |      | false    | Prefix value. Object names in the response begin with this value.|
      | Delimeter | string  |      | false    | Delimiter value, which returns the object names that are nested in the container.|
    And it has these properties for each resource:
      | name  | type   |
      | Name  | string |
      | Count | string |
      | Bytes | string |
    When I generate sample code
    Then the output is:
    """
    Sample code
    ~~~~~~~~~~~

    .. code-block:: php

      $iterator = $service->listContainers([
          'Limit'     => '{integer}',
          'Marker'    => '{string}',
          'EndMarker' => '{string}',
          'Format'    => 'json',
          'Prefix'    => '{string}',
          'Delimeter' => '{string}',
      ]);

      foreach ($iterator as $resource) {
          echo $resource['Name'];
          echo $resource['Count'];
          echo $resource['Bytes'];
      }
    """