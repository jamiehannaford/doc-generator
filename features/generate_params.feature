Feature: Generating parameter tables
  In order for my users to view the parameters of commands easily
  As an SDK contributor
  I need to generate parameter tables from service definitions

  Background:
    Given a listContainers operation has these parameters:
      | name      | type   | enum | required | description |
      | Limit     | int    |      | false    | For an integer value n, limits the number of results to n. |
      | Marker    | string |      | false    | For a string value x, returns container names that are greater in value than the specified marker. |
      | EndMarker | string |      | false    | For a string value x, returns container names that are less in value than the specified marker. |
      | Format    | string | json | false    | |
      | Prefix    | string |      | false    | Prefix value. Object names in the response begin with this value.|
      | Delimeter | string |      | false    | Delimiter value, which returns the object names that are nested in the container.|

  Scenario: Generating CSV table
    When I generate a CSV table for listContainers
    And the output is:
    """
    Parameters
    ~~~~~~~~~~

    .. csv-table::
      :header: "Name", "Type", "Required", "Description"
      :widths: 20, 20, 10, 50

      "Limit", "int", "No", "For an integer value n, limits the number of results to n."
      "Marker", "string", "No", "For a string value x, returns container names that are greater in value than the specified marker."
      "EndMarker", "string", "No", "For a string value x, returns container names that are less in value than the specified marker."
      "Format", "'json'", "No", ""
      "Prefix", "string", "No", "Prefix value. Object names in the response begin with this value."
      "Delimeter", "string", "No", "Delimiter value, which returns the object names that are nested in the container."
    """